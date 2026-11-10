<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\CmsService;
use App\Services\FormService;
use App\Services\UploadService;

/**
 * Public form endpoints: contact, newsletter, volunteer registration and job
 * applications. Every endpoint answers JSON to the site's data-ajax-form
 * handler and falls back to a redirect + flash for non-JS submissions.
 */
final class FormsController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        Csrf $csrf,
        AuthService $auth,
        private FormService $forms,
        private CmsService $cms,
        private UploadService $uploads
    ) {
        parent::__construct($view, $session, $csrf, $auth);
    }

    public function contact(Request $request): Response
    {
        $in = $request->all();
        if ($blocked = $this->forms->guard('contact', $in, $request->ip())) {
            return $this->blocked($request, $blocked, '/contact');
        }
        $v = Validator::make($in, [
            'name'    => 'required|string|min:2|max:120',
            'email'   => 'required|email|max:191',
            'phone'   => 'required|string|min:7|max:40',
            'subject' => 'required|string|min:2|max:200',
            'message' => 'required|string|min:10|max:5000',
        ], ['name' => 'Name', 'email' => 'Email address', 'message' => 'Message']);
        if ($v->fails()) {
            return $this->invalid($request, $v->errors(), $in, '/contact');
        }
        if (!$this->forms->isDuplicate('contact', $in)) {
            $this->forms->storeContact($in, $request->ip(), (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
        } else {
            $this->forms->record('contact', null, $in, $request->ip(), 'duplicate');
        }
        return $this->ok($request, 'Thank you — your message has been received. Our team will get back to you within 24 hours.', '/contact');
    }

    public function newsletter(Request $request): Response
    {
        $in = $request->all();
        if ($blocked = $this->forms->guard('newsletter', $in, $request->ip(), 6)) {
            return $this->blocked($request, $blocked, '/newsletter');
        }
        $v = Validator::make($in, [
            'name'  => 'nullable|string|max:120',
            'email' => 'required|email|max:191',
        ], ['email' => 'Email address']);
        if ($v->fails()) {
            return $this->invalid($request, $v->errors(), $in, '/newsletter');
        }
        $res = $this->forms->storeNewsletter($in, $request->ip(), (string) ($in['source'] ?? 'website'));
        $msg = $res['status'] === 'existing'
            ? 'You are already on our list — thank you for staying with us.'
            : 'Welcome aboard! You are now subscribed to Al Marah Foundation updates.';
        return $this->ok($request, $msg, '/newsletter');
    }

    public function volunteer(Request $request): Response
    {
        $in = $request->all();
        if ($blocked = $this->forms->guard('volunteer', $in, $request->ip(), 5)) {
            return $this->blocked($request, $blocked, '/become-a-volunteer#volunteer-form');
        }
        $v = Validator::make($in, [
            'full_name' => 'required|string|min:3|max:160',
            'email' => 'required|email|max:191',
            'phone' => 'required|string|min:7|max:40',
            'whatsapp' => 'nullable|string|max:40',
            'city' => 'required|string|max:120',
            'street' => 'required|string|max:200',
            'occupation_type' => 'required|in:student,job,other',
            'occupation_detail' => ($in['occupation_type'] ?? '') === 'other' ? 'required|string|max:120' : 'nullable|string|max:120',
            'institution' => 'nullable|string|max:160',
            'organisation' => 'nullable|string|max:160',
            'skills' => 'required|string|min:2|max:500',
            'experience' => 'nullable|string|max:5000',
            'motivation' => 'required|string|min:10|max:5000',
            'additional_notes' => 'nullable|string|max:3000',
        ], ['full_name' => 'Full name', 'phone' => 'Mobile number', 'street' => 'Address',
            'occupation_type' => 'Occupation', 'occupation_detail' => 'Occupation details',
            'experience' => 'Previous volunteer experience']);
        if ($v->fails()) {
            return $this->invalid($request, $v->errors(), $in, '/become-a-volunteer#volunteer-form');
        }
        $allowedAreas = ['education','food','community','fundraising','events','other'];
        $allowedDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        foreach (['areas_of_interest' => $allowedAreas, 'availability' => $allowedDays] as $key => $allowed) {
            $values = $in[$key] ?? null;
            if (!is_array($values) || !$values || count($values) > count($allowed) ||
                count(array_unique($values)) !== count($values) ||
                array_diff($values, $allowed)) {
                return $this->invalid($request, [$key => 'Please select at least one valid option.'], $in, '/become-a-volunteer#volunteer-form');
            }
        }
        if (!$this->forms->isDuplicate('volunteer', $in)) {
            $this->forms->storeVolunteer($in, $request->ip());
        } else {
            $this->forms->record('volunteer', null, $in, $request->ip(), 'duplicate');
        }
        return $this->ok($request, 'Thank you for registering! A volunteer coordinator will contact you within 48 hours.', '/become-a-volunteer#volunteer-form');
    }

    public function careerApply(Request $request): Response
    {
        $in = $request->all();
        if ($blocked = $this->forms->guard('career', $in, $request->ip(), 5)) {
            return $this->blocked($request, $blocked, '/careers#apply');
        }
        $v = Validator::make($in, [
            'position'     => 'required|string|max:160',
            'name'         => 'required|string|min:3|max:120',
            'email'        => 'required|email|max:191',
            'phone'        => 'required|string|min:7|max:40',
            'city'         => 'nullable|string|max:120',
            'linkedin_url' => 'nullable|url|max:255',
            'cover_letter' => 'nullable|string|max:8000',
        ], ['name' => 'Full name', 'position' => 'Position']);
        if ($v->fails()) {
            return $this->invalid($request, $v->errors(), $in, '/careers#apply');
        }
        $job = null;
        if (!empty($in['job_slug'])) {
            $job = $this->cms->job((string) $in['job_slug']);
        }
        $resumePath = null;
        $resumeName = null;
        $file = $request->file('resume');
        if ($file !== null && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $resumePath = $this->uploads->storeDocument($file, 'resumes', ['pdf', 'doc', 'docx'], 5 * 1024 * 1024);
                $resumeName = mb_substr((string) ($file['name'] ?? 'resume'), 0, 200);
            } catch (\InvalidArgumentException $e) {
                return $this->invalid($request, ['resume' => $e->getMessage()], $in, '/careers#apply');
            }
        } else {
            return $this->invalid($request, ['resume' => 'Please attach your CV (PDF or Word, max 5 MB).'], $in, '/careers#apply');
        }
        $this->forms->storeCareerApplication($in, $job !== null ? (int) $job['id'] : null, $resumePath, $resumeName, $request->ip());
        return $this->ok($request, 'Your application has been received. We will be in touch if your profile matches an opening.', '/careers#apply');
    }

    // ------------------------------------------------------------ responses

    private function ok(Request $request, string $message, string $back): Response
    {
        if ($request->isAjax() || $request->wantsJson()) {
            return Response::json(['success' => true, 'message' => $message]);
        }
        $this->flashSuccess($message);
        return $this->redirect($back);
    }

    /** @param array<string,string|array<int,string>> $errors @param array<string,mixed> $old */
    private function invalid(Request $request, array $errors, array $old, string $back): Response
    {
        if ($request->isAjax() || $request->wantsJson()) {
            return Response::json(['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors], 422);
        }
        unset($old['_token'], $old['website']);
        $this->session->flash('_errors', $errors);
        $this->session->flash('_old', $old);
        return $this->redirect($back);
    }

    private function blocked(Request $request, string $reason, string $back): Response
    {
        if ($reason === 'spam') {
            // Never tell a bot it was detected.
            return $this->ok($request, 'Thank you — your submission has been received.', $back);
        }
        $message = 'Too many submissions from your connection. Please wait a little while and try again.';
        if ($request->isAjax() || $request->wantsJson()) {
            return Response::json(['success' => false, 'message' => $message], 429);
        }
        $this->flashError($message);
        return $this->redirect($back);
    }
}
