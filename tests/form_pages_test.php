<?php

declare(strict_types=1);

/** Destructive integration suite; only run against a disposable *_test database. */
require __DIR__ . '/bootstrap.php';

use Tests\Reports\Fixture;
use App\Core\Database;
use App\Core\Request;
use App\Core\Router;
use App\Core\Csrf;
use App\Core\Session;
use App\Services\AuthService;

putenv('DB_HOST=' . (getenv('TEST_DB_HOST') ?: '127.0.0.1'));
putenv('DB_DATABASE=' . (getenv('TEST_DB_NAME') ?: 'almarah_test'));
putenv('DB_USERNAME=' . (getenv('TEST_DB_USER') ?: 'alm'));
putenv('DB_PASSWORD=' . (getenv('TEST_DB_PASS') ?: 'almpass'));
if (!str_contains((string) getenv('DB_DATABASE'), 'test')) throw new RuntimeException('Refusing non-test database.');
$app = require __DIR__ . '/../bootstrap/app.php';
$db = $app->make(Database::class);
Fixture::migrate($db);
Fixture::seed($db);
$db->insert('site_settings', ['setting_key'=>'site.notification_email','setting_value'=>'office@example.org']);
$session = $app->make(Session::class);
$session->start();
$token = $app->make(Csrf::class)->token();
$router = $app->make(Router::class);
$ok = 0;
function checkForm(bool $assertion, string $label): void {
    global $ok;
    if (!$assertion) throw new RuntimeException('FAIL: '.$label);
    $ok++;
}
function routeForm(Router $router, $app, string $method, string $path, array $body=[]): \App\Core\Response {
    return $router->dispatch(new Request($method, $path, $method === 'GET' ? $body : [], $method === 'POST' ? $body : [], [], ['accept'=>'application/json','x-requested-with'=>'xmlhttprequest']), $app);
}
foreach (['/contact'=>'/forms/contact', '/become-a-volunteer'=>'/forms/volunteer'] as $path=>$action) {
    $html = routeForm($router, $app, 'GET', $path)->body();
    checkForm(str_contains($html, 'action="https://almarah.org' . $action . '"') && !str_contains($html, '[forminator_form'), $path.' shipped page renders custom form in original page');
}
$db->insert('cms_pages',['slug'=>'contact','title'=>'Contact Us','body_html'=>'<p>Keep original text</p>[forminator_form id="1203"]<p>End</p>']);
$db->insert('cms_pages',['slug'=>'become-a-volunteer','title'=>'Become a Volunteer','body_html'=>'<p>Keep volunteer text</p>[forminator_form id="1204"]<p>End</p>']);
foreach (['/contact'=>'forms/contact','/become-a-volunteer'=>'forms/volunteer'] as $path=>$action) {
    $html = routeForm($router, $app, 'GET', $path)->body();
    checkForm(str_contains($html, 'Keep ') && str_contains($html, $action) && !str_contains($html, '[forminator_form'), $path.' CMS override keeps surrounding body and replaces only shortcode');
}
try {
    routeForm($router, $app, 'POST', '/forms/contact', ['name'=>'No CSRF']);
    throw new RuntimeException('Missing CSRF was accepted');
} catch (\App\Exceptions\HttpException $e) {
    checkForm($e->statusCode() === 419, 'contact POST rejects missing CSRF');
}
$contact=['_token'=>$token,'_t'=>time()-10,'name'=>'A Contact','email'=>'contact@example.org','phone'=>'+923001234567','subject'=>'Question about education','message'=>'I would like details of your programmes.'];
$bad = $contact; $bad['email']='not-an-email';
checkForm(routeForm($router, $app, 'POST', '/forms/contact', $bad)->status() === 422, 'contact backend rejects invalid email');
checkForm($db->int('SELECT COUNT(*) FROM contact_messages') === 0, 'invalid contact not stored');
checkForm(routeForm($router, $app, 'POST', '/forms/contact', $contact)->status() === 200, 'contact AJAX submission succeeds');
checkForm($db->int('SELECT COUNT(*) FROM contact_messages') === 1 && $db->int("SELECT COUNT(*) FROM form_case_events WHERE contact_id IS NOT NULL AND event_type='submitted'") === 1, 'contact saved in existing table with history');
routeForm($router, $app, 'POST', '/forms/contact', $contact);
checkForm($db->int('SELECT COUNT(*) FROM contact_messages') === 1 && $db->int("SELECT COUNT(*) FROM form_submissions WHERE form_key='contact' AND outcome='duplicate'") === 1, 'repeat contact post de-duplicated');
$spam=$contact; $spam['website']='spam.example';
routeForm($router, $app, 'POST', '/forms/contact', $spam);
checkForm($db->int('SELECT COUNT(*) FROM contact_messages') === 1, 'contact honeypot suppresses spam');
checkForm($db->int("SELECT COUNT(*) FROM email_jobs WHERE event_key='forms.notification' AND recipient_email='office@example.org'") === 1, 'contact office notification queued');
$volunteer=['_token'=>$token,'_t'=>time()-10,'full_name'=>'A Volunteer', 'email'=>'volunteer@example.org','phone'=>'+923001234567','whatsapp'=>'+923009876543','city'=>'Lahore','street'=>'123 Park Avenue', 'occupation_type'=>'student','institution'=>'University','skills'=>'Teaching and coaching','areas_of_interest'=>['education','community'], 'availability'=>['Monday','Saturday'], 'experience'=>'Previously mentored students', 'motivation'=>'I would like to support young learners in my community.', 'additional_notes'=>'Weekend mornings preferred'];
$invalid=$volunteer; $invalid['areas_of_interest']=['invalid'];
checkForm(routeForm($router, $app, 'POST', '/forms/volunteer', $invalid)->status()===422, 'volunteer invalid interests rejected');
checkForm(routeForm($router, $app, 'POST', '/forms/volunteer', $volunteer)->status()===200, 'volunteer application submitted');
$v=$db->selectOne("SELECT * FROM volunteers WHERE email='volunteer@example.org'");
checkForm($v && $v['status']==='pending' && $v['city']==='Lahore' && str_contains((string)$v['interests_json'], 'education') && str_contains((string)$v['details_json'], 'Weekend mornings'), 'volunteer stored in existing table with interests, availability and notes');
checkForm($db->int("SELECT COUNT(*) FROM email_jobs WHERE event_key='forms.volunteer_ack' AND recipient_email='volunteer@example.org'")===1, 'volunteer acknowledgment queued');
routeForm($router, $app, 'POST', '/forms/volunteer', $volunteer);
checkForm($db->int('SELECT COUNT(*) FROM volunteers')===1, 'volunteer repeat post de-duplicated');
foreach (['dashboard.view','forms.view','volunteers.manage'] as $permission) {
    $id=$db->insert('permissions',['name'=>$permission,'group_name'=>explode('.',$permission)[0],'label'=>$permission]);
    $db->insert('role_permissions',['role_id'=>1,'permission_id'=>$id]);
}
$auth=$app->make(AuthService::class);
$auth->login($db->selectOne('SELECT * FROM users WHERE id=1'),'127.0.0.1');
$cid=$db->int('SELECT MAX(id) FROM contact_messages');
$vid=(int)$v['id'];
checkForm(routeForm($router,$app,'GET','/admin/inbox/contact')->status()===200, 'authorized staff can open existing contact inbox');
checkForm(routeForm($router,$app,'GET','/admin/inbox/volunteers')->status()===200, 'authorized staff can open existing volunteer inbox');
checkForm(routeForm($router,$app,'GET','/admin/inbox/contact/'.$cid)->status()===200, 'staff sees full contact message and event history');
checkForm(routeForm($router,$app,'GET','/admin/inbox/volunteers/'.$vid)->status()===200, 'staff sees volunteer skills and availability');
routeForm($router,$app,'POST','/admin/inbox/contact/'.$cid,['_token'=>$token,'status'=>'responded','assigned_to'=>'1','admin_notes'=>'Assigned for reply','response_text'=>'Thank you, we will contact you soon.','send_response'=>'1']);
$c=$db->selectOne('SELECT * FROM contact_messages WHERE id=:id',['id'=>$cid]);
checkForm($c['status']==='responded' && (int)$c['assigned_to']===1, 'contact assignment and response status updated');
checkForm($db->int("SELECT COUNT(*) FROM form_case_events WHERE contact_id=$cid AND event_type='response'")===1 && $db->int("SELECT COUNT(*) FROM email_jobs WHERE event_key='forms.contact_response'")===1, 'contact response recorded and email queued');
routeForm($router,$app,'POST','/admin/inbox/volunteers/'.$vid,['_token'=>$token,'status'=>'approved','campaign_id'=>'1','team_id'=>'1','admin_notes'=>'Good fit']);
$v=$db->selectOne('SELECT * FROM volunteers WHERE id=:id',['id'=>$vid]);
checkForm($v['status']==='approved' && (int)$v['campaign_id']===1 && (int)$v['team_id']===1 && (int)$v['reviewed_by']===1, 'volunteer approval and existing campaign/team assignment');
checkForm($db->int("SELECT COUNT(*) FROM form_case_events WHERE volunteer_id=$vid AND event_type='updated'")===1, 'volunteer status history retained');
try { routeForm($router,$app,'POST','/admin/inbox/contact/'.$cid.'/delete',['_token'=>$token]); throw new RuntimeException('Case deletion accepted'); }
catch (\App\Exceptions\HttpException $e) { checkForm($e->statusCode()===403 && $db->int('SELECT COUNT(*) FROM contact_messages')===1, 'contact history protected against inbox deletion'); }

