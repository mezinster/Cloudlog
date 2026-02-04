# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Cloudlog is a self-hosted PHP web application for amateur radio contact (QSO) logging, built on **CodeIgniter 3** with Bootstrap 5, HTMX, and jQuery. Current version: 2.8.7.

**Stack:** PHP 7.4+ (8.2 compatible), MySQL 5.7+, Apache/Nginx, CodeIgniter 3

## Development Commands

```bash
# Start Docker dev environment (web on port 80, MySQL)
cp .env.sample .env        # first time only
docker-compose up

# Run Cypress E2E tests (requires Docker containers running)
docker-compose up -d
npm install cypress         # first time only
npx cypress run

# Run a single Cypress spec
npx cypress run --spec cypress/e2e/1-login.cy.js

# Database migrations (via CLI or admin UI)
php index.php migrate
```

Cypress base URL: `http://localhost/` with 60-second timeouts. Tests cover login flows, station creation, logbook operations, and version checks.

## Architecture

### MVC Pattern (CodeIgniter 3)

- **Controllers** (`application/controllers/`): Extend `CI_Controller`. ~70 controllers. Authentication checked via `$this->user_model->validate_session()`. Authorization levels: `authorize(2)` for users, `authorize(99)` for admins.
- **Models** (`application/models/`): Extend `CI_Model`. Use CI Query Builder for DB access.
- **Views** (`application/views/`): PHP templates. Always wrap with `interface_assets/header` and `interface_assets/footer`. Use Bootstrap 5 classes.
- **Libraries** (`application/libraries/`): `Qra` (gridsquare/bearing/distance), `OptionsLib` (settings), `Frequency`, `AdifHelper`, `DxccFlag`, etc.
- **PSR-4 classes** (`src/`): `Dxcc/`, `Label/`, `QSLManager/`

### Routing

Standard CI3 URL routing: `/controller/method/params`. Default controller: `dashboard`. Custom routes rarely needed — new controllers are auto-routable. Use `site_url()` and `base_url()` helpers for URL generation.

### Frontend

- **HTMX** is the preferred AJAX method. Use `hx-get`, `hx-post`, `hx-target`, `hx-trigger` attributes.
- **jQuery** for additional frontend functionality.
- **Assets** in `assets/` — CSS, JS, fonts. Core includes wired via `interface_assets/header.php` and `footer.php`.
- **JS globals** defined in `footer.php`: `base_url`, `site_url`, `my_call`.
- Key JS libs: Leaflet.js (maps), DataTables, Chart.js, Quill (rich text), Fancybox.

### Database

- **Main QSO table**: `TABLE_HRD_CONTACTS_V01` (name configurable in `config.php`)
- **Migrations**: Sequential numbered files in `application/migrations/` (currently 244). Each extends `CI_Migration` with `up()` method. Auto-run by `OptionsLib` on page load.
- **Key tables**: `station_profile`, `station_logbooks`, `station_logbooks_entity`, `users`, `options`, `user_options`

### Configuration

- Copy `config.sample.php` → `config.php` and `database.sample.php` → `database.php`
- Never commit `config.php` or `database.php`
- Docker DB host must be `db` (service name), not `localhost`
- Environment set in `index.php`: `define('ENVIRONMENT', 'development')` enables profiler

## Code Conventions

- **Indentation**: Tabs, 4 spaces width (see `.editorconfig`)
- **Line endings**: LF, UTF-8
- **PHP/JS files**: Final newline, trim trailing whitespace
- **Auth pattern**: Load `user_model`, call `validate_session()` at controller start, `authorize(N)` for role checks
- **Flash messages**: `$this->session->set_flashdata('notice', 'Message')` then redirect
- **Direct access guard**: `if (!defined('BASEPATH')) exit('No direct script access allowed');`
- **Input sanitization**: `$this->security->xss_clean($input)`, use Query Builder for SQL
- **Language/i18n**: `<?php echo lang('key'); ?>` with files in `application/language/`

## Adding New Features

1. **Controller**: `application/controllers/MyFeature.php` extending `CI_Controller`. Enforce auth in `__construct()`.
2. **Model**: `application/models/Myfeature_model.php`. Use CI Query Builder. Load via `$this->load->model('myfeature_model')`.
3. **View**: `application/views/myfeature/*.php`. Include header/footer. Prefer HTMX for async operations.
4. **Migration**: Add `application/migrations/NNN_description.php` with sequential number.

## PR Guidelines

- **Target branch**: `dev` only (PRs to master will be rejected)
- **One feature per PR** — no bundled changes
- **Run Cypress tests** before submitting

## Domain Glossary

- **QSO**: A radio contact/log entry
- **Gridsquare/Locator**: Maidenhead grid system for location (e.g., `IO87JP`)
- **DXCC**: Country entities for award tracking
- **ADIF**: Amateur Data Interchange Format for QSO import/export
- **LoTW**: Logbook of The World (ARRL electronic QSL confirmation)
- **eQSL**: Electronic QSL card system
- **POTA/SOTA/WWFF**: Parks/Summits/Flora & Fauna on the Air programs
- **Cabrillo**: Contest log submission format
