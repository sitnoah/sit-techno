# Release verification — 6 September 2026

## Theme 0.3.0 / Core 0.2.0

| Check | Result |
|---|---|
| Preview build and structural validator | 25 content pages plus 404; links, fragments, local assets, unique IDs and heading structure passed |
| PHP syntax | All 15 theme, plugin and PHP test files passed PHP lint |
| Base intake boundaries | 29 validation and origin/token assertions passed |
| Typed requests and staff workflow | 59 additional assertions passed: four request types, malformed/forged fields, canonical legacy compatibility, privacy detail filtering, allowed transitions, staff/assignee authority, schema guard, stale versions and transaction failure handling |
| Public form interactions | All four journeys, required conditional fields, type/service selection, review, irrelevant-field exclusion, switching, demo reset, disabled live mode, double-submit lock and successful idempotent retry passed |
| Discovery interactions | Expertise disclosure, keyboard tabs, safe local search, combined service filters, sector links, three AI guidance paths, no planner storage and print action passed |
| JavaScript and CSS | JavaScript syntax and CSS parsing checked for the shared assets and request-desk stylesheet |
| WordPress packages | Separate theme and Core archives checked for integrity, correct install roots and matching release versions |

PHP runs use the WordPress project's PHP WASM CLI, PHP **8.5.8**. DOM tests use **jsdom 30.0.1**. PHP workflow tests execute the real update function against database fault doubles; they verify application behavior but do not establish MySQL transaction behavior. DOM tests simulate document interaction and are not visual browser tests.

## Deployment and hosted checks

The private preview is a static design demonstration. It does not run WordPress or send enquiries. Its deployment status is checked through the hosting service before handoff. Public GitHub source and its hosted Actions status are separate from the successful local checks above; a failed hosted run is not treated as a local test pass or ignored as an application result.

## Staging gates not executed here

- Full WordPress installation, theme/plugin activation and minimum targets WordPress 6.6 / PHP 8.1.
- MySQL/MariaDB InnoDB table creation, upgrade of an actual 0.1.0 database, repeat upgrade, row locking, concurrent retries and transactional rollback.
- WordPress output/localisation, current theme with old Core disabled, starter-page preservation and opt-in refresh.
- Request capability denial on real WordPress accounts, concurrent staff sessions and actual privacy export/erasure.
- Scheduled notifications, actual provider delivery and retention cleanup on a real host.
- Browser/device rendering, screen-reader operation, formal accessibility conformance, performance/load testing and production monitoring.

The acceptance checklist in [installation.md](installation.md) must be completed before live client intake. No legacy business records have been imported, and no original application has been changed.

## Earlier design releases

Theme 0.2.0 added discovery tools and the consulting-site redesign. Theme 0.2.1 introduced dominant apple-yellow/orange surfaces and the recoloured original bridge artwork. Their structural, interaction, CSS and representative colour-contrast checks were recorded during implementation. Those checks did not establish browser or accessibility conformance; Core behavior was unchanged until this 0.2.0 plugin release.


## Logo replacement — theme 0.3.1

Replaced the shared header/footer wordmark with the approved transparent artwork and added a matching symbol favicon. Checked all generated pages, logo references and accessible names; responsive frame dimensions and CSS syntax; modified PHP syntax; and theme archive integrity. Core 0.2.0 and request workflows are unchanged. No browser/device visual testing was performed for this asset-only update.


## SIT Consultancy logo — theme 0.3.2

The shared header/footer now use the owner-approved SIT Consultancy wordmark with matching alternative text and home-link names. Rebuilt the shared page sources and static preview; checked logo references, source image dimensions, responsive frame bounds, version consistency, every generated page’s local assets and the direct-upload ZIP structure/integrity. The background cleanup uses a white-matte image with CSS multiply blending on the existing warm surfaces.

This release changes branding assets and theme version metadata only. Core 0.2.0 is unchanged. Browser/device rendering and full WordPress activation were not exercised for this update. The current runtime has no PHP executable; the sole functions.php edit changes the version string.
