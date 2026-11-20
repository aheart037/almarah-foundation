# Donor receipt + thanks / tax-exemption letter

One PDF per donor: receipt -> thank-you letter -> FBR approval, Shariah approval, Charity Commission certificate.

Test locally:  `php -S localhost:8080 router.php` then open http://localhost:8080/donor-letter.php (works only on your own computer).
Command line:  `php tools/make-donor-letter.php "Donor Name" [donation|inkind] [amount]`

Edit wording / approval dates: `config/donor_letter.php`
When the FBR approval is renewed: replace `assets/documents/Donor-Letter-Attachments.pdf` and update the dates in the config.
