<?php

declare(strict_types=1);

namespace App\Admin;

/**
 * Declarative description of every CMS table the admin panel edits through the
 * generic ResourceController. One definition = list page + create/edit form +
 * delete + drag-to-reorder, with validation and uploads handled centrally.
 *
 * Field types: text, slug, textarea, html, int, decimal, bool, select, date,
 * datetime, url, image (upload), pdf (public upload), color, json.
 *
 * Every write still goes through Validator rules declared here; the controller
 * never trusts a column name coming from the request.
 */
final class ResourceRegistry
{
    /** @return array<string,array<string,mixed>> */
    public static function all(): array
    {
        $status = ['type' => 'select', 'label' => 'Status', 'options' => ['published' => 'Published', 'hidden' => 'Hidden'], 'rules' => 'required|in:published,hidden', 'default' => 'published'];
        $sort = ['type' => 'int', 'label' => 'Sort order', 'rules' => 'nullable|int|min_value:0|max_value:65000', 'default' => 0, 'help' => 'Lower numbers appear first. You can also drag rows on the list page.'];

        return [
            // ------------------------------------------------------------ slider
            'slides' => [
                'table'      => 'cms_slides',
                'label'      => 'Homepage slider',
                'singular'   => 'slide',
                'permission' => 'content.manage',
                'order'      => 'sort_order ASC, id ASC',
                'sortable'   => true,
                'list'       => ['image_path' => 'Image', 'title' => 'Title', 'button_label' => 'Button', 'is_active' => 'Active'],
                'search'     => ['title', 'subtitle'],
                'help'       => 'The big rotating images at the top of the homepage. Use 1920×800 JPGs under 400 KB; the first active slide loads eagerly so keep it light.',
                'fields'     => [
                    'image_path'        => ['type' => 'image', 'label' => 'Desktop image', 'folder' => 'slides', 'rules' => 'nullable|string|max:255', 'required_on_create' => true, 'help' => 'Recommended 1920×800. JPG or WebP.'],
                    'mobile_image_path' => ['type' => 'image', 'label' => 'Mobile image (optional)', 'folder' => 'slides', 'rules' => 'nullable|string|max:255', 'help' => 'Portrait crop for phones, e.g. 900×1100. Falls back to the desktop image.'],
                    'image_alt'         => ['type' => 'text', 'label' => 'Image description (alt text)', 'rules' => 'nullable|string|max:200'],
                    'title'             => ['type' => 'text', 'label' => 'Headline', 'rules' => 'nullable|string|max:200'],
                    'subtitle'          => ['type' => 'textarea', 'label' => 'Sub-headline', 'rules' => 'nullable|string|max:500', 'rows' => 2],
                    'button_label'      => ['type' => 'text', 'label' => 'Button label', 'rules' => 'nullable|string|max:60', 'half' => true],
                    'button_url'        => ['type' => 'text', 'label' => 'Button link', 'rules' => 'nullable|string|max:255', 'half' => true, 'help' => 'e.g. /donate or https://…'],
                    'button2_label'     => ['type' => 'text', 'label' => 'Second button label', 'rules' => 'nullable|string|max:60', 'half' => true],
                    'button2_url'       => ['type' => 'text', 'label' => 'Second button link', 'rules' => 'nullable|string|max:255', 'half' => true],
                    'text_position'     => ['type' => 'select', 'label' => 'Text position', 'options' => ['left' => 'Left', 'center' => 'Centre', 'right' => 'Right'], 'rules' => 'required|in:left,center,right', 'default' => 'left', 'half' => true],
                    'overlay_opacity'   => ['type' => 'int', 'label' => 'Dark overlay (0–90 %)', 'rules' => 'nullable|int|min_value:0|max_value:90', 'default' => 0, 'half' => true],
                    'is_active'         => ['type' => 'bool', 'label' => 'Show this slide', 'default' => 1],
                    'sort_order'        => $sort,
                ],
            ],

            // ----------------------------------------------------------- banners
            'banners' => [
                'table'      => 'cms_banners',
                'label'      => 'Announcement banners',
                'singular'   => 'banner',
                'permission' => 'content.manage',
                'order'      => 'is_active DESC, id DESC',
                'list'       => ['title' => 'Title', 'placement' => 'Placement', 'starts_at' => 'From', 'ends_at' => 'Until', 'is_active' => 'Active'],
                'search'     => ['title', 'body'],
                'help'       => 'A slim bar above the header (e.g. “Ramadan appeal is live”). Only the newest active banner per placement is shown, within its date window.',
                'fields'     => [
                    'placement'  => ['type' => 'select', 'label' => 'Placement', 'options' => ['top_bar' => 'Top bar', 'popup' => 'Pop-up', 'sidebar' => 'Sidebar'], 'rules' => 'required|in:top_bar,popup,sidebar', 'default' => 'top_bar', 'half' => true],
                    'bg_color'   => ['type' => 'color', 'label' => 'Background colour', 'rules' => 'nullable|string|max:9', 'default' => '#6b0f35', 'half' => true],
                    'title'      => ['type' => 'text', 'label' => 'Title', 'rules' => 'required|string|min:3|max:200'],
                    'body'       => ['type' => 'textarea', 'label' => 'Text', 'rules' => 'nullable|string|max:500', 'rows' => 2],
                    'link_label' => ['type' => 'text', 'label' => 'Link label', 'rules' => 'nullable|string|max:60', 'half' => true],
                    'link_url'   => ['type' => 'text', 'label' => 'Link', 'rules' => 'nullable|string|max:255', 'half' => true],
                    'starts_at'  => ['type' => 'datetime', 'label' => 'Show from', 'rules' => 'nullable|string|max:25', 'half' => true],
                    'ends_at'    => ['type' => 'datetime', 'label' => 'Show until', 'rules' => 'nullable|string|max:25', 'half' => true],
                    'is_active'  => ['type' => 'bool', 'label' => 'Active', 'default' => 1],
                ],
            ],

            // ------------------------------------------------------------- pages
            'pages' => [
                'table'      => 'cms_pages',
                'label'      => 'Pages',
                'singular'   => 'page',
                'permission' => 'content.manage',
                'order'      => 'menu_group ASC, sort_order ASC, title ASC',
                'revisions'  => true,
                'list'       => ['title' => 'Title', 'slug' => 'Address', 'template' => 'Template', 'status' => 'Status', 'updated_at' => 'Updated'],
                'search'     => ['title', 'slug'],
                'help'       => 'Simple content pages. Pages whose address matches a designed template (About, Contact, programmes…) use that template and only the hero text and SEO are editable here; everything else renders the HTML body.',
                'preview'    => '/{slug}',
                'fields'     => [
                    'title'            => ['type' => 'text', 'label' => 'Title', 'rules' => 'required|string|min:2|max:200'],
                    'slug'             => ['type' => 'slug', 'label' => 'Web address', 'rules' => 'required|slug|max:160', 'from' => 'title', 'help' => 'Letters, numbers and dashes. Changing it breaks links already shared — add a redirect under SEO if you must.'],
                    'template'         => ['type' => 'select', 'label' => 'Template', 'options' => ['page' => 'Standard page', 'wide' => 'Wide (no sidebar)', 'legal' => 'Legal / policy'], 'rules' => 'required|in:page,wide,legal', 'default' => 'page', 'half' => true],
                    'status'           => ['type' => 'select', 'label' => 'Status', 'options' => ['published' => 'Published', 'draft' => 'Draft'], 'rules' => 'required|in:published,draft', 'default' => 'draft', 'half' => true],
                    'hero_title'       => ['type' => 'text', 'label' => 'Hero title', 'rules' => 'nullable|string|max:200', 'help' => 'Defaults to the page title.'],
                    'hero_subtitle'    => ['type' => 'textarea', 'label' => 'Hero sub-title', 'rules' => 'nullable|string|max:500', 'rows' => 2],
                    'hero_image'       => ['type' => 'image', 'label' => 'Hero image', 'folder' => 'pages', 'rules' => 'nullable|string|max:255'],
                    'body_html'        => ['type' => 'html', 'label' => 'Body', 'rules' => 'nullable|string|max:200000'],
                    'show_breadcrumbs' => ['type' => 'bool', 'label' => 'Show breadcrumbs', 'default' => 1],
                    'menu_group'       => ['type' => 'text', 'label' => 'Group (for the admin list only)', 'rules' => 'nullable|string|max:40', 'half' => true],
                    'sort_order'       => $sort + ['half' => true],
                ],
            ],

            // ---------------------------------------------------- donation purposes
            'purposes' => [
                'table'      => 'donation_purposes',
                'label'      => 'Donation purposes',
                'singular'   => 'purpose',
                'permission' => 'content.manage',
                'order'      => 'sort_order ASC, id ASC',
                'sortable'   => true,
                'list'       => ['name' => 'Name', 'slug' => 'Code', 'is_zakat_eligible' => 'Zakat-eligible', 'status' => 'Status'],
                'search'     => ['name', 'slug'],
                'help'       => 'The options in the “Where should your gift go?” drop-down on the Donate page. Donations keep the purpose they were given under, so hide rather than delete old ones.',
                'fields'     => [
                    'name'              => ['type' => 'text', 'label' => 'Name', 'rules' => 'required|string|min:2|max:120'],
                    'slug'              => ['type' => 'slug', 'label' => 'Code', 'rules' => 'required|slug|max:140', 'from' => 'name', 'help' => 'Used in links such as /donate?purpose=zakat.'],
                    'description'       => ['type' => 'text', 'label' => 'One-line description', 'rules' => 'nullable|string|max:255'],
                    'is_zakat_eligible' => ['type' => 'bool', 'label' => 'Zakat-eligible', 'default' => 1],
                    'status'            => ['type' => 'select', 'label' => 'Status', 'options' => ['active' => 'Active', 'hidden' => 'Hidden'], 'rules' => 'required|in:active,hidden', 'default' => 'active', 'half' => true],
                    'sort_order'        => $sort + ['half' => true],
                ],
            ],

            // -------------------------------------------------------------- team
            'team' => [
                'table'      => 'team_profiles',
                'label'      => 'Board & team',
                'singular'   => 'profile',
                'permission' => 'content.manage',
                'order'      => 'group_key ASC, sort_order ASC, id ASC',
                'sortable'   => true,
                'list'       => ['photo_path' => 'Photo', 'name' => 'Name', 'designation' => 'Designation', 'group_key' => 'Group', 'status' => 'Status'],
                'search'     => ['name', 'designation'],
                'fields'     => [
                    'group_key'    => ['type' => 'select', 'label' => 'Group', 'options' => ['board' => 'Board of Directors', 'advisor' => 'Advisory Board', 'management' => 'Management', 'ambassador' => 'Ambassador'], 'rules' => 'required|in:board,advisor,management,ambassador', 'default' => 'board', 'half' => true],
                    'status'       => $status + ['half' => true],
                    'name'         => ['type' => 'text', 'label' => 'Full name', 'rules' => 'required|string|min:2|max:120', 'half' => true],
                    'designation'  => ['type' => 'text', 'label' => 'Designation', 'rules' => 'nullable|string|max:160', 'half' => true],
                    'photo_path'   => ['type' => 'image', 'label' => 'Photo', 'folder' => 'team', 'rules' => 'nullable|string|max:255', 'help' => 'Square crop, at least 600×600.'],
                    'bio'          => ['type' => 'textarea', 'label' => 'Short bio', 'rules' => 'nullable|string|max:5000', 'rows' => 5],
                    'linkedin_url' => ['type' => 'url', 'label' => 'LinkedIn', 'rules' => 'nullable|url|max:255', 'half' => true],
                    'sort_order'   => $sort + ['half' => true],
                ],
            ],

            // ------------------------------------------------ partners / certificates
            'partners' => [
                'table'      => 'partners',
                'label'      => 'MOUs, certificates & partners',
                'singular'   => 'record',
                'permission' => 'content.manage',
                'order'      => 'kind ASC, sort_order ASC, id ASC',
                'sortable'   => true,
                'list'       => ['logo_path' => 'Logo', 'name' => 'Name', 'kind' => 'Type', 'issued_on' => 'Issued', 'status' => 'Status'],
                'search'     => ['name', 'description'],
                'help'       => 'Feeds the Memorandum of Understanding, Certificates & Registrations and partner-logo sections.',
                'fields'     => [
                    'kind'          => ['type' => 'select', 'label' => 'Type', 'options' => ['mou' => 'Memorandum of Understanding', 'certificate' => 'Certificate', 'registration' => 'Registration', 'bank' => 'Banking partner', 'sponsor' => 'Sponsor / partner'], 'rules' => 'required|in:mou,certificate,registration,bank,sponsor', 'default' => 'mou', 'half' => true],
                    'status'        => $status + ['half' => true],
                    'name'          => ['type' => 'text', 'label' => 'Name', 'rules' => 'required|string|min:2|max:160'],
                    'description'   => ['type' => 'textarea', 'label' => 'Description', 'rules' => 'nullable|string|max:500', 'rows' => 3],
                    'logo_path'     => ['type' => 'image', 'label' => 'Logo / thumbnail', 'folder' => 'partners', 'rules' => 'nullable|string|max:255', 'half' => true],
                    'document_path' => ['type' => 'pdf', 'label' => 'Document (PDF)', 'folder' => 'documents', 'rules' => 'nullable|string|max:255', 'half' => true],
                    'external_url'  => ['type' => 'url', 'label' => 'Website', 'rules' => 'nullable|url|max:255'],
                    'issued_on'     => ['type' => 'date', 'label' => 'Issued on', 'rules' => 'nullable|date', 'half' => true],
                    'expires_on'    => ['type' => 'date', 'label' => 'Expires on', 'rules' => 'nullable|date', 'half' => true],
                    'sort_order'    => $sort,
                ],
            ],

            // ------------------------------------------------------------ reports
            'reports' => [
                'table'      => 'financial_reports',
                'label'      => 'Financial reports',
                'singular'   => 'report',
                'permission' => 'content.manage',
                'order'      => 'sort_order ASC, fiscal_year DESC, id DESC',
                'sortable'   => true,
                'list'       => ['title' => 'Title', 'fiscal_year' => 'Year', 'document_path' => 'File', 'status' => 'Status'],
                'search'     => ['title', 'fiscal_year'],
                'fields'     => [
                    'title'         => ['type' => 'text', 'label' => 'Title', 'rules' => 'required|string|min:3|max:160'],
                    'fiscal_year'   => ['type' => 'text', 'label' => 'Fiscal year', 'rules' => 'required|string|max:20', 'half' => true, 'help' => 'e.g. 2024-25'],
                    'status'        => $status + ['half' => true],
                    'document_path' => ['type' => 'pdf', 'label' => 'PDF', 'folder' => 'reports', 'rules' => 'nullable|string|max:255', 'required_on_create' => true, 'size_column' => 'size_bytes'],
                    'summary'       => ['type' => 'textarea', 'label' => 'Summary', 'rules' => 'nullable|string|max:500', 'rows' => 3],
                    'sort_order'    => $sort,
                ],
            ],

            // ------------------------------------------------------- bank accounts
            'banks' => [
                'table'      => 'bank_accounts',
                'label'      => 'Bank accounts',
                'singular'   => 'account',
                'permission' => 'finance.manage',
                'order'      => 'is_international ASC, sort_order ASC, id ASC',
                'sortable'   => true,
                'list'       => ['bank_name' => 'Bank', 'account_number' => 'Account no.', 'iban' => 'IBAN', 'currency' => 'Currency', 'status' => 'Status'],
                'search'     => ['bank_name', 'account_number', 'iban'],
                'help'       => 'Shown on the Donate and Bank Details pages. Double-check every digit — donors copy these.',
                'fields'     => [
                    'bank_name'        => ['type' => 'text', 'label' => 'Bank name', 'rules' => 'required|string|min:2|max:120', 'half' => true],
                    'account_title'    => ['type' => 'text', 'label' => 'Account title', 'rules' => 'required|string|min:2|max:160', 'default' => 'Almarah Foundation', 'half' => true],
                    'account_number'   => ['type' => 'text', 'label' => 'Account number', 'rules' => 'nullable|string|max:60', 'half' => true],
                    'iban'             => ['type' => 'text', 'label' => 'IBAN', 'rules' => 'nullable|string|max:60', 'half' => true],
                    'swift_code'       => ['type' => 'text', 'label' => 'SWIFT / BIC', 'rules' => 'nullable|string|max:20', 'half' => true],
                    'branch'           => ['type' => 'text', 'label' => 'Branch / sort code', 'rules' => 'nullable|string|max:160', 'half' => true],
                    'currency'         => ['type' => 'select', 'label' => 'Currency', 'options' => ['PKR' => 'PKR', 'USD' => 'USD', 'GBP' => 'GBP', 'EUR' => 'EUR', 'AED' => 'AED', 'SAR' => 'SAR', 'CAD' => 'CAD', 'AUD' => 'AUD'], 'rules' => 'required|in:PKR,USD,GBP,EUR,AED,SAR,CAD,AUD', 'default' => 'PKR', 'half' => true],
                    'account_type'     => ['type' => 'text', 'label' => 'Account type / note', 'rules' => 'nullable|string|max:60', 'half' => true, 'help' => 'e.g. Zakat, Islamic Banking, Zelle'],
                    'logo_path'        => ['type' => 'image', 'label' => 'Bank logo', 'folder' => 'banks', 'rules' => 'nullable|string|max:255'],
                    'is_international' => ['type' => 'bool', 'label' => 'International account', 'default' => 0],
                    'status'           => $status + ['half' => true],
                    'sort_order'       => $sort + ['half' => true],
                ],
            ],

            // ------------------------------------------------------------- media
            'media' => [
                'table'      => 'media_items',
                'label'      => 'Photos & videos',
                'singular'   => 'item',
                'permission' => 'content.manage',
                'order'      => 'kind ASC, sort_order ASC, id DESC',
                'sortable'   => true,
                'list'       => ['thumbnail_path' => 'Thumb', 'title' => 'Title', 'kind' => 'Kind', 'album' => 'Album', 'is_featured' => 'Featured', 'status' => 'Status'],
                'search'     => ['title', 'album'],
                'filters'    => ['kind' => ['photo' => 'Photos', 'video' => 'Videos', 'ebook' => 'E-books', 'document' => 'Documents']],
                'help'       => 'Photos appear in the gallery (grouped by album). Videos are YouTube links — paste the full watch URL. Set a thumbnail for videos to avoid loading YouTube until clicked.',
                'fields'     => [
                    'kind'           => ['type' => 'select', 'label' => 'Kind', 'options' => ['photo' => 'Photo', 'video' => 'Video (YouTube)', 'ebook' => 'E-book (PDF)', 'document' => 'Document (PDF)'], 'rules' => 'required|in:photo,video,ebook,document', 'default' => 'photo', 'half' => true],
                    'status'         => $status + ['half' => true],
                    'title'          => ['type' => 'text', 'label' => 'Title', 'rules' => 'required|string|min:2|max:200'],
                    'file_path'      => ['type' => 'image', 'label' => 'Photo file', 'folder' => 'media', 'rules' => 'nullable|string|max:255', 'half' => true, 'size_column' => 'size_bytes', 'dimension_columns' => ['width', 'height']],
                    'thumbnail_path' => ['type' => 'image', 'label' => 'Thumbnail (optional)', 'folder' => 'media', 'rules' => 'nullable|string|max:255', 'half' => true],
                    'external_url'   => ['type' => 'url', 'label' => 'YouTube / external URL', 'rules' => 'nullable|url|max:500'],
                    'album'          => ['type' => 'text', 'label' => 'Album', 'rules' => 'nullable|string|max:120', 'half' => true, 'help' => 'e.g. Apna Ghar, Iftar 2026'],
                    'alt_text'       => ['type' => 'text', 'label' => 'Alt text', 'rules' => 'nullable|string|max:200', 'half' => true],
                    'description'    => ['type' => 'textarea', 'label' => 'Caption', 'rules' => 'nullable|string|max:1000', 'rows' => 2],
                    'is_featured'    => ['type' => 'bool', 'label' => 'Featured', 'default' => 0],
                    'sort_order'     => $sort,
                ],
            ],

            // -------------------------------------------------------------- jobs
            'jobs' => [
                'table'      => 'career_jobs',
                'label'      => 'Job openings',
                'singular'   => 'job',
                'permission' => 'volunteers.manage',
                'order'      => 'status ASC, sort_order ASC, id DESC',
                'sortable'   => true,
                'list'       => ['title' => 'Title', 'department' => 'Department', 'employment_type' => 'Type', 'closes_at' => 'Closes', 'status' => 'Status'],
                'search'     => ['title', 'department'],
                'help'       => 'Open jobs are listed on the Careers page; applicants pick one in the form.',
                'fields'     => [
                    'title'            => ['type' => 'text', 'label' => 'Title', 'rules' => 'required|string|min:3|max:160'],
                    'slug'             => ['type' => 'slug', 'label' => 'Web address', 'rules' => 'required|slug|max:180', 'from' => 'title'],
                    'department'       => ['type' => 'text', 'label' => 'Department', 'rules' => 'nullable|string|max:100', 'half' => true],
                    'location'         => ['type' => 'text', 'label' => 'Location', 'rules' => 'nullable|string|max:120', 'default' => 'Lahore', 'half' => true],
                    'employment_type'  => ['type' => 'select', 'label' => 'Type', 'options' => ['full_time' => 'Full-time', 'part_time' => 'Part-time', 'contract' => 'Contract', 'internship' => 'Internship', 'volunteer' => 'Volunteer'], 'rules' => 'required|in:full_time,part_time,contract,internship,volunteer', 'default' => 'full_time', 'half' => true],
                    'status'           => ['type' => 'select', 'label' => 'Status', 'options' => ['open' => 'Open', 'closed' => 'Closed', 'draft' => 'Draft'], 'rules' => 'required|in:open,closed,draft', 'default' => 'draft', 'half' => true],
                    'closes_at'        => ['type' => 'date', 'label' => 'Applications close', 'rules' => 'nullable|date', 'half' => true],
                    'sort_order'       => $sort + ['half' => true],
                    'summary'          => ['type' => 'textarea', 'label' => 'Summary', 'rules' => 'nullable|string|max:500', 'rows' => 2],
                    'description_html' => ['type' => 'html', 'label' => 'Full description', 'rules' => 'nullable|string|max:100000'],
                ],
            ],

            // ---------------------------------------------------------- SEO meta
            'seo' => [
                'table'      => 'seo_meta',
                'label'      => 'SEO: page metadata',
                'singular'   => 'entry',
                'permission' => 'content.manage',
                'order'      => 'route_path ASC',
                'list'       => ['route_path' => 'Path', 'title' => 'Title', 'priority' => 'Priority', 'in_sitemap' => 'Sitemap'],
                'search'     => ['route_path', 'title'],
                'help'       => 'Title and description per URL. Paths not listed here fall back to sensible defaults from the page itself.',
                'fields'     => [
                    'route_path'     => ['type' => 'text', 'label' => 'Path', 'rules' => 'required|string|max:191', 'help' => 'Starts with a slash, e.g. /about', 'half' => true],
                    'robots'         => ['type' => 'select', 'label' => 'Robots', 'options' => ['' => 'Default (index, follow)', 'noindex,follow' => 'noindex, follow', 'noindex,nofollow' => 'noindex, nofollow'], 'rules' => 'nullable|string|max:80', 'half' => true],
                    'title'          => ['type' => 'text', 'label' => 'Title tag', 'rules' => 'nullable|string|max:200', 'counter' => 60],
                    'description'    => ['type' => 'textarea', 'label' => 'Meta description', 'rules' => 'nullable|string|max:320', 'rows' => 2, 'counter' => 160],
                    'canonical_url'  => ['type' => 'url', 'label' => 'Canonical URL', 'rules' => 'nullable|url|max:255'],
                    'og_title'       => ['type' => 'text', 'label' => 'Social title', 'rules' => 'nullable|string|max:200', 'half' => true],
                    'og_image'       => ['type' => 'image', 'label' => 'Social image (1200×630)', 'folder' => 'seo', 'rules' => 'nullable|string|max:255', 'half' => true],
                    'og_description' => ['type' => 'textarea', 'label' => 'Social description', 'rules' => 'nullable|string|max:320', 'rows' => 2],
                    'priority'       => ['type' => 'select', 'label' => 'Sitemap priority', 'options' => ['1.0' => '1.0', '0.9' => '0.9', '0.8' => '0.8', '0.7' => '0.7', '0.6' => '0.6', '0.5' => '0.5', '0.3' => '0.3'], 'rules' => 'required|in:1.0,0.9,0.8,0.7,0.6,0.5,0.3', 'default' => '0.5', 'half' => true],
                    'changefreq'     => ['type' => 'select', 'label' => 'Change frequency', 'options' => ['daily' => 'daily', 'weekly' => 'weekly', 'monthly' => 'monthly', 'yearly' => 'yearly'], 'rules' => 'required|in:daily,weekly,monthly,yearly', 'default' => 'monthly', 'half' => true],
                    'in_sitemap'     => ['type' => 'bool', 'label' => 'Include in sitemap', 'default' => 1],
                    'schema_json'    => ['type' => 'json', 'label' => 'Structured data (JSON-LD, optional)', 'rules' => 'nullable|string|max:20000'],
                ],
            ],

            // --------------------------------------------------------- redirects
            'redirects' => [
                'table'      => 'seo_redirects',
                'label'      => 'SEO: redirects',
                'singular'   => 'redirect',
                'permission' => 'content.manage',
                'order'      => 'is_active DESC, hits DESC, id DESC',
                'list'       => ['from_path' => 'From', 'to_url' => 'To', 'status_code' => 'Code', 'hits' => 'Hits', 'is_active' => 'Active'],
                'search'     => ['from_path', 'to_url', 'note'],
                'help'       => 'Old WordPress addresses → new pages. Use 301 for permanent moves, 410 for pages that are gone for good (leave “To” empty).',
                'fields'     => [
                    'from_path'   => ['type' => 'text', 'label' => 'From path', 'rules' => 'required|string|max:191', 'half' => true, 'help' => 'e.g. /old-page/'],
                    'to_url'      => ['type' => 'text', 'label' => 'To', 'rules' => 'nullable|string|max:500', 'half' => true, 'help' => '/new-page or a full URL'],
                    'status_code' => ['type' => 'select', 'label' => 'HTTP status', 'options' => ['301' => '301 permanent', '302' => '302 temporary', '410' => '410 gone'], 'rules' => 'required|in:301,302,410', 'default' => '301', 'half' => true],
                    'is_regex'    => ['type' => 'bool', 'label' => 'Pattern (regular expression)', 'default' => 0, 'half' => true],
                    'note'        => ['type' => 'text', 'label' => 'Note', 'rules' => 'nullable|string|max:255'],
                    'is_active'   => ['type' => 'bool', 'label' => 'Active', 'default' => 1],
                ],
            ],
        ];
    }

    /** @return array<string,mixed>|null */
    public static function get(string $key): ?array
    {
        $all = self::all();
        if (!isset($all[$key])) {
            return null;
        }
        $def = $all[$key];
        $def['key'] = $key;
        return $def;
    }
}
