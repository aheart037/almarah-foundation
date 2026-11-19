# Admin glass redesign (all admin pages)

Every admin page now uses the same glass look: homepage-slider photo background (`Header-4`), pill top bar,
glass sidebar, glass content stage, floating bottom dock, light/dark switch. Routes, controllers, database,
permissions and the public site are untouched; existing page markup was NOT edited — the site's own classes
(panel, stat-card, data-table, btn, badge, alert, forms…) are restyled by `admin-glass.css`.

## Files
| File | Status |
|---|---|
| `assets/css/dashboard-glass.css` | new — tokens, background, top bar, sidebar, stage, dashboard widgets |
| `assets/css/admin-glass.css` | new — glass styling for every shared admin component |
| `assets/js/dashboard-glass.js` | new — theme toggle, dock marker, "Find a page", dashboard chart/filters/refresh |
| `resources/views/partials/admin-dock.php` | new — bottom dock (role-aware, highlights current section) |
| `resources/views/layouts/admin.php` | changed — loads the glass CSS/JS + background on every admin page |
| `resources/views/admin/dashboard.php` | changed — glass dashboard markup (same data and permission checks) |

If your zip tool renames it, the script ships as `assets/js/dashboard-glass.js.txt` — rename to `dashboard-glass.js`.
Old `dashboard-v31.css/js` are no longer loaded (left in place for rollback).

## Notes
- Theme choice is remembered in the browser. The bell (dashboard only) lists "Needs your attention".
- "Find a page" searches the sidebar links, so it only offers pages the signed-in role may open.
- Dashboard counters use `data-gg-count` (the old `data-count` collided with `app.js`, which could overwrite money values).
- To switch the glass look off, set `$glass = false;` in `layouts/admin.php`.
