# WordPress installation and launch checks

## Contact and Team update: theme 0.5.0 / Core 0.4.0

1. Back up the site. Upload `sit-technology-core-0.4.0.zip` through **Plugins → Add New → Upload Plugin** and replace the installed SIT Core plugin. Upload `sit-technology-theme-0.5.0.zip` through **Appearance → Themes → Add New → Upload Theme** and replace the installed SIT theme.
2. Open **Appearance → SIT Site Setup → Add Contact and Team pages**. Select **Add missing Contact and Team pages**. This publishes only missing `/contact/` and `/team/` pages and preserves all existing paths and homepage settings. If a custom page already exists, review it in the editor; setup does not replace its copy. The shortcodes for deliberate manual adoption are `[sit_page name="contact"]` and `[sit_page name="team"]`.
3. Open **SIT Requests → Office details**. Add the verified public business address, address type, email, phone, hours and visiting/accessibility information for each country. Select its confirmation checkbox only when these details may be published. Unknown or unconfirmed addresses remain explicitly pending. A registered/correspondence address never offers a directions link. A confirmed visiting office can offer a click-through Google Maps link.
4. Review **SIT Requests → Settings** and the privacy notice before enabling live intake. Contact enquiries require Core 0.4.0 or newer and the existing readiness checks. Location preference does not reroute email; the authorised staff desk receives all enquiries.
5. If using a custom primary WordPress menu, add links to Contact and Team. The packaged navigation already includes both under Company, plus footer and About links.
6. Clear the hosting/page cache. Review `/contact/` and `/team/` on desktop and mobile. Test a sample enquiry on staging, confirm its topic/region in the desk, and verify the configured mail worker separately.

The Team page intentionally contains 20 **proposed role mockups**, not invented staff profiles, actual vacancies or confirmed headcount. Replace these with approved people and responsibilities before representing them as a current team. No staff accounts are created.

Core 0.4.0 retains schema 0.3.0; this release uses a separate office option and optional enquiry detail fields. Older request payloads and hashes remain compatible. Installing the theme alone leaves Contact intake closed and addresses pending. Nothing is published or overwritten automatically during theme activation.

The Sites preview is a separate static design publication. Its forms never send or save enquiries and its office addresses remain pending. Publishing that preview does not install these packages on `sit-consultancy.com`.

## About-page update: theme 0.4.3 / Core 0.3.1

1. Back up the site, upload `sit-technology-theme-0.4.3.zip` under **Appearance → Themes → Add New → Upload Theme**, and replace the installed SIT theme. Keep Core 0.3.1 active; it is unchanged.
2. Open **Appearance → SIT Site Setup → Update only the About page**. If it already uses `[sit_page name="about"]`, the new page design arrives with the theme; skip to the cache step.
3. For an older HTML starter, review the existing About copy, tick the About-only confirmation and select **Apply the new About design**. Do not use the separate whole-site starter refresh just to update About.
4. Clear the hosting page cache. Check `/about/`, all three delivery tabs, six FAQ disclosures, resource links, and portrait/landscape layouts.

The action checks administrator and page-edit permissions, a WordPress nonce, the exact About starter identity and a hash of the reviewed copy. It refuses a missing, custom, trashed or changed page. It backs up the exact previous body in private `_sit_about_design_backup` post metadata before replacing the body, and also requests a WordPress revision. A failed backup stops the update. Title, status, parent, SEO metadata, other pages, plugin settings and operational records are preserved. These checks do not substitute for a full site/database backup or WordPress staging verification.

To recover prior copy, use the page's WordPress revisions where available. When revisions are disabled, an authorised site administrator can retrieve the saved `post_content` from `_sit_about_design_backup` post metadata and restore that body through the page editor. Multiple backups are retained rather than overwritten.

## Mobile update: theme 0.4.2 / Core 0.3.1

Upload `sit-technology-theme-0.4.2.zip` through **Appearance → Themes → Add New → Upload Theme**, then choose to replace the existing SIT theme. Keep Core 0.3.1 active; this mobile update does not require a plugin or database change. If Core is older, use the 0.3.1 installer described below.

The shared mobile stylesheet loads after the existing design in both WordPress and the preview, using the theme version to refresh asset URLs. This release includes the 0.4.1 rendering repair. There is no need to refresh starter Pages for the mobile styling; existing edited content is preserved. Clear the IONOS/hosting page cache after installation.

