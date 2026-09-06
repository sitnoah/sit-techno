# SIT Technology — WordPress replacement blueprint

## Architecture

Use one WordPress installation for the public consultancy website and protected staff administration. The theme owns presentation. SIT Core owns private enquiries, lifecycle events, notification outbox and privacy operations. Extend that domain through focused Operations, Recruitment and Content modules, rather than storing private workflow state in theme templates or public Posts.

Public Team profiles and case studies are editorial content. Staff login accounts and private client records are separate entities. Public search indexes approved public content only.

| Module | Responsibilities | Current state |
|---|---|---|
| SIT Technology theme | 25 public pages, responsive design, discovery tools, project form | Implemented; theme 0.2.1 |
| SIT Core | Private enquiry intake/inbox, assignment, state changes, events, durable notifications and retention | Implemented first version; Core 0.1.0; full host integration pending |
| Operations extension | Consultation, software brief and team request types; request-scoped messages; scheduling state; qualification and quotes | Planned |
| Recruitment extension | Jobs, job applications, open talent registration, private CVs, screening and applicant communications | Planned |
| Content extension | Structured services, sectors, technologies, team profiles, locations and permissioned case studies/testimonials | Planned; current starter Pages remain editable |
| Integration adapters | Confirmed booking events, CRM mapping/reconciliation, opt-in newsletter delivery | Planned; external services require configuration |
| Migration tools | Dry-run validation, legacy-ID map, record counts, reconciliation, redirect map and cutover report | Planned |

## Staff experience

The staff home should open with work requiring action: new requests, unassigned work, upcoming confirmed consultations, applications awaiting review and failed notifications. Use actual database counts and clear empty states.

Navigation: **Overview · Requests · Appointments · Talent · Organisations · Content · Reports · Settings**. Show only areas permitted by capabilities, and enforce those same permissions in every handler and query. Native WordPress content editing, revisions and account administration remain available to their authorised roles.

| Role | Intended authority |
|---|---|
| Site administrator | System configuration, invitation/capability management and recovery |
| Content editor | Approved public content; no access to enquiries or CVs by default |
| Business development | Assigned prospects/briefs, qualification, proposals and booking coordination |
| Delivery lead | Assigned technical briefs, delivery discussion and agreed project transitions |
| Recruiter | Jobs, applications and controlled CV access |
| Read-only reviewer | Explicitly scoped reports/records, without write privileges |

Roles are proposed mappings, not privileges already installed by Core. Grant only verified business authority. Existing Core grants its enquiry capability to administrators on activation.

## Implementation sequence

| Stage | Deliverable | Acceptance gate |
|---|---|---|
| 1. Foundation | Theme, private intake and a real WordPress staging installation | Theme activation, all routes, form → database → inbox → notification → privacy lifecycle verified |
| 2. Requests | One registry for enquiry, consultation, software brief and dedicated-team request; server validation and retry keys | Every enabled public form reaches the correct queue and assignee; all denied-role and duplicate cases pass |
| 3. Scheduling and discussion | Explicit requested/confirmed/cancelled booking state; verified provider events; one conversation per request | Duplicate/out-of-order provider events handled; no cross-request visibility; internal/client messages distinguished |
| 4. Recruitment | Jobs, open applications, staged applicant input, protected CVs, screening and retention | Ownership, expiry, MIME/size checks, scanning and private download verified; no invented account verification |
| 5. Editorial and evidence | Structured content, approval state, media, team/location profiles and case studies | Draft/private content cannot enter search, feeds or pages; all public claims have approved evidence |
| 6. CRM and newsletter | Durable adapters, consent mapping, retry/backoff, deduplication and reconciliation | Provider credentials configured; authorised sample deliveries verified; separate marketing preferences |
| 7. Migration and cutover | Rehearsed import, route redirects, backup, freeze window and rollback plan | Counts and sampled records reconcile; no credential fields imported; staff permissions and old-system retirement verified |

## Data and lifecycle rules

- Store a unique `(source_system, source_collection, source_id)` mapping, import-run ID and content checksum for migrated records. A repeated dry run must not create duplicates.
- Separate an enquiry from a confirmed appointment, a team requirement from an applicant, and a public company profile from a private CRM organisation.
- Keep threads and attachments tied to a canonical request/application ID. Validate access on each read/download/write, including exports.
- Keep immutable lifecycle events separate from mutable request state. Use transactions and version checks where multiple operators can update a record.
- Send only necessary reference data in notifications. Use a durable outbox with retry and visible failure states.
- Treat old credential material and ambiguous records as excluded/quarantined migration inputs. Use fresh invitations or approved identity-migration procedures.
- Preserve consent evidence and agreed retention. Importing an email address does not authorise marketing.

## Route migration

Preserve search value with an explicit 301 map, reviewed on staging. Map meaningful content, not every route to the homepage. Typical candidates:

| Existing route family | Replacement destination |
|---|---|
| `/about_us`, `/company`, `/who_we_are` | `/about/` with content consolidation |
| `/services/web_development`, `/services/mobile_development` | `/services/software-engineering/` or future dedicated pages |
| `/services/cloud_adoption_&_engineering` | `/services/cloud-and-devops/` |
| `/services/quality_assurance` | `/services/quality-and-security/` |
| `/services/technology_audit_&_Consultancy` | `/services/technology-strategy/` |
| `/services/book_a_call`, `/contact_us`, `/contact_us/enquiry` | Appropriate request/consultation entry when its workflow is enabled |
| `/pricing`, `/pricing/book_a_call` | New brief configurator when implemented; retain a useful consultation route meanwhile |
| `/career` and detail pages | `/careers/` and verified Job pages |
| `/talent/find_work/*` | Protected open-talent application when implemented |
| `/talent/discover_talent/*` | Employer team request; accounts only where necessary |
| `/brands/*`, `/our_clients`, `/what_clients_say` | Approved case-study/testimonial content |
| `/technologies/*`, `/industries/*` | Toolkit/sector pages with specific mappings |

This is a design and migration plan, not an executed data migration or a complete replacement declaration. The current public preview uses demonstration enquiries. Live WordPress, mail, scheduling and import acceptance remain separate implementation gates.
