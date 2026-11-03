<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Core\Database;
use App\Core\Logger;

/**
 * Privacy Policy, Terms of Use and Donation & Refund Policy as editable CMS
 * pages (template "legal"). Only inserted when the slug does not exist, so
 * edits made in the admin are never overwritten.
 *
 * DRAFTS — written for Almarah Foundation's actual data flows (donations via
 * Meezan / Etisalat hosted pages, bank transfers, volunteer and contact
 * forms, newsletter). They should be reviewed by the Foundation's legal
 * adviser before going live; placeholders are marked [REVIEW].
 */
final class LegalPagesSeeder
{
    public function __construct(private Database $db, private ?Logger $logger = null)
    {
    }

    public function run(): int
    {
        $n = 0;
        foreach ($this->pages() as $slug => $page) {
            if ($this->db->int('SELECT COUNT(*) FROM cms_pages WHERE slug = :s', ['s' => $slug]) > 0) {
                continue;
            }
            $this->db->insert('cms_pages', [
                'slug'             => $slug,
                'title'            => $page['title'],
                'template'         => 'legal',
                'hero_title'       => $page['title'],
                'hero_subtitle'    => $page['subtitle'],
                'body_html'        => $page['body'],
                'menu_group'       => 'footer',
                'status'           => 'published',
                'show_breadcrumbs' => 1,
                'sort_order'       => $page['order'],
            ]);
            $n++;
        }
        return $n;
    }

