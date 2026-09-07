# Theme 0.5.0 / Core 0.4.0 — feature inventory

## Implemented

| Area | Features |
|---|---|
| Brand and UI | Original apple-yellow and amber glass bridge artwork; dominant yellow/orange backgrounds, crimson accents, silver details and charcoal text; responsive layouts; consistent spacing, typography and buttons; self-hosted Manrope variable font and included font license |
| Homepage | Clear software/AI proposition; warm split hero; three engagement choices; six connected capabilities; sample-document showcase; four delivery stages; AI planner, sector and insight links |
| Services | Strategy, software engineering, AI and automation, data and analytics, cloud and DevOps, dedicated teams, enterprise solutions, quality and security, managed services; combined keyword/category filtering with counts and reset |
| Service details | Nine individual pages with specific challenges, proposed outputs, prerequisites, section navigation, FAQs, relevant sample templates and typed enquiry links |
| Industries | Eight keyboard-operable sector panels with fragment links and related services; honest distinction between target sectors and proven experience |
| Delivery model | UK coordination, African engineering, four delivery stages, three engagement models, expandable FAQs |
| Engagement comparison | Three detailed models: advisory, projects and dedicated teams; fit, outputs, responsibilities and commercial approach; no promised prices or availability |
| Sample deliverables | Five original templates: discovery brief, architecture decision, roadmap, quality review and handover; readable pages, editable Markdown downloads and native print / save PDF |
| Contact | Compact enquiry form with topic and preferred region; links to project, discovery and team journeys; three regional cards; local clocks; FAQs; retry-safe submission and confirmation reference |
| Public office settings | Administrator-only address, address type, email, telephone, hours and visiting/accessibility fields; explicit public confirmation; escaped output; copy address; opt-in click-through directions for visiting offices; exact business addresses still awaiting owner confirmation |
| Team | 20 labelled proposed roles across leadership, product/delivery, engineering and people/operations; combined search/category filters, results count, empty state/reset, native responsibilities disclosures and relevant expertise links; no staff identities or vacancies asserted |
| Company | Expanded About story and branded artwork; page-section navigation; three-view keyboard-operable UK/Africa delivery explorer; four practical principles; six FAQ disclosures; company capabilities, sample documents and careers resources; honest no-vacancies state |
| Insights | Listing plus two original editorial articles about AI engineering and project briefs |
| AI project planner | Five required questions, three qualitative guidance paths, tailored next actions, no signup or answer storage |
| Technology toolkit | Four groups of languages, frameworks and platforms; capability confirmation and no partnership implication |
| Delivery assurance | Six practical working-standard topics; no certification claims |
| Company capabilities | Printable company overview and native print / save-as-PDF action |
| Request UI | Four request choices; conditional consultation, product and team fields; five optional context fields; three steps; direct review/edit controls; local plain-text brief download; type/service/goal deep links; required fields; review; consent; retry; success reference; reset; no irrelevant-field submission or edits during sending |
| Navigation | Grouped Expertise and Company menus, local site-search dialog, mobile disclosure menu, Escape-to-close, current-page indicator, skip link, footer links, real 404 page |
| Accessibility foundations | Semantic landmarks, one H1 per page, keyboard controls, labels, focus styles, live status messaging and reduced-motion support |
| WordPress theme | Installable theme; editor styles; theme.json; editable starter Pages; custom primary menu; title/description metadata; guarded setup, missing Contact/Team page creation, About-only adoption and opt-in refresh of SIT starter pages; five allowlisted interactive component shortcodes; allowlisted packaged-page shortcode; scoped formatting repair for existing HTML starters; shared assets |
| Private intake | Strict scalar/length/enum validation, origin check, signed expiring token, honeypot, atomic rate counter, unique idempotency hash and no public enquiry retrieval |
| Upgrade diagnostics | Separate app/schema versions; administrator-only version and readiness panel; no patch-level database update for a current installation |
| Private request desk | Apple-gold admin header; actual status counts; type/status/owner filters; reference and organisation search; pagination; request details; eligible staff assignment; server-controlled transitions; row version comparison; transactional lifecycle events |
| Notification outbox | Transactional enqueue, scheduled worker, atomic claims, retry/backoff, failed state, mail transport acceptance state |
| Upgrade and privacy | Additive, protected schema upgrade preserving old records; intake/worker pause until current schema; type-aware export; explicit activation settings, no automatic marketing opt-in, retention cleanup, WordPress privacy exporter and eraser, data-preserving uninstall |
| Engineering handoff | Strategy, legacy audit, architecture, installation guide, release status, boundary tests, DOM interaction tests, preview validator, package script and GitHub Actions validation workflow |