Check portrait and landscape layouts on your devices, the menu and search, service navigation, and every step of the project form. The release checks cover source structure and simulated interactions; physical-device rendering has not been verified here.

## Rendering repair: theme 0.4.1 / Core 0.3.1

The live site was already using theme 0.4.0. WordPress automatic paragraph formatting (`wpautop`) split block-level links and inserted extra grid items, causing the layout to differ from the preview. Theme 0.4.1 suspends that filter only while rendering recognised SIT starter Pages, then restores it. Existing saved copy is preserved and ordinary WordPress content keeps normal formatting.

1. Take a file/database backup. Install `sit-technology-core-0.3.1.zip` through **Plugins → Add New → Upload Plugin** and replace the installed SIT Core plugin.
2. Install `sit-technology-theme-0.4.2.zip` through **Appearance → Themes → Add New → Upload Theme** and replace the installed SIT theme.
3. Keep the theme and plugin active. No page refresh is required to repair existing 0.4.0 HTML layouts.
4. Clear the IONOS/hosting page cache and reload the public site. The observed live cache can retain HTML for one hour; a normal browser refresh alone may continue receiving cached output.
5. Open **SIT Requests → Settings** and check Installation status. Core 0.3.1 uses schema 0.3.0, so an existing current database does not require another update. Older schemas still require the explicit database action. No settings are automatically enabled or replaced.
6. Compare the six capability cards in two columns, the three engagement choices, the sample rows and nine service pages. Check the form review/download and verify it stays closed unless previously configured.

New or deliberately refreshed starter pages now use `[sit_page name="home"]` (or the relevant allowlisted page name). The shortcode loads the packaged layout after WordPress text formatting, so future theme changes carry through. To adopt this for existing starters, use the explicit **Apply the latest design** option after backing up any editorial changes. This replaces their copy; it is optional for the immediate rendering repair.


This is a first implementation, not a production-certified release. Use an isolated staging site first.

## Install the packages

1. Use a single-site WordPress installation with PHP 8.1 or later and MySQL/MariaDB with InnoDB. The declared WordPress minimum is 6.6. Keep a database and file backup before changing an existing site.
2. In **Appearance → Themes → Add New → Upload Theme**, upload `sit-technology-theme-0.4.2.zip` and activate it.
3. In **Plugins → Add New → Upload Plugin**, upload `sit-technology-core-0.3.1.zip` and activate it. Core does not support multisite in this release.
4. Open **Appearance → SIT Site Setup**. On a fresh installation, choose **Create starter pages**. This creates and publishes the starter pages and selects Home as the front page. Existing matching paths are preserved by default. To upgrade an earlier SIT starter design on staging, select **Apply the latest design to existing SIT starter pages**. This replaces content only on pages marked as SIT starters and requests a WordPress revision first. Back up any editorial changes before selecting it.
5. In **Settings → Permalinks**, select a pretty-permalink structure such as Post name and save it. The designed navigation expects these paths. Verify all service and insight child pages.
6. Edit content under **Pages**. The starter uses HTML sections; preserve their classes and structure when editing layout. Keep the `[sit_component ...]` shortcodes intact: they render the ambition, service, industry and AI planner controls from theme templates. The enquiry form itself is also kept in the theme template. Assign a menu under **Appearance → Menus** if you want to replace the default primary navigation.
7. Replace the working privacy page with the reviewed company notice. Add verified business contact details, retention, processors and relevant cross-border arrangements.
8. Under **SIT Requests → Settings**, set an authorised team mailbox and an agreed retention period, acknowledge the reviewed notice and enable enquiries only after completing staging tests.

Do not upload the source folder or Core plugin as a theme. The theme installer has `sit-technology/style.css` at its root; the plugin installer has `sit-technology-core/sit-technology-core.php`.

## Upgrade from earlier SIT releases

Back up and rehearse on a copy of the database. Install Core 0.3.0 first, complete its database update, then install theme 0.4.0. If WordPress keeps the plugin active during replacement, use the administrator notice **Update SIT request database**. Intake, staff mutations and scheduled work pause until the schema update succeeds. Activation also runs the upgrade. Existing rows keep their references, timestamps, status, owner, details and retry hashes. Rows from Core 0.1 gain the General enquiry default and version 1; Core 0.2 typed requests retain their existing data. No legacy-system data is imported. Existing settings are preserved: previously enabled intake resumes after a successful upgrade; a new installation stays disabled until configured.

