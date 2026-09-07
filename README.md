# SIT Consultancy — WordPress replacement

A UK-headquartered technology consultancy with engineering talent in Africa, including Liberia.

**Build better software. Put AI to work.** The design uses dominant apple-yellow and orange backgrounds, crimson actions, charcoal text, self-hosted Manrope typography and original glass bridge artwork.

## Packages

- **SIT Technology theme 0.4.0:** 32 public pages, responsive navigation and search, nine services, eight-sector explorer, AI project planner, technology toolkit, delivery assurance printable capabilities, engagement comparison, five editable delivery templates and detailed service journeys.
- **SIT Core 0.3.0:** four typed public request journeys with optional brief context and a local draft download, a private staff desk with type/status/owner filters, controlled transitions, versioned assignment updates, audit events, notification outbox, retention and privacy hooks.

The static preview form is a demonstration. Live WordPress enquiries are disabled until configured. The theme works without Core. This build does not yet replace all scheduling, recruitment or administrative workflows; see the migration plan.

## Local preview and packages

Python 3 builds the static preview; Node is needed only for JavaScript checks. PHP and WordPress are required for the live installation.

```bash
python3 scripts/build-preview.py
python3 scripts/validate-preview.py
python3 scripts/package-wordpress.py /absolute/output/path
```

Upload the theme ZIP through **Appearance → Themes**, and the separate Core ZIP through **Plugins**. Do not upload the source archive as a theme. Read the staging checklist before activation on a live website.

To edit the source content, update `scripts/author.py`, `scripts/redesign.py`, `scripts/consultancy.py`, `scripts/delivery_samples.py` or `scripts/request_form.py`, run `python3 scripts/author.py`, then rebuild the preview. WordPress editorial changes do not automatically sync back to source.

## Documentation

- [Architecture](docs/architecture.md)
- [Feature inventory](docs/implementation-status.md)
- [WordPress migration blueprint](docs/wordpress-migration-blueprint.md)
- [Request workflows](docs/request-workflows.md)
- [Installation and staging](docs/installation.md)
- [Verification record](docs/verification.md)
- [Brand palette](docs/branding.md)
- [Consulting-site design research](docs/design-research.md)

The implementation has local syntax, structural and interaction checks. Full WordPress/MySQL, browser/device and live delivery verification remain staging gates. No real client data, private legacy code, credentials or private audit findings are included in this repository.

Theme and plugin code are GPL-2.0-or-later. The Manrope font license is included with its asset. No vendor partnership, certification, client result or testimonial is implied by the design.
