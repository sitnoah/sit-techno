=== SIT Technology Core ===
Contributors: sitnoah
Requires at least: 6.6
Requires PHP: 8.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Private project enquiries, staff administration, audit events and a notification outbox.

== Installation ==
Upload this ZIP under Plugins, then activate it on a single-site WordPress installation with MySQL/MariaDB and InnoDB. Use SIT Enquiries → Settings to configure the team mailbox, retention and reviewed privacy notice. Enquiries are disabled until configured. Install the separate SIT Technology theme for the designed public form.

This is a first implementation. Complete the supplied staging acceptance checks before enabling live enquiries. Activation on WordPress/MySQL has not yet been verified in this development environment.

== Data and privacy ==
Business records are stored in private custom database tables. No public read API exposes enquiries. WordPress privacy export and erasure hooks are provided. Retention is an explicit setting. Uninstall preserves business records. No CRM or marketing subscription is activated.

== Changelog ==
= 0.1.0 =
Initial implementation: validated and idempotent intake, staff inbox, status and assignment, transactional audit/outbox creation, scheduled notifications, retention and privacy operations.
