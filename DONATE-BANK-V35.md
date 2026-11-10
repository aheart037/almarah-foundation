# V3.5 — Donate page: bank transfer design

The bank part of /donate now uses the same design as the original donate page.
The donation form, currency handling, payment methods, "Report your transfer" flow and all JavaScript are unchanged.

## What changed
* New `resources/views/public/donate/_bank-section.php`: "Donate via Bank Transfer" heading, How to Donate,
  Zakat notice, Pakistan and International account cards (tap to copy), Zakat account, Important Points, closing call to action.
* Accounts come from Admin > Bank accounts. Adding, editing or hiding an account there updates this page.
  - Rows with "international" ticked go under International Donations.
  - A row whose Account type / note contains the word "Zakat" becomes the Zakat account block.
  - Sort code / SWIFT show when filled in; Zelle shows as a phone number.
* `donate/index.php`: includes the new section, adds `id="online-form"` to the form section, and the side panel button now jumps to the bank section.
* `assets/css/donate-v31.css`: side bank panel restyled to match.

Uses the bnk-* / don-* styles that are already part of site.css.