The private preview does not send enquiries. The WordPress endpoint is implemented but disabled until configured. The theme remains usable without the Core plugin.

The public source repository is `sitnoah/sit-techno`; confidential legacy audit evidence is retained separately. The reviewed legacy applications have a broader operational scope than this first WordPress build.

## Not yet implemented

| Next capability | What remains |
|---|---|
| Complete legacy workflow replacement | See the migration blueprint: booking confirmation, quotes, recruitment, protected CVs, request-scoped discussions, content modules and data migration remain. Request intake for consultations, software briefs and teams is implemented. |
| Production WordPress deployment | Select a host; install; run the staging acceptance checks; configure domain, TLS, backups, scheduling and mail. |
| Full Site Editing | Current theme is a classic theme with editor styling; a custom block library and FSE templates are a later enhancement. |
| Live scheduling | Add an approved calendar provider and real availability. No decorative booking button is presented. |
| Bigin CRM | OAuth/server credentials, mapping, durable sync queue, idempotency, retry and reconciliation. |
| Portfolio evidence | Approved client case studies, outcome evidence, logos, references and permissions. Sample templates are clearly labelled and do not stand in for client evidence. |
| Talent recruitment | Job publishing, candidate consent, protected CV intake, screening and hiring workflow. |
| Secure RFP uploads | Private storage, scanning, download controls and retention. |
| Client/partner portal | Verified authentication, scoped records, project status, documents and audit trail. |
| Tender operations | Opportunity qualification, bid/no-bid decisions, evidence library, proposal production and deadlines. |
| Structured editorial CMS and expanded search | Custom service/industry/case-study types, taxonomy filters and scalable full-content search. Current catalogue search covers public starter content, or up to 200 published non-password-protected WordPress Pages and Posts. |
| Newsletter and campaigns | Verified opt-in, unsubscribe, email provider, consent records and separate marketing preferences. |
| Analytics and consent | Agreed KPIs, tracking configuration and reviewed cookie controls. |
| Dutch localisation | Approved translated copy, URL/language structure and editorial workflow. |
| Production assurance | Browser/device/assistive technology review, real WordPress/MySQL integration tests, load/security testing and operational monitoring. |

## Recommended next sequence

1. Review the design and copy using the private preview. Confirm the name, brand direction, service scope and UK/Africa delivery description.
2. Install both packages on an isolated WordPress staging host.
3. Replace the working privacy notice and supply verified business contact details, leadership bios and real evidence of delivery capability.
4. Verify the complete enquiry → staff inbox → notification → retention journey, then configure the production domain and publish.
5. Add Bigin, approved case studies and conversion measurement. Build recruitment, bid operations and portals when their processes and owners are clear.


## Mobile release — theme 0.4.2

- Tablet hero and engagement sections stack earlier; mobile capability lists, delivery stages and industry choices use one column.
- Larger labels, 44–52 px minimum primary control targets, full-width phone actions, and more readable service and enquiry content.
- Scroll-contained mobile navigation with a Close label, background isolation, keyboard focus containment and search focus return.
- Viewport-bounded search, safe-area padding, narrower-screen spacing and cache-versioned shared preview assets.
- All 32 public pages use the same mobile stylesheet as WordPress. Core stays at 0.3.1 with no database or settings changes.
- Local source/interaction checks passed; device and production WordPress checks remain to be performed.
