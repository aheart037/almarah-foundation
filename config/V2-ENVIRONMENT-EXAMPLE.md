# V2 environment configuration example (no secrets)

This is **documentation, not an active `.env` file**. Set these variables through your host's secret manager or a private environment file outside the public document root; never paste actual credentials into this repository or the V2 ZIP. Blank values mean **configure for your environment**. Do not enable a gateway until its bank-provided sandbox/live settings and return URLs have been tested.

```text
APP_ENV=production
APP_DEBUG=false
APP_URL=
APP_TIMEZONE=UTC
APP_CURRENCY=PKR
APP_KEY=
SESSION_SECURE_COOKIE=true
SESSION_SAMESITE=Lax
DB_CONNECTION=mysql
DB_HOST=
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
MAIL_MAILER=smtp
SMTP_HOST=
SMTP_PORT=587
SMTP_ENCRYPTION=tls
SMTP_USERNAME=
SMTP_PASSWORD=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME=Almarah Foundation
MAIL_REPLY_TO=
MAIL_QUEUE_ENABLED=true
MAIL_VERIFY_PEER=true
FUNDRAISE_URL=
FUNDRAISE_DB_HOST=
FUNDRAISE_DB_DATABASE=
FUNDRAISE_DB_USERNAME=
FUNDRAISE_DB_PASSWORD=
MEEZAN_ENABLED=false
MEEZAN_ENVIRONMENT=sandbox
MEEZAN_API_URL=
MEEZAN_RETURN_URL=
MEEZAN_USERNAME=
MEEZAN_PASSWORD=
ETISALAT_ENABLED=false
ETISALAT_ENVIRONMENT=sandbox
ETISALAT_CUSTOMER=
ETISALAT_USERNAME=
ETISALAT_PASSWORD=
```

The fundraising DB bridge is optional; only configure it if that separate deployment is authorized and available. Gateway values may also be managed through the existing protected admin settings; see the original `config/payments.php` for the full key catalogue. Do not use this example as a production configuration. Keep actual environment files and backup archives outside the web root, restrict filesystem permissions, and confirm the web server refuses direct access to `storage/`, `config/`, `.env` and private backups.
