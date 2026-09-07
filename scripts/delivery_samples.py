"""Original, editable templates. No client work or outcome claims."""
SAMPLES = [
 ('discovery-brief','Discovery brief','Agree the problem before choosing the solution.',[
  ('Decision to make','What decision should this discovery enable?\nExample: decide whether a self-service request portal is worth piloting.'),
  ('People and the current journey','Name the sponsor, delivery owner and user groups. Describe the current workflow and the friction to investigate.\nExample: staff enter the same request in several systems; validate this through interviews and observation.'),
  ('Evidence and success','Record the current baseline, how it was measured, and the desired change. Leave targets uncommitted until the baseline is understood.\nEvidence to collect: request volumes, handling steps, user feedback and existing system constraints.'),
  ('Scope and boundaries','In scope: journey research, options, feasibility and a prioritised pilot backlog.\nOut of scope until agreed: production build, live data migration and integrations.'),
  ('Outputs and acceptance','A problem statement, options with trade-offs, risk register, outline architecture and recommended next step.\nAcceptance: the sponsor reviews the evidence, records decisions and agrees whether to proceed.'),
  ('Inputs and open questions','Identify stakeholders, approved system access, budget boundaries and decision dates. Track each unanswered question with an owner and review date.')]),
 ('architecture-decision','Architecture decision record','Make technical choices understandable and reviewable.',[
  ('Record','Decision ID: ADR-001\nStatus: proposed example, not approved\nOwner / date / reviewers: to be assigned'),
  ('Context','An illustrative service needs to notify staff when a request arrives. Requests must remain available if the email provider is temporarily unavailable.'),
  ('Options','A. Send email in the request handler: fewer components, but provider failure affects the request.\nB. Save the request and queue a notification: more operational work, but delivery can be retried.'),
  ('Proposed decision','Evaluate a transactional outbox so the request and notification intent are stored together. Use a worker with bounded retries and a visible failure state.'),
  ('Consequences','Benefits: request handling can be independent of provider availability.\nCosts: monitor the queue, handle duplicate delivery, define retention and make failed notifications visible.'),
  ('Verification and revisit triggers','Test rollback, retry, duplicate processing and provider outage. Review this choice if volume, hosting capabilities or delivery requirements change. Record approval before implementation.')]),
 ('delivery-roadmap','Delivery roadmap','Connect delivery stages to decisions and acceptance.',[
  ('Outcome and owners','Business outcome: to be agreed. Sponsor, product owner, delivery lead and reviewers: to be assigned. This template intentionally makes no schedule or price promise.'),
  ('Stage 1 — Understand','Activities: user research, system review, baseline and constraints.\nOutput: agreed discovery brief.\nExit decision: is the proposed outcome valuable and feasible enough to investigate?'),
  ('Stage 2 — Define','Activities: prototype, architecture options, risk review and backlog.\nOutput: reviewed delivery plan and acceptance criteria.\nExit decision: agree scope, commercial model and responsibilities.'),
  ('Stage 3 — Build and review','Activities: work in small increments, test, demonstrate and capture decisions.\nOutput: reviewable releases and an updated risk/dependency log.\nExit decision: stakeholders accept the agreed release scope.'),
  ('Stage 4 — Release and transfer','Activities: rehearse migration, verify rollback, train operators and transfer documentation.\nOutput: release record, runbook and ownership checklist.\nExit decision: named owners approve launch and support arrangements.'),
  ('Track changes','For each change record the reason, impact on scope/cost/date, approver and decision. Keep unresolved dependencies visible; revise estimates when evidence changes.')]),
 ('quality-review','Quality review checklist','Bring review evidence into release decisions.',[
  ('Review context','Release, environment, scope, reviewer and date: to be completed. For each check record Pass / Fail / Not applicable, a reason and a link to evidence. This is a planning aid, not a certification.'),
  ('Behaviour and resilience','Check agreed acceptance criteria, input validation, permission boundaries, retries, duplicate requests, failure messages, backups and rollback. Confirm that incomplete work cannot be mistaken for success.'),
  ('Security and data','Review dependency findings, approved data flows, secrets handling, least privilege, logging and retention. Track unresolved findings with an owner and explicit release decision.'),
  ('Usability and accessibility','Check keyboard operation, focus visibility, readable labels, error recovery, zoom and narrow screens. Include manual checks with assistive technology relevant to the service.'),
  ('Performance and operations','Measure representative user journeys, document test conditions, verify monitoring and alerts, and agree support ownership. Set budgets appropriate to the project before assessing them.'),
  ('Release recommendation','Summarise evidence, unresolved risks and acceptance conditions. Name the person accountable for the go/no-go decision and record the decision date.')]),
 ('handover-checklist','Handover checklist','Make ownership part of delivery.',[
  ('Ownership','Name the business owner, technical owner, support contact and escalation route. Confirm who can approve releases, access changes and supplier costs.'),
  ('Code and environments','Transfer repository access, build instructions, environment inventory and deployment documentation. Verify the receiving team can build and deploy. Share credentials only through an approved secret-management process.'),
  ('Runbook','Document routine checks, monitoring, alerts, backup restoration, incident triage and rollback. Rehearse critical recovery steps and record results.'),
  ('Data and suppliers','Record approved data flows, storage, retention, integrations and supplier dependencies. Confirm account ownership, licences, renewal responsibilities and access removal.'),
  ('Knowledge transfer','Run a walkthrough of architecture and key workflows. Capture known limitations, unresolved risks and prioritised improvements. Record questions and owners.'),
  ('Acceptance','List deliverables received, evidence reviewed and exceptions agreed. Record the handover decision, named owners and the start/end of any agreed support period.')]),
]
