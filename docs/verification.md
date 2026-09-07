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

## Theme 0.4.0 / Core 0.3.0 — 7 September 2026

- All 33 generated HTML documents pass the internal-link, fragment, local-asset, unique-ID and one-H1 checks.
- JavaScript syntax and functional DOM simulations pass: four request types, optional context, direct edits, safe plain-text download, field isolation, double-submit protection, retry identity and reset; service filtering, sector deep links, search and all three AI planner paths.
- PHP.wasm CLI (WordPress Playground package) executes 122 assertions against the real validation/workflow functions: 29 intake boundaries and 93 typed request/context/compatibility/staff workflow checks. WordPress helpers and database failures are test doubles. PHP syntax checks cover the theme, plugin and test files. The native PHP binary is unavailable in this workspace.
- New sample files and page content come from the same authored source. Installer integrity and root-entry checks are run during packaging.

No browser/device/assistive-technology review or full WordPress/MySQL/email integration test was performed for this release. Those remain staging gates; the private static preview never submits enquiries.

## WordPress parity repair — theme 0.4.1 / Core 0.3.1

The public production response loaded theme 0.4.0 and contained extra `<p>` siblings inside `.capability-editorial`, splitting arrows from block-level card links. Its cache headers reported a one-hour HTML cache. This establishes a rendering defect; it is not evidence that the user uploaded an old theme.

Checks passed: 103 rendering assertions using the official WordPress 7.1 formatting, hook and shortcode functions; all 32 raw starter layouts and all 32 managed-shortcode layouts match the authored source. Checks cover preserved edits, normal-page formatting, filter restoration, allowlisting and protected form output. Ten readiness checks verify the patch does not pause a configured schema-0.3.0 installation. The previous 122 intake/workflow assertions and JavaScript journeys also pass. PHP syntax passed for 18 files.

The formatting tests use real WordPress core functions with simulated post access and password-state helpers; they are not a full WordPress/MySQL or browser integration test. Live installation still requires the site's authenticated administrator session. No real enquiry was submitted and no existing data or settings were migrated during development.


## Mobile release — theme 0.4.2, 7 September 2026

Preview validation passed for 32 content pages and the 404, including links, anchors, assets and heading structure. The existing four form journeys and discovery interactions passed. A new mobile navigation test covers background isolation, forward/reverse focus cycling, nested-menu Escape, preservation of existing inert states, search handoff, link navigation and desktop resize. All 103 WordPress formatting/hook/shortcode checks passed, retaining the 0.4.1 rendering repair. JavaScript syntax and installer integrity were checked.

CSS introduces tablet, phone and narrow-phone breakpoints, 16 px form inputs and 44–52 px primary control targets. These are source-defined dimensions, not measured device results. No browser/device rendering or screen-reader conformance is claimed. The live WordPress site has not been updated through these checks; install the new theme and clear the host cache to apply it. Core 0.3.1 is unchanged.
