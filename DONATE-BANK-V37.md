# V3.7 — Donate page: bank design, dropdown currency, upload-receipt link

Built on top of v3.6 (the new "upload my slip" / "new transfer" flow is kept and untouched).

* New bank-transfer section on /donate in the original design (`donate/_bank-section.php`), driven by Admin > Bank accounts.
* "Send Your Receipt" card is now **Upload Your Receipt** with a button to `/donate/transfer?mode=report`.
  The "Important Points" card links there too.
* Currency selector is a plain dropdown (flag + list). The typing/search box and its JavaScript were removed.
* Side panel: both buttons ("I already paid — upload my slip" and "I want to make a new transfer") keep the chosen currency
  when the currency dropdown changes. The "all bank accounts" button jumps to the new section.
* Side panel restyled to match the bank section.

Files: `resources/views/public/donate/index.php`, `resources/views/public/donate/_bank-section.php` (new),
`assets/js/donate.js`, `assets/css/donate-v31.css`.
