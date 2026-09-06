# Release verification — 6 September 2026

## Executed successfully

| Check | Result |
|---|---|
| Preview generation | 25 content pages plus a separate 404 page |
| Internal page links and fragment anchors | All generated references resolved |
| Local styles, scripts and imagery | All referenced files present |
| Semantic structural checks | One H1 per page, no duplicate IDs, all images have alt attributes, no unresolved template markers |
| JavaScript syntax | `node --check` passed |
| PHP syntax | All 12 theme/plugin PHP files passed PHP lint in the base release; modified theme functions passed again for 0.2.0 |
| Intake boundary tests | 29 assertions passed in the base release against the real validation and origin/token functions, with narrow WordPress helper stubs |
| DOM interactions | Required-field progression, service preselection, review, sample submission, reset, menu Escape behavior, disabled live mode, failed submit, successful retry and preserved idempotency key passed |
| Discovery interactions | Expertise disclosure and focus return, keyboard tabs, safe local search, combined filters, sector fragment links, all three AI guidance paths, no planner storage and print action passed |
| Private preview deployment | Prior release published successfully; redesigned release is packaged and published through the same private Site with deployment status checked before handoff |

PHP lint and boundary tests ran with the WordPress project's PHP WASM CLI, PHP **8.5.8**. The DOM tests ran with **jsdom 30.0.1**. DOM tests simulate document interactions; they are not visual browser tests.

The Core plugin has no behavioural changes in theme 0.2.0. The existing form interactions and new discovery interactions were both executed for this redesign.

## Not verified in this environment

- A complete WordPress installation with MySQL/MariaDB and InnoDB.
- Plugin activation and database schema creation against an actual host.
- WordPress script-data output, shortcode rendering and starter-content upgrade behavior on an installed WordPress instance.
- Database concurrency, transactional rollback and real scheduled-event processing.
- Actual mail-provider delivery, bounce handling and credentials.
- Full browser/device rendering, screen-reader operation or formal accessibility conformance.
- The GitHub Actions workflow, because the new GitHub source publication and its hosted checks are separate from the local verification recorded here.

The declared minimum targets, WordPress 6.6 and PHP 8.1, have not been exercised here. The staging checklist in `installation.md` is a release gate before live client enquiries, not a claim of tests already passed.

## Apple-colour update — theme 0.2.1

Dominant apple-yellow/orange surfaces, a recoloured original bridge asset and WordPress editor tokens were added. Page/link/asset validation and JavaScript syntax were checked. The CSS parser and representative text/background contrast checks were executed for this palette; these do not establish full accessibility or visual browser conformance. Core behavior is unchanged.
