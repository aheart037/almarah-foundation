# Delete, Edit and WhatsApp: install notes

Only these three features changed. Copy the files in this zip over the repo (same paths), then:

1. **Run the SQL once** (phpMyAdmin / SQL console), in this order:
   - `setup/6-run-sixth-0024-delete-permissions.sql`  (delete permissions)
   - `setup/7-run-seventh-0025-edit-permissions.sql`  (edit permissions)
   - `setup/8-run-eighth-0026-deleted-records.sql`    (the Deleted records table; run it before anyone deletes, otherwise a delete is refused and nothing is removed)
   All are idempotent and are also in `database/migrations/`.
2. **WhatsApp:** add `WETARSEEL_API_KEY` (and optionally `WETARSEEL_TEMPLATE`) to `.env`. See `WHATSAPP-WETARSEEL-SETUP.md`. Blank key = buttons hidden.
3. **Give people the permissions:** Admin > Roles & permissions > Edit. Super Admin has everything automatically.

| Permission | Allows | Default after the SQL |
|---|---|---|
| `donations.delete` | delete junk (never paid) donations | Super Admin |
| `inkind.delete` | delete pending / rejected in-kind donations | roles with `inkind.verify` |
| `forms.delete` | delete website form entries | roles with `forms.view` / `volunteers.manage` |
| `donations.edit` | edit offline (staff-recorded) donations | Super Admin |
| `inkind.edit` | edit in-kind donations | roles with `inkind.verify` |

Until the SQL is run, only Super Admin can delete or edit.

## Reasons, and the Deleted records page

- **Every delete asks for a reason** (5-500 characters), on the single-delete forms and the bulk-delete bars (one reason covers the ticked rows). Nothing is deleted without it.
- **Every edit asks for a reason** (same limits). It is saved in the donation's review history / the in-kind history and in the audit log. A save that changes nothing needs no reason.
- **Admin > Deleted records** lists every deletion: when, what kind, reference, a short summary, the reason and who deleted it, with search and a kind filter. Each person sees only the kinds they may delete (`donations.delete`, `inkind.delete`, `forms.delete`).
- Deleted entries are removed from every normal screen (lists, ledgers, reports, inbox); only this page shows them. Website form entries are logged by box and id only, with no name, email or message, so data-removal requests are honoured.

## How Edit behaves

**Offline donations** (button on the donation page, only for donations staff recorded):
- Not yet verified: every field can be changed, and a new bank slip can replace the old one. If it was already part-way through review, or rejected, saving restarts review from the first stage (a `reset` review event, the same convention the existing amount correction uses).
- Verified, receipted, certificate issued, or has refund / adjustment / reconciliation records: money and donor identity are locked. Only donor email, phone and internal note can be corrected (so receipts and WhatsApp reach the donor).
- Never editable: online (gateway) payments, transfers the donor reported themselves, failed / cancelled / refunded entries.

**In-kind donations** (button on the in-kind page): donor, items, hand-over, acknowledgment and referrer can be edited in any status. Photos and status are not changed. For a verified donation the certificate already issued is not rewritten; use Re-issue afterwards.

Every save writes a history event and an audit record in the same database transaction (money fields with old and new values, personal data by field name only).

## Merge notes (the two uploaded packages overlapped)
`routes/admin.php`, `InKindController.php` and `resources/views/admin/inkind/show.php` were changed by both the delete and the WhatsApp package; they are merged here, plus the edit routes / button.

## Tests
`php tests/record_deletion_test.php`, `php tests/whatsapp_send_test.php`, `php tests/record_edit_test.php` need no database. `tests/form_pages_test.php` was updated by the delete package and needs the MySQL test database as before.
