# WhatsApp: send the donor PDF from the donation pages (Wetarseel)

A **Send on WhatsApp** button sits next to **Download PDF** on:
* a donation page (online, bank, offline - any donation that has a certificate), and
* an in-kind donation page (next to "Download certificate" and on every certificate version).

One click sends the same PDF that "Download PDF" gives (receipt + thank-you / tax-exemption letter + approvals) to the donor's
WhatsApp number, from the foundation's business number. Needs the `donations.verify` / `inkind.verify` permission.
Every send is written to the audit trail (donation page shows "Last sent"; in-kind page shows it in the timeline).

## One-time setup
1. In Wetarseel create a WhatsApp **template** (category Utility, English) named `donation_pack`:
   * Header: **Document** (upload any sample PDF)
   * Body: `Assalam-o-Alaikum {{1}}, thank you for your generous {{2}} to Almarah Foundation. Please find your receipt and letter attached. JazakAllah Khair.`
   * Wait for Meta to approve it. ({{1}} = donor name, {{2}} = "donation of PKR 1,500.00" or "donation of goods")
2. Copy the API key from **API Keys** in the Wetarseel app.
3. Put it in `config.php` on the server (never in Git - the file is git-ignored and blocked by the web server):
   ```
   'wetarseel_api_key'  => 'your-key',
   'wetarseel_template' => 'donation_pack',
   ```
   (`.env` with `WETARSEEL_API_KEY` / `WETARSEEL_TEMPLATE` also works.) Empty key = the buttons are hidden.

## Notes
* Donors have not messaged the foundation in the last 24 hours, so WhatsApp only allows an approved template message.
* Phone numbers: `+...` / `00...` are international; `03xx...` is treated as Pakistan (+92).
* A send is never retried automatically (a retry could deliver the PDF twice).
