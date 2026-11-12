# Auth pages: Almarah Foundation redesign

## Changed / new files (upload to repo, same paths)
- assets/css/auth-foundation.css  (NEW, shared styles for all four pages)
- resources/views/public/auth/login.php
- resources/views/public/auth/forgot-password.php
- resources/views/public/auth/reset-password.php
- resources/views/public/auth/verify-email.php

## What was wrong
1. "Create an account" -> /register: the route was removed from routes/web.php
   ("no public self-registration on the main site"), so the link fell into the
   /{slug} catch-all and returned 404. "Browse fundraisers" (/fundraisers) was
   also a dead route.
2. reset-password.php and verify-email.php linked assets/auth-pages.css, which
   does not exist in the repo, so the emailed reset page opened unstyled.
   (style.css, which holds the old .auth-shell rules, is not loaded by the layout.)

## What changed
- Login is now Almarah Foundation branded; no fundraiser wording anywhere.
- Create-account link replaced by "Contact our team" (/contact); donor link
  now points to /donate.
- All four pages share one stylesheet that exists (auth-foundation.css).

## If the reset email never arrives
That is mail delivery, not the link: check Admin > Settings > Email (send a
test) and Admin > System > Email queue, and that the mail:work cron runs.
Also, /forgot-password redirects signed-in users to their dashboard
(GuestMiddleware), so test it in a private window.