    /** @return array<string,array{title:string,subtitle:string,order:int,body:string}> */
    private function pages(): array
    {
        $updated = date('j F Y');
        $org = 'Almarah Foundation';
        $address = '3 Canal Park, Gulberg-II, Lahore, Pakistan';
        $email = 'info@almarah.org';
        $phone = '+92 326 8050116';

        $privacy = <<<HTML
<p><em>Last updated: {$updated}</em></p>
<p>{$org} ("we", "us", "the Foundation") is a registered non-profit organisation based in Lahore, Pakistan. This policy explains what personal information we collect through <strong>almarah.org</strong> and our fundraising platform <strong>fundraise.almarah.org</strong>, why we collect it, how long we keep it and the choices you have.</p>

<h2>1. Who is responsible for your data</h2>
<p>{$org}, {$address}. Email: <a href="mailto:{$email}">{$email}</a> · Phone: {$phone}. <span class="review">[REVIEW: add registration number and the name of the person responsible for data protection.]</span></p>

<h2>2. What we collect and why</h2>
<ul>
<li><strong>Donations.</strong> Your name, email address, phone number, country and city, the amount, currency, the purpose you chose (for example Zakat, Parent the Orphan) and whether you wish to remain anonymous. We use this to process your gift, issue your receipt, answer your questions and meet our accounting and audit obligations.</li>
<li><strong>Online card payments.</strong> Payments are completed on the secure hosted pages of <strong>Meezan Bank</strong> or <strong>Etisalat / UBL e-Payment Gateway</strong>. Your card number, expiry date and CVV are entered on their pages and <strong>never reach our servers</strong>. The bank returns to us only a transaction reference, the amount, the currency and the result, which we keep to reconcile and receipt your donation.</li>
<li><strong>Bank transfers and Zelle.</strong> If you transfer directly to one of our published accounts we record the details you share with us (name, reference, amount, date) so we can issue a receipt.</li>
<li><strong>Volunteer and job applications.</strong> Name, contact details, date of birth, guardian name (for applicants under 18), education, occupation, availability, motivation and any CV you upload. We use this to assess your application and to stay in touch about opportunities.</li>
<li><strong>Contact form and newsletter.</strong> Your name, email address and message, so we can reply; your email address, if you subscribe, so we can send our newsletter until you unsubscribe.</li>
<li><strong>Technical information.</strong> IP address, browser type, pages visited and the time of each request, held in server logs for security, fraud prevention and to keep the site working. We do not use advertising or tracking cookies; only a session cookie (to keep you signed in and to protect forms) and, where enabled, privacy-respecting analytics. <span class="review">[REVIEW: confirm which analytics tool, if any, is used.]</span></li>
</ul>

<h2>3. Legal basis</h2>
<p>We process your information to carry out the transaction you asked for (your donation, application or enquiry), to comply with Pakistani law including tax, charity and anti-money-laundering requirements, and for our legitimate interests in running a secure website and keeping accurate records. Where we rely on consent (newsletter, publishing your name as a supporter) you may withdraw it at any time.</p>

<h2>4. How long we keep it</h2>
<ul>
<li>Donation and payment records: <strong>10 years</strong> from the end of the financial year, as required for audit and tax purposes. <span class="review">[REVIEW with auditors.]</span></li>
<li>Volunteer and job applications: up to <strong>2 years</strong> after the application is closed, unless you join us.</li>
<li>Contact messages: <strong>2 years</strong>.</li>
<li>Newsletter subscription: until you unsubscribe.</li>
<li>Server and security logs: <strong>90 days</strong>.</li>
</ul>

<h2>5. Who we share it with</h2>
<p>We never sell your personal information. We share it only with:</p>
<ul>
<li>our payment providers (Meezan Bank, Etisalat / UBL EPG, and the banks holding our accounts) to complete your payment;</li>
<li>our hosting and email delivery providers, who process data on our instructions; <span class="review">[REVIEW: name hosting provider and SMTP provider.]</span></li>
<li>our auditors and regulators where the law requires it;</li>
<li>anyone you ask us to (for example when you choose to show your name on a fundraiser page).</li>
</ul>
<p>Some providers may store data outside Pakistan. Where they do, we rely on their contractual commitments to protect it to an equivalent standard.</p>

<h2>6. How we protect it</h2>
<p>The website is served only over HTTPS. Access to donor records is limited to authorised staff with individual accounts and roles, and every change to a donation record is written to an audit log. Backups are encrypted and stored outside the web server. Card data is never collected by us.</p>

<h2>7. Your rights</h2>
<p>You can ask us at any time to see the information we hold about you, to correct it, to delete it (where we are not legally required to keep it), to stop sending you the newsletter, or to remove your name from public display. Write to <a href="mailto:{$email}">{$email}</a> and we will respond within 30 days.</p>

<h2>8. Children</h2>
<p>Our programmes serve children, but our website is intended for adults. Volunteers under 18 must give a guardian's name and may only volunteer with that guardian's consent. We do not knowingly collect personal information from children online.</p>

<h2>9. Changes</h2>
<p>We will post any changes to this policy on this page with a new "last updated" date. Significant changes will also be announced in our newsletter.</p>

<h2>10. Contact</h2>
<p>{$org}, {$address} · <a href="mailto:{$email}">{$email}</a> · {$phone}</p>
HTML;

        $terms = <<<HTML
<p><em>Last updated: {$updated}</em></p>
<p>These terms apply to your use of almarah.org, fundraise.almarah.org and any service we provide through them. By using the websites you agree to them. If you do not agree, please do not use the websites.</p>

<h2>1. About us</h2>
<p>{$org} is a non-profit organisation registered in Pakistan, {$address}. <span class="review">[REVIEW: add registration authority and number, and FBR / PCP certification references if applicable.]</span></p>

<h2>2. Using the websites</h2>
<p>You may use the websites to learn about our work, donate, apply to volunteer or work with us, start or join a fundraising page, and contact us. You must not use them for anything unlawful, to make payments on behalf of a third party without authority, to upload harmful code, to scrape personal data, or to attempt to gain access to areas you are not authorised to see.</p>

<h2>3. Donations</h2>
<ul>
<li>Donations are voluntary gifts to {$org}. Our <a href="/refund-policy">Donation &amp; Refund Policy</a> explains how we handle mistakes, duplicates and refunds.</li>
<li>You confirm that you are entitled to use the payment method you choose and that the funds are lawfully yours.</li>
<li>Where you choose a purpose (Zakat, Sadaqah, a programme), we apply your gift to that purpose. Zakat is held and distributed separately to eligible recipients in accordance with Shariah guidance. If a programme is fully funded or cannot be delivered, we apply the gift to the closest equivalent programme and tell you where we can.</li>
<li>A receipt is emailed for every completed online donation. Receipts for bank transfers are issued once the credit has been verified against our statement.</li>
<li>Monthly giving on this website is a pledge; each month's payment is a separate donation that you authorise. We do not store card details and cannot charge your card automatically. <span class="review">[REVIEW if recurring billing is later enabled with a gateway.]</span></li>
</ul>

<h2>4. Fundraising pages (fundraise.almarah.org)</h2>
<p>If you create a fundraising page you confirm that what you publish is truthful, that you have permission to use any photos or names you include, and that you will not present yourself as an employee or agent of {$org}. All funds raised through a page are paid directly to {$org} — fundraisers never handle donations. We may edit, pause or remove a page that breaches these terms or could harm the Foundation's reputation.</p>

<h2>5. Volunteers and applicants</h2>
<p>Submitting a volunteer or job application does not create an employment or engagement relationship. Placements with children are subject to identity verification, references and, where relevant, police clearance.</p>

<h2>6. Content and intellectual property</h2>
<p>Text, photographs, logos, reports and designs on the websites belong to {$org} or are used with permission. You may share links and quote short extracts with attribution; please ask before reusing photographs, particularly those of children in our care.</p>

<h2>7. Accuracy and availability</h2>
<p>We try to keep the websites accurate and available, but we do not guarantee that they are error-free or uninterrupted. Programme statistics are updated periodically and may lag behind the latest figures. Nothing on the websites constitutes financial, legal or religious advice.</p>

<h2>8. Liability</h2>
<p>To the extent permitted by law we are not liable for indirect or consequential loss arising from use of the websites. Nothing in these terms limits liability that cannot lawfully be limited. Your donations already made are always protected: we will give you a clear record of them and honour our refund policy.</p>

<h2>9. Privacy</h2>
<p>Our <a href="/privacy-policy">Privacy Policy</a> explains how we handle personal information and forms part of these terms.</p>

<h2>10. Governing law</h2>
<p>These terms are governed by the laws of the Islamic Republic of Pakistan and the courts of Lahore have jurisdiction. <span class="review">[REVIEW with legal adviser.]</span></p>

<h2>11. Contact</h2>
<p>{$org}, {$address} · <a href="mailto:{$email}">{$email}</a> · {$phone}</p>
HTML;

        $refund = <<<HTML
<p><em>Last updated: {$updated}</em></p>
<p>We want every donor to give with confidence. This policy explains what happens if something goes wrong with a donation.</p>

<h2>1. Receipts and records</h2>
<p>Every completed online donation receives an email receipt with a reference number (for example ALM-2026-XXXXXX). Keep it: it lets us find your payment quickly. If you did not receive a receipt within an hour, check your spam folder and then contact us with the approximate time, amount and the last four digits of the card or the bank reference.</p>

<h2>2. Pending payments</h2>
<p>If the bank's payment page did not return you to our site, your donation may show as <em>pending</em>. We check pending payments with the bank automatically; most are resolved within an hour. You will not be charged twice for a single attempt — if you were, see section 4.</p>

<h2>3. Mistakes you tell us about</h2>
<p>If you entered the wrong amount, chose the wrong purpose, or made a donation by mistake, contact us within <strong>14 days</strong> at <a href="mailto:{$email}">{$email}</a> quoting the reference number. We will correct the purpose free of charge, or refund the donation to the original payment method. We may ask for proof of identity so that money only ever goes back to the person who paid.</p>

<h2>4. Duplicate or unauthorised payments</h2>
<p>Duplicate charges for the same donation attempt, and payments you did not authorise, are refunded in full as soon as we have confirmed them with the bank, normally within <strong>7 working days</strong>. Please also tell your card issuer about any unauthorised transaction.</p>

<h2>5. How refunds are paid</h2>
<ul>
<li>Card payments are refunded through the same gateway (Meezan Bank or Etisalat / UBL EPG) to the original card. Banks typically take 5–10 working days to show the credit.</li>
<li>Bank transfers are refunded to the sending account; we will ask you for the account details and the transfer receipt.</li>
<li>Refunds are made in the currency and amount originally received; bank charges and exchange differences are outside our control.</li>
</ul>

<h2>6. When we cannot refund</h2>
<p>Zakat that has already been distributed to eligible recipients, and donations made more than 14 days ago that have already been spent on the programme you chose, cannot generally be refunded. In these cases we will explain where the money went and, where possible, show you the impact it has had.</p>

<h2>7. Cancelling monthly giving</h2>
<p>Monthly giving on this website is paid month by month; there is nothing to cancel on our side. If you have set up a standing order with your bank, cancel it with your bank and let us know so that we can close your pledge record.</p>

<h2>8. Contact</h2>
<p>{$org}, {$address} · <a href="mailto:{$email}">{$email}</a> · {$phone}. We aim to acknowledge every refund request within 2 working days.</p>
HTML;

        return [
            'privacy-policy' => ['title' => 'Privacy Policy', 'subtitle' => 'How we look after the personal information you share with us.', 'order' => 90, 'body' => $privacy],
            'terms'          => ['title' => 'Terms of Use', 'subtitle' => 'The rules for using almarah.org and our fundraising platform.', 'order' => 91, 'body' => $terms],
            'refund-policy'  => ['title' => 'Donation & Refund Policy', 'subtitle' => 'What happens if a donation goes wrong.', 'order' => 92, 'body' => $refund],
        ];
    }
}