$filtered=routeForm($router,$app,'GET','/admin/inbox/contact',['q'=>'+923001234567','status'=>'responded','date_from'=>gmdate('Y-m-d'),'date_to'=>gmdate('Y-m-d')]);
checkForm($filtered->status()===200 && str_contains($filtered->body(),'A Contact'), 'contact search by phone, date and status');
$csv=routeForm($router,$app,'GET','/admin/inbox/contact/export',['q'=>'+923001234567','status'=>'responded','date_from'=>gmdate('Y-m-d')]);
checkForm($csv->status()===200 && str_contains($csv->body(),'contact@example.org'), 'contact filtered CSV export includes matching record');

$invalidAssignment=routeForm($router,$app,'POST','/admin/inbox/contact/'.$cid,['_token'=>$token,'status'=>'closed','assigned_to'=>'999999']);
checkForm($db->selectOne('SELECT status FROM contact_messages WHERE id=:id',['id'=>$cid])['status']==='responded', 'unknown staff assignment rejected without status change');
$badCampaign=routeForm($router,$app,'POST','/admin/inbox/volunteers/'.$vid,['_token'=>$token,'status'=>'active','campaign_id'=>'2','team_id'=>'1']);
checkForm($db->selectOne('SELECT status FROM volunteers WHERE id=:id',['id'=>$vid])['status']==='approved', 'cross-campaign team assignment rejected');
$volunteerOther=$volunteer; $volunteerOther['email']='other@example.org'; $volunteerOther['occupation_type']='other'; unset($volunteerOther['occupation_detail']);
checkForm(routeForm($router,$app,'POST','/forms/volunteer',$volunteerOther)->status()===422, 'other occupation requires conditional detail server-side');
$transport=new class implements \App\Mail\TransportInterface {
    public array $recipients=[];
    public function send(string $email,string $name,string $subject,string $html,string $text): bool { $this->recipients[]=$email; return true; }
    public function testConnection(): bool { return true; }
    public function describe(): string { return 'capturing transport'; }
};
$queue=new \App\Mail\MailQueue($db,$transport,$app->make(\App\Services\EmailTemplateService::class),$app->make(\App\Core\Logger::class),['queue'=>['enabled'=>true]]);
foreach ($db->select("SELECT id FROM email_jobs WHERE event_key IN ('forms.notification','forms.volunteer_ack','forms.contact_response') ORDER BY id") as $job) $queue->processJob((int)$job['id']);
checkForm(in_array('office@example.org',$transport->recipients,true) && in_array('volunteer@example.org',$transport->recipients,true) && in_array('contact@example.org',$transport->recipients,true), 'office notification, volunteer acknowledgment and contact reply delivered via captured transport');
checkForm($db->int("SELECT COUNT(*) FROM email_jobs WHERE status='sent' AND event_key IN ('forms.notification','forms.volunteer_ack','forms.contact_response')")===4, 'email delivery attempts recorded as sent');
$list=routeForm($router,$app,'GET','/admin/inbox/contact',['q'=>'']);
checkForm($list->status()===200, 'inbox remains available after updates');
$auth->logout();
checkForm(routeForm($router,$app,'GET','/admin/inbox/contact')->status()===401, 'admin inbox rejects unauthenticated AJAX users');
$db->insert('roles',['id'=>4,'name'=>'content_manager','label'=>'Content manager']);
$db->insert('users',['id'=>4,'first_name'=>'Content','last_name'=>'Staff','email'=>'content@example.org','password_hash'=>'x','status'=>'active']);
$db->insert('user_roles',['user_id'=>4,'role_id'=>4]);
foreach (['dashboard.view','forms.view'] as $name) {
    $pid=$db->int('SELECT id FROM permissions WHERE name=:name',['name'=>$name]);
    $db->insert('role_permissions',['role_id'=>4,'permission_id'=>$pid]);
}
$session->start();
$auth->login($db->selectOne('SELECT * FROM users WHERE id=4'),'127.0.0.1');
checkForm(routeForm($router,$app,'GET','/admin/inbox/contact')->status()===200, 'forms-only staff can view contact inquiries');
try { routeForm($router,$app,'GET','/admin/inbox/volunteers'); throw new RuntimeException('Volunteer inbox allowed without permission'); }
catch (\App\Exceptions\HttpException $e) { checkForm($e->statusCode()===403, 'forms-only staff denied volunteer records'); }

echo "{$ok} form-to-admin assertions passed.\n";
