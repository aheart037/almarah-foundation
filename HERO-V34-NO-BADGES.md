# V3.4 — Plain page-title heroes + motion

Only `assets/css/hero-v32.css` changed (new section 17 at the end of the file).

* Removed from every page-title hero: the small pill above the title and the rows of glass badges/chips
  (About, Careers, Financial Reports, Registrations & MoU, Newsletter, MoUs, CEO/Chairperson/E-Book, Contact, Projects,
  Members, Banks, Donate, Media, Volunteer, program pages, CMS pages).
* The short tag-line under the leadership / e-book titles is now plain text.
* Added motion (CSS only): breathing ripple rings, two layers of golden sparks drifting upward, slow sheen on the gold title word.
  Disabled automatically for visitors with "reduce motion" turned on.

The badge markup is still in the PHP files but hidden by CSS. To bring any back, delete the "(a)" rule in section 17.
Rollback: restore the previous `hero-v32.css`.
