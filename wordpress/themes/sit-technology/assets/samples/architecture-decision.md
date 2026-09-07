# Architecture decision record

SIT Consultancy — editable sample

Illustrative template, not a client deliverable. Adapt it to your project.

## Record

Decision ID: ADR-001
Status: proposed example, not approved
Owner / date / reviewers: to be assigned

## Context

An illustrative service needs to notify staff when a request arrives. Requests must remain available if the email provider is temporarily unavailable.

## Options

A. Send email in the request handler: fewer components, but provider failure affects the request.
B. Save the request and queue a notification: more operational work, but delivery can be retried.

## Proposed decision

Evaluate a transactional outbox so the request and notification intent are stored together. Use a worker with bounded retries and a visible failure state.

## Consequences

Benefits: request handling can be independent of provider availability.
Costs: monitor the queue, handle duplicate delivery, define retention and make failed notifications visible.

## Verification and revisit triggers

Test rollback, retry, duplicate processing and provider outage. Review this choice if volume, hosting capabilities or delivery requirements change. Record approval before implementation.

