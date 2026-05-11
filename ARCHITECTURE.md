# TadreebLMS Architecture

This document is a practical map of the codebase for new contributors.

## Overview

TadreebLMS is a Laravel-based learning management system. It supports:

- Public marketing and course discovery pages
- User authentication and profile management
- Course enrollment, lessons, assessments, and certificates
- Admin operations for users, content, settings, and reporting
- Integrations for payments, email, LDAP, social login, and S3/storage
- Background processing for notifications, KPIs, license checks, and data fixes

The project is intentionally broad in scope. Most product logic lives in the Laravel app itself rather than in separate services.

## High-Level Stack

- Backend: Laravel / PHP 8.2+
- Frontend build: Laravel Mix / Webpack
- UI libraries: Bootstrap 4, Vue 2, jQuery
- Auth: session auth, password expiry, optional impersonation, social login
- API: `api/v1` routes with JWT protection for most endpoints
- Storage and delivery: local files, S3 sync, email providers, media tools

See:

- [composer.json](/Users/JuanSanchez/PhpstormProjects/tadreeblms/composer.json)
- [package.json](/Users/JuanSanchez/PhpstormProjects/tadreeblms/package.json)
- [webpack.mix.js](/Users/JuanSanchez/PhpstormProjects/tadreeblms/webpack.mix.js)

## Request Flow

The normal HTTP flow is:

1. `public/index.php` bootstraps Laravel.
2. `app/Http/Kernel.php` applies global and route middleware.
3. `app/Providers/RouteServiceProvider.php` loads web, API, and module routes.
4. Controllers resolve models, services, and helpers.
5. Views render from `resources/views`.

Important middleware and request guards include:

- installation checks
- maintenance mode
- CSRF protection
- locale selection
- password expiry
- subscription checks
- role and permission checks
- JWT verification for API routes

See:

- [app/Http/Kernel.php](/Users/JuanSanchez/PhpstormProjects/tadreeblms/app/Http/Kernel.php)
- [app/Providers/RouteServiceProvider.php](/Users/JuanSanchez/PhpstormProjects/tadreeblms/app/Providers/RouteServiceProvider.php)

## Route Structure

Routes are split by concern instead of keeping everything in a single file.

### Web Routes

`routes/web.php` is the main entry file. It contains:

- top-level utility routes
- language switching
- sitemap and certificate verification
- frontend route loading
- backend route loading
- some direct debugging or maintenance endpoints

The file also includes other route files via `include_route_files(...)`, which recursively loads PHP route files from the given folder.

See:

- [routes/web.php](/Users/JuanSanchez/PhpstormProjects/tadreeblms/routes/web.php)
- [app/helpers.php](/Users/JuanSanchez/PhpstormProjects/tadreeblms/app/helpers.php)

### Frontend Routes

Frontend routes are organized under `routes/frontend/`.

- `routes/frontend/auth.php` handles login, logout, registration, password reset, teacher registration, and account confirmation.
- `routes/frontend/home.php` handles authenticated user dashboard/account/profile routes plus a few utility endpoints.

See:

- [routes/frontend/auth.php](/Users/JuanSanchez/PhpstormProjects/tadreeblms/routes/frontend/auth.php)
- [routes/frontend/home.php](/Users/JuanSanchez/PhpstormProjects/tadreeblms/routes/frontend/home.php)

### Backend Routes

Backend routes are organized under `routes/backend/`.

- `routes/backend/admin.php` contains most admin and staff functionality.
- `routes/backend/auth.php` contains admin user and role management.

The admin side covers:

- dashboard and operational tools
- roles and permissions
- KPI configuration and exports
- teachers and employees
- assessments and assignments
- orders and subscriptions
- settings and integrations
- notifications, licenses, SMTP, LDAP, S3, and external apps

See:

- [routes/backend/admin.php](/Users/JuanSanchez/PhpstormProjects/tadreeblms/routes/backend/admin.php)
- [routes/backend/auth.php](/Users/JuanSanchez/PhpstormProjects/tadreeblms/routes/backend/auth.php)

### API Routes

`routes/api.php` exposes a versioned API under `/api/v1`.

The API includes:

- auth endpoints
- catalog endpoints for courses, bundles, teachers, testimonials, FAQs, sponsors, blogs
- learning endpoints for lessons, progress, quizzes, certificates
- commerce endpoints for cart, checkout, subscriptions, coupons, purchases
- community endpoints for messages and forums

Most of the API is protected by `jwt.verify`.

See:

- [routes/api.php](/Users/JuanSanchez/PhpstormProjects/tadreeblms/routes/api.php)

## Application Layers

### Controllers

Controllers are grouped by domain:

- `app/Http/Controllers/Frontend`
- `app/Http/Controllers/Backend`
- `app/Http/Controllers/Backend/Admin`
- `app/Http/Controllers/v1`

The codebase still uses a mix of:

- class-based controller references
- string controller action references
- legacy route naming conventions

That is normal in this repo.

### Models

Models are in `app/Models` and cover the main LMS entities:

- users, roles, permissions
- courses, lessons, bundles, categories
- assessments, questions, answers, test results
- orders, invoices, subscriptions, coupons
- feedback, reviews, blogs, pages, announcements
- KPIs, snapshots, targets, roles, templates
- LDAP, external apps, licenses, locale, settings

### Services

