<?php

declare(strict_types=1);

use App\Core\Env;

return [
    /*
     | Wetarseel WhatsApp Business API (https://developers.wetarseel.ai/api).
     | Empty key = the "Send on WhatsApp" buttons stay hidden.
     | The key is a secret: put it in .env on the server only, never in Git.
     */
    'wetarseel' => [
        'api_key'  => Env::secret('WETARSEEL_API_KEY'),
        'base_url' => rtrim((string) Env::get('WETARSEEL_BASE_URL', 'https://app.wetarseel.ai/api/v1'), '/'),
        // Meta-approved template with a DOCUMENT header and two body variables (see WHATSAPP-WETARSEEL-SETUP.md).
        'template' => (string) Env::get('WETARSEEL_TEMPLATE', 'donation_pack'),
        'timeout'  => (int) Env::get('WETARSEEL_TIMEOUT', 30),
    ],
];
