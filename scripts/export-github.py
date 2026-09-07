"""Export only the new WordPress implementation and public documentation."""
from pathlib import Path
import argparse,json,shutil
root=Path(__file__).resolve().parents[1]
p=argparse.ArgumentParser();p.add_argument('destination',type=Path);args=p.parse_args();target=args.destination.resolve()
if target.exists() and any(target.iterdir()):raise SystemExit('Destination must be empty.')
target.mkdir(parents=True,exist_ok=True)
for directory in ['wordpress','scripts','tests','.github']:
 shutil.copytree(root/directory,target/directory,ignore=shutil.ignore_patterns('__pycache__','*.pyc'),dirs_exist_ok=True)
public_docs=['architecture.md','implementation-status.md','installation.md','verification.md','branding.md','design-research.md','wordpress-migration-blueprint.md','request-workflows.md']
(target/'docs').mkdir(exist_ok=True)
for name in public_docs:shutil.copy2(root/'docs'/name,target/'docs'/name)
shutil.copy2(root/'LICENSE',target/'LICENSE')
(target/'.gitignore').write_text('dist/\nrelease/\nnode_modules/\n__pycache__/\n*.pyc\n.env*\n.openai/\n.DS_Store\n')
(target/'README.md').write_text('''# SIT Consultancy — WordPress replacement

A UK-headquartered technology consultancy with engineering talent in Africa, including Liberia.

**Build better software. Put AI to work.** The design uses dominant apple-yellow and orange backgrounds, crimson actions, charcoal text, self-hosted Manrope typography and original glass bridge artwork.

## Packages

- **SIT Technology theme 0.4.3:** 32 public pages, responsive navigation and search, nine services, eight-sector explorer, AI project planner, technology toolkit, delivery assurance printable capabilities, engagement comparison, five editable delivery templates, detailed service journeys and an expanded interactive About page.
- **SIT Core 0.3.1:** four typed public request journeys with optional brief context and a local draft download, a private staff desk with type/status/owner filters, controlled transitions, versioned assignment updates, audit events, notification outbox, retention and privacy hooks.

Theme 0.4.3 adds an editorial About page, three-view delivery explorer, six FAQs and an explicit About-only upgrade action. It retains the mobile improvements and WordPress rendering repair. Core 0.3.1 is unchanged.

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
''')
print(json.dumps({'destination':str(target),'files':len([p for p in target.rglob('*') if p.is_file()])}))
