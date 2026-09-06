# WordPress installation and launch checks

This is a first implementation, not a production-certified release. Use an isolated staging site first.

## Install the packages

1. Use a single-site WordPress installation with PHP 8.1 or later and MySQL/MariaDB with InnoDB. The declared WordPress minimum is 6.6. Keep a database and file backup before changing an existing site.
2. In **Appearance → Themes → Add New → Upload Theme**, upload `sit-technology-theme-0.2.1.zip` and activate it.
3. In **Plugins → Add New → Upload Plugin**, upload `sit-technology-core-0.1.0.zip` and activate it. Core does not support multisite in this release.
4. Open **Appearance → SIT Site Setup**. On a fresh installation, choose **Create starter pages**. This creates and publishes the starter pages and selects Home as the front page. Existing matching paths are preserved by default. To upgrade an earlier SIT starter design on staging, select **Apply the latest design to existing SIT starter pages**. This replaces content only on pages marked as SIT starters and requests a WordPress revision first. Back up any editorial changes before selecting it.
5. In **Settings → Permalinks**, select a pretty-permalink structure such as Post name and save it. The designed navigation expects these paths. Verify all service and insight child pages.
6. Edit content under **Pages**. The starter uses HTML sections; preserve their classes and structure when editing layout. Keep the `[sit_component ...]` shortcodes intact: they render the ambition, service, industry and AI planner controls from theme templates. The enquiry form itself is also kept in the theme template. Assign a menu under **Appearance → Menus** if you want to replace the default primary navigation.
7. Replace the working privacy page with the reviewed company notice. Add verified business contact details, retention, processors and relevant cross-border arrangements.
8. Under **SIT Enquiries → Settings**, set an authorised team mailbox and an agreed retention period, acknowledge the reviewed notice and enable enquiries only after completing staging tests.

Do not upload the source folder or Core plugin as a theme. The theme installer has `sit-technology/style.css` at its root; the plugin installer has `sit-technology-core/sit-technology-core.php`.

## Staging acceptance checks

- Activate the theme with Core disabled: public pages must work and the form must explain that enquiries are not open.
- Activate Core: confirm all four tables use InnoDB and the enquiry capability is granted only to the intended staff roles.
- Submit a valid sample enquiry on the configured same-origin HTTPS site. Confirm one enquiry, one initial event and one outbox record are committed.
- Retry the same request/key and confirm the same reference; alter the payload with the same key and confirm conflict. Exercise concurrent identical requests.
- Submit missing/invalid fields, array values, unsupported options, excessive payloads, a foreign origin, stale/tampered tokens and more than the rate limit. Confirm no unintended records and no personal data in public responses.
- Sign in as a content editor or subscriber and verify that enquiry pages/actions are denied. Test status/assignee changes from two simultaneous authorised sessions.
- Configure a real mail transport. Verify one sample notification reaches the intended team inbox. Simulate transport failure and confirm retry and final failure reporting. Verify that the message contains no prospect contact or project content.
- Exercise the WordPress verified personal-data export and erasure workflows. Confirm related event/outbox data is removed during erasure.
- Configure the host’s real scheduler to run due WordPress events every five minutes. Confirm notification processing and the agreed retention cleanup. Review backup retention separately.
- Review mobile/tablet/desktop layouts, keyboard focus, form errors, reduced motion and screen-reader operation. Test all links and the real 404 response.
- Verify the Expertise menu and search dialog, category-plus-keyword filtering, sector deep links, all three AI planner results and the printable company overview. Search should include only published non-password-protected Pages and Posts; confirm drafts, private pages and enquiries never appear.
- On a copy of an earlier SIT installation, verify default setup preserves existing content and the optional refresh only changes SIT starter pages; inspect revisions and all 25 resulting routes.
- Ensure no real client names, badges, project outcomes or staff credentials are published without verification and permission.

## Operations

The plugin does not supply a mail service, malware scanner, CRM account, CDN, WAF or backup provider. Configure hosting capabilities according to project requirements. Avoid adding an overlapping form plugin for the same enquiry path.

The default rate limiter uses `REMOTE_ADDR` only. The host must supply an accurate trusted client connection address if it uses a reverse proxy; do not add arbitrary forwarded-header trust in the application.

Notifications show **accepted by transport**, not delivered. Failed notifications are visible in the enquiry list. There is no manual resend UI in this release; diagnose the transport and use an authorised maintenance procedure before requeueing.

Uninstall preserves business records. Erase them through the verified privacy process or agreed retention policy before removing the plugin if deletion is required. Do not drop the tables as a routine uninstall step.

## GitHub handoff

The owner has created [sitnoah/sit-techno](https://github.com/sitnoah/sit-techno) as a public repository. It receives the new WordPress source and public documentation. Private audit reports, legacy source, business data and hosting identity are excluded. Build the preview with `python3 scripts/build-preview.py`; create the two installers with `python3 scripts/package-wordpress.py /absolute/output/path`.
