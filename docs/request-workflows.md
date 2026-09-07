# Unified request desk — theme 0.5.0 / Core 0.4.0

The public website and protected WordPress administration now share one request domain. Every new submission starts unassigned with status **New**. The staff team reviews and assigns it; no automatic qualification, quotation, meeting reservation or talent matching is implied.

## Public journeys

All four use `/start-a-project/`. The optional `type` query selects a journey. Existing service and ambition links still preselect relevant fields. Type changes hide and disable unrelated questions; review and submission include only the selected type’s answers.

| Type | Direct entry | Additional questions |
|---|---|---|
| General enquiry | `?type=enquiry` | No extra questions |
| Consultation | `?type=consultation` | Preferred video/phone/email conversation, time zone, optional availability |
| Software project | `?type=software-project` | Idea/prototype/existing stage; web/mobile/platform/integration/undecided product |
| Dedicated team | `?type=dedicated-team` | Required roles and skills, approximate team size, expected duration |

Shared fields: ambition, service interest, challenge, indicative GBP budget, ideal start, name, work email, organisation and consent to respond. The challenge must be 20–3,000 characters. Time zone is required for consultations; roles are required for teams. Availability expresses a preference and does not create a booking. Contact starts from the submitted email; no phone number is requested at intake.

The three steps cover the request, contact details and review. During a live submission, controls are locked against double submission or editing. A retry uses the same idempotency key. A changed payload with that same key returns a conflict; the visitor can start again. A successful response includes a private request reference. There is no public reference lookup.

The private static preview is an explicit demonstration. It sends no answers and stores no enquiry data. The live form requires the paired Core plugin, a current database schema and the existing activation settings.

## Staff desk

Open **SIT Requests** in WordPress. The menu slug and `manage_sit_enquiries` capability remain unchanged for existing staff links and role configuration.

- Counts show all requests grouped by status, including a real zero state.
- Combine request type, status, all/mine/unassigned ownership and reference/organisation search.
- Lists show 25 requests per page, owner, received UTC timestamp and notification state.
- Details show contact and request fields, tailored answers, assignment, permitted next statuses and the latest 50 activity events.
- Only request-capable staff can access the desk. Those staff can review **all** requests; assignment is not a data-access boundary.
- Updates require WordPress nonce verification, staff capability, an eligible assignee, a row lock, a current version and an allowed transition. Update, version increment and audit event are transactional.

| Current status | Allowed destinations, in addition to staying in place |
|---|---|
| New | Reviewing, Closed |
| Reviewing | Awaiting reply, Proposal, Closed |
| Awaiting reply | Reviewing, Closed |
| Proposal | Reviewing, Agreed, Closed |
| Agreed | Closed |
| Closed | Reviewing |

**Agreed** is a staff-managed workflow label. It does not generate a contract, invoice or payment. Internal codes are `new`, `reviewing`, `waiting`, `proposal`, `won` and `closed`.

## Persistence and upgrades

The existing `sit_enquiries` table remains the system of record. New columns are `request_type` (default `enquiry`), `details` (validated JSON text, nullable for older rows) and `version` (default 1). A request-type/status index supports filtering. Existing references, retry hashes, timestamps, contact fields, statuses, ownership, events and notification records remain in place.

Core activation or the protected **Update SIT request database** action runs the additive schema update. Use a backup and staging copy first. The schema version is recorded only after required table/column checks succeed. During a version mismatch, intake, the desk and scheduled workers pause. Settings are preserved: an already-enabled installation resumes intake after a successful update; new installations require explicit configuration.

Existing untyped clients keep their original canonical payload representation and retry hash. New typed payloads reject unknown root fields, privilege fields and irrelevant type-specific fields. Extra answers are sanitised and stored in deterministic order. The request type and detail JSON cannot be edited through the staff status/assignment action.

Privacy export includes known additional fields with readable labels. Existing verified erasure and retention remove the whole request with its events and outbox. Notification emails contain only the reference and protected desk link; mail transport acceptance is not delivery confirmation.

## Verification and remaining work

Local checks cover all four journeys, required fields, payload isolation, type switching, review, disabled live mode, double submissions, retry identity and privacy-safe sample preview behavior. PHP checks cover typed validation, legacy canonical compatibility, forbidden fields, transitions, staff authority, stale versions and database failure handling using test doubles.

Actual WordPress activation, InnoDB migration/concurrency, end-to-end privacy operations and real mail delivery remain staging gates. Browser/device and assistive-technology checks have not been performed for this release.

Scheduling integration, request discussions, quotations, recruitment, protected CVs, CRM, data migration and production cutover remain later milestones. See the [migration blueprint](wordpress-migration-blueprint.md).

## Guided brief context — Core 0.3

All four typed requests can include optional `audience` (500 characters), `systems` and `integrations` (600 each), `outcomes` and `constraints` (800 each). Scalar type and length checks run on the server; values are plain text and stored in canonical field order in `details`. Missing or blank optional values are omitted, preserving earlier request hashes. Unknown fields and fields for another request type remain rejected. The request-body cap is 48,000 bytes to accommodate the bounded Unicode fields.

The review screen provides separate request/contact edit actions and a plain-text download with no network request. It does not save a draft in browser storage or send the request. Submission still uses the signed token and stable idempotency key. The staff desk and verified privacy export render known optional fields through the same detail mapper. These values do not enter notification emails.


## Compact contact journey

`/contact/` adds a compact general enquiry form. Name, email, organisation/individual, a 20–3,000 character message and consent are required. Topic supports general, project, procurement/NDA, partnership and careers questions. Preferred location supports no preference, United Kingdom, Liberia and Côte d’Ivoire. The fixed general-enquiry context uses `explore`, `discuss` and `flexible`; it does not claim a price or timetable. Both additional fields are optional on the server for existing clients and displayed in the staff details/privacy export when present.

No topic or location promises a booking, application or automatic regional assignment. All records enter the existing request desk and notification workflow. After an ambiguous failure, retry uses the same immutable payload and key. A clearly labelled new-attempt action unlocks editing and warns that the previous enquiry may already have been saved. No personal draft survives a page reload.