The upgraded request form is a theme template and updates without refreshing editorial Pages. Review the expanded privacy notice separately. Use the starter refresh only if you intend to replace existing SIT starter copy. The new theme keeps intake closed with Core older than 0.3.0 to prevent typed details being discarded.

## Staging acceptance checks

- Activate the theme with Core disabled: public pages must work and the form must explain that enquiries are not open.
- Activate Core: confirm all four tables use InnoDB and the enquiry capability is granted only to the intended staff roles.
- Submit a valid sample for each of the four request types on the configured same-origin HTTPS site. Confirm one enquiry, one initial event and one outbox record are committed.
- On an upgraded database, compare existing record counts/references/status/assignment, retry an old untyped payload and verify the same reference. Repeat the upgrade and verify it does not duplicate or delete records. Confirm failed schema verification leaves intake paused.
- Test consultation time-zone validation, required team roles, type changes, hidden-field exclusion and privacy export of additional details.
- Retry the same request/key and confirm the same reference; alter the payload with the same key and confirm conflict. Exercise concurrent identical requests.
- Submit missing/invalid fields, array values, unsupported options, excessive payloads, a foreign origin, stale/tampered tokens and more than the rate limit. Confirm no unintended records and no personal data in public responses.
- Sign in as a content editor or subscriber and verify that enquiry pages/actions are denied. Test status/assignee changes from two simultaneous authorised sessions, stale versions, forbidden status jumps, closed-request reopening and revoked assignees. Check combined filters and pagination.
- Configure a real mail transport. Verify one sample notification reaches the intended team inbox. Simulate transport failure and confirm retry and final failure reporting. Verify that the message contains no prospect contact or project content.
- Exercise the WordPress verified personal-data export and erasure workflows. Confirm related event/outbox data is removed during erasure.
- Configure the host’s real scheduler to run due WordPress events every five minutes. Confirm notification processing and the agreed retention cleanup. Review backup retention separately.
- Review mobile/tablet/desktop layouts, keyboard focus, form errors, reduced motion and screen-reader operation. Test all links and the real 404 response.
- Verify the Expertise menu and search dialog, category-plus-keyword filtering, sector deep links, all three AI planner results and the printable company overview. Search should include only published non-password-protected Pages and Posts; confirm drafts, private pages and enquiries never appear.
- On a copy of an earlier SIT installation, verify default setup preserves existing content and the optional refresh only changes SIT starter pages; inspect revisions and all 32 resulting routes.
- Ensure no real client names, badges, project outcomes or staff credentials are published without verification and permission.

## Operations

The plugin does not supply a mail service, malware scanner, CRM account, CDN, WAF or backup provider. Configure hosting capabilities according to project requirements. Avoid adding an overlapping form plugin for the same enquiry path.

The default rate limiter uses `REMOTE_ADDR` only. The host must supply an accurate trusted client connection address if it uses a reverse proxy; do not add arbitrary forwarded-header trust in the application.

Notifications show **accepted by transport**, not delivered. Failed notifications are visible in the enquiry list. There is no manual resend UI in this release; diagnose the transport and use an authorised maintenance procedure before requeueing.

Uninstall preserves business records. Erase them through the verified privacy process or agreed retention policy before removing the plugin if deletion is required. Do not drop the tables as a routine uninstall step.

## GitHub handoff

The owner has created [sitnoah/sit-techno](https://github.com/sitnoah/sit-techno) as a public repository. It receives the new WordPress source and public documentation. Private audit reports, legacy source, business data and hosting identity are excluded. Build the preview with `python3 scripts/build-preview.py`; create the two installers with `python3 scripts/package-wordpress.py /absolute/output/path`.



## Apply the new landing page and service design

The logo, CSS and guided form update with the theme. Existing WordPress page copy remains stored in the database. To apply this release’s homepage and service content, back up editorial work and use **Appearance → SIT Site Setup → Apply the latest design to existing SIT starter pages** on staging. Setup also creates the engagement comparison and six sample-document pages. The unchecked action preserves existing copy while adding missing pages.

Review optional brief context in the staff desk and privacy export. Download a sample project brief and verify it contains the reviewed answers without submitting. Check all five template downloads and print layouts. A downloaded brief is a local plain-text file containing contact details; there is no automatic local draft storage or resume service.

Core 0.3 stores optional context in the existing details column. Its explicit upgrade verifies the existing schema and advances the version without replacing records. Legacy canonical payloads remain compatible, including when optional fields are blank. The theme requires Core 0.3 or newer before opening intake.