Business logic that is too large for controllers is pushed into services.

Notable service areas:

- KPI processing and snapshots
- notification settings
- language marketplace
- licensing
- LMS event recording
- Zoom and external integrations

Look in:

- `app/Services`
- `app/Helpers`
- `app/Repositories`

### Jobs and Events

The app uses queued jobs and domain events for background work and side effects.

Examples include:

- email dispatch
- KPI processing
- file sync between local storage and S3
- subscribe-course chunk processing
- pathway assignment emails

Relevant folders:

- `app/Jobs`
- `app/Events`
- `app/Listeners`
- `app/Notifications`

## Authentication and Authorization

Auth is layered:

- Frontend auth routes live in `routes/frontend/auth.php`.
- Backend auth routes live in `routes/backend/auth.php`.
- Role and permission enforcement uses Spatie permission middleware.
- Password expiry is enforced by middleware and config.
- Impersonation/login-as is supported behind config flags.

Important config:

- `config/access.php`
- `config/permission.php`

Important middleware:

- `auth`
- `guest`
- `role`
- `permission`
- `password_expires`
- `subscribed`
- `jwt.verify`

## Configuration Model

The app uses both environment values and database-backed settings.

Examples of config-driven behavior:

- registration and impersonation flags
- password expiry policy
- payment provider credentials
- social login providers
- locale/language availability
- KPI settings
- LDAP settings
- theme layout selection

`AppServiceProvider` loads a lot of this into the runtime view layer and global config. New contributors should expect some behavior to be data-driven rather than hard-coded.

Key config files:

- [config/access.php](/Users/JuanSanchez/PhpstormProjects/tadreeblms/config/access.php)
- [config/services.php](/Users/JuanSanchez/PhpstormProjects/tadreeblms/config/services.php)
- [config/locale.php](/Users/JuanSanchez/PhpstormProjects/tadreeblms/config/locale.php)
- [config/kpi.php](/Users/JuanSanchez/PhpstormProjects/tadreeblms/config/kpi.php)
- [config/theme_layouts.php](/Users/JuanSanchez/PhpstormProjects/tadreeblms/config/theme_layouts.php)

## View and UI Structure

Views are split by surface area:

- `resources/views/frontend`
- `resources/views/frontend-rtl`
- `resources/views/backend`
- `resources/views/admin`
- `resources/views/installer`
- `resources/views/language-marketplace`

This repo also ships some third-party assets directly under `public/assets/libs` and `resources/views/backend/assetslib`.

## Scheduling and Maintenance

`app/Console/Kernel.php` defines the recurring operational tasks.

Current recurring work includes:

- unpublishing expired courses
- completing live courses
- dispatching subscribe-course jobs
- sending assignment reminders
- sending course notifications
- license checks
- incremental KPI processing
- dashboard cache warmup

The app also contains many one-off maintenance commands for historical data fixes, migrations, and sync tasks.

See:

- [app/Console/Kernel.php](/Users/JuanSanchez/PhpstormProjects/tadreeblms/app/Console/Kernel.php)
- `app/Console/Commands`

## External Integrations

The project supports several external systems:

- Stripe and Cashier
- Social login providers
- LDAP
- S3 file storage
- SendGrid and mail transports
- Zoom
- GeoIP
- Laravel Filemanager
- PDF and Excel exports
- DataTables

See:

- [config/services.php](/Users/JuanSanchez/PhpstormProjects/tadreeblms/config/services.php)
- [config/ldap.php](/Users/JuanSanchez/PhpstormProjects/tadreeblms/config/ldap.php)

## Theme and Frontend Build

Frontend assets are compiled with Laravel Mix:

- `resources/sass/frontend/app.scss` -> `public/css/frontend.css`
- `resources/sass/frontend-rtl/app.scss` -> `public/css/frontend-rtl.css`
- `resources/sass/backend/app.scss` -> `public/css/backend.css`
- `resources/js/frontend/app.js` -> `public/js/frontend.js`
- `resources/js/backend/*.js` -> `public/js/backend.js`

The build is old-school but stable. If you are adding UI work, check whether the target is frontend, frontend-RTL, or backend before editing styles.

See:

- [webpack.mix.js](/Users/JuanSanchez/PhpstormProjects/tadreeblms/webpack.mix.js)
- `resources/sass`
- `resources/js`

## Practical Onboarding Tips

If you are new to the repo, start here:

1. `routes/web.php` to understand the top-level app flow.
2. `routes/frontend/*` for learner-facing behavior.
3. `routes/backend/*` for admin/staff functionality.
4. `app/Http/Controllers/Backend/Admin` for the main admin feature set.
5. `app/Services` for business logic that should not be in controllers.
6. `app/Console/Kernel.php` for background tasks and recurring jobs.

If you are changing a feature, also check:

- the related model in `app/Models`
- the request validation in `app/Http/Requests`
- any queued job or event listener used by the flow
- the matching Blade views in `resources/views`

## Notes For Contributors

- There is a lot of legacy code and duplicate route style mixed with newer class-based syntax.
- Some routes are utility-only or admin maintenance endpoints and should be handled carefully.
- The app relies on database-driven configuration, so a change may need a code update and a config row update.
- If you touch auth, subscription, or licensing, verify middleware and config together.
- If you touch KPIs, also check the scheduled commands and snapshot versioning.

