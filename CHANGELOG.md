# HydrogenModules — Changelog

All notable changes to this project are documented in this file.

---

## [1.1.1] — 2026-11-13

**Period:** November 2026  
**Commits since 1.1.0:** 2

### Bugfixes

- **Resource:Database:Undo** — Refactored the `note()` method: uses `TableReader` instead of direct PDO query for more reliable data reading; renamed parameter `Model $tableWriter` to `Model $model` for correct semantics
- **Manage:Forms** — Renamed AJAX controller `Controller_Manage_Form_Fill_Receive` to `Controller_Ajax_Manage_Form_Fill_Receive`; all `respondError()` calls now use typed error codes (`'form'`, `'altcha'`, `'captcha'`, `'ip'`, `'spam'`, etc.) instead of `0`
- **Security:IP:Check** — `logRequestOfRejectedIp()` now accepts `Entity_IP_Check_Result` as a parameter instead of reading the IP from the request; controller uses entity property `$result->clean` instead of array access `$result['clean']`

---

## [1.0.2] — 2026-11-13

**Period:** October 2025 – November 2026  
**Commits since 1.0.1:** 534

### New Modules

- **Security:IP:Check** (0.9.1) — IP checking with data entities for query, source, and result
- **Security:Altcha** (0.2.1) — ALTCHA CAPTCHA integration with JS module for client-side verification
- **Security:Limiter:Request** (0.2.1) — Request rate limiting based on Server:Log:Request
- **Server:Measurement:InfluxDB** — InfluxDB metrics with SSL certificate validation, default tags, and exception hook
- **Server:Log:Request** (0.2.8) — Request logging with session ID, POST/PUT body, log file mode, and microtime comparison
- **Resource:SMB** — SMB file access via icewind/smb
- **Work:Notifications** (0.3.0) — Notification module, feature-complete to the first concept
- **Work:Meetings** (0.2.7) — Meeting management with modal view, participant management, and job scheduler

### Manage:Forms — Major Update (0.6.6 → 0.7.2)

- API import with request limits via Server:Log:Request
- SPAM check hook
- AJAX controller for form fill filters
- ALTCHA support and redirect on submit
- Fill token for confirmation
- Form fill receiver fully migrated to AJAX
- Rejected requests are logged

### Newsletter — Major Overhaul

#### Resource:Newsletter (0.5.3 → 0.6.2)

- Link click tracking for reader letters
- Tester flag in reader model
- Mail template wrapping newsletter template
- Reader letters can be sent in the future
- Theme/template snapshot with mail template ID
- Complete entity implementation
- English language file

#### Work:Newsletter (1.4.4 → 1.5.6)

- Import strategies with deduplication
- Link views and responsive design
- Queue-based sending for future reader letters
- Improved template snapshot (theme)
- Group relations support
- Module assets loaded on demand

#### Info:Newsletter (0.7.3 → 0.7.6)

- Improved preview of latest newsletter
- Link click tracking
- Queue job without limit

### Resource:Mail — Refactoring (1.1.3 → 1.1.7)

- Mail priority on mail object and queue
- HTML mail rendering with module-based placeholders
- Static constructor on abstract mail class
- Mail language support
- Timestamp column for sent mails
- `Mail::onSent` hook after transport
- Improved queue pagination performance

#### Admin:Mail (1.0.4 → 1.0.5)

- More bulk actions in queue list
- Autocomplete email filter in queue
- Improved columns and page handling for bulk actions

### Security & CSRF

- **Security:CSRF** (0.3.5) — Refresh and hints in view helper, main switch
- **Manage:My:User** (0.6.3) — CSRF protection enabled
- **UI:LanguageSelector** (0.2.7) — Main switch

### User Management

#### Resource:Users (0.4.6 → 0.4.9)

- Logic classes for group relations and roles
- Language column in user model
- Password status "update"
- Methods: `countRoleUsers`, `getRoles`, `getRoleUsers`, `getUser`, `getGroupUsers`

#### Manage:Users (0.8.2 → 0.8.3)

- Role rights indicator on index
- Helper for module entity group relations
- Email filter

#### Manage:My:User

- Password management in separate tab
- User language changeable
- Empty email allowed

#### Resource:Authentication

- User logic extensions in all backends (Local, JSON, OAuth, OAuth2, REST)
- Password status "update"

### Admin:Log:Exception (0.6.6 → 0.7.0)

- Rights, index, and view implemented
- Public statistics (as JSON)
- ID filter
- View refactored with view helper and style
- IO and Logic exception extra data

### Shop & Catalog

- **Catalog:Bookstore** — Entities and measurements
- **Shop:Payment:PayPal** — Strategies, store payment request/response, billing address instead of user
- **Shop:Payment:Stripe** — Giropay and Sofort disabled
- Order entity, shipping view helper
- Cart positions strategies and quantity buttons extracted
- Abstract shop mail class
- Payment backend refactoring

### Resource:Jobs (0.9.0)

- Job discovery via config: by request (always) or by job call (job)
- Result entity
- Scheduler: job definition migrated from XML to module config
- Strict entity usage

### Entity Migration (widespread)

- **Catalog:\***, **Manage:Catalog:\*** — Entity migration
- **Resource:Downloads** — Model entities
- **Resource:Payment** — Payment entity
- **Resource:Sitemap** — Sitemap links entity
- **UI:Helper:Relations** — Entities for module relation
- **UI:Navigation** — Menu item and pages entities
- **Server:IP:Lock** — Entity classes with unlock and cleanup jobs
- **Resource:Address** (0.3.0) — Logic class with first methods

### Info:News / Manage:News

- New status: **outdated**

### Manage:Relocation

- CSV export

### UI & Frontend

- **UI:Bootstrap** — v4.6.2 and v5.3.3 added
- **JS:TinyMCE** — Version 8.2.2 supported
- **UI:Helper:Forms** — ALTCHA support
- **TimePhraser** — Now supports cases (dative, etc.) in addition to nominative

### Work:Mail

- **Work:Mail:Check** (0.3.7) — Database tables and primary keys renamed
- **Work:Mail:Sync** — Hook extracted

### Work:FTP (0.2.5)

- AJAX controller extracted

### Localization (widespread)

- Admin:\*, Catalog:\*, Info:\*, Manage:\*, Resource:\*, Server:\*, UI:\*, Work:\* — Localization completed

### Infrastructure & Code Quality

- Hooks changed from `App:response` to `Page::render`
- Module XML updated to new schema `module-1.0.0.xsd`
- PHPStan upgraded from 1 to 2
- Auth session keys replaced by constants
- Singleton fixes
- Deprecated modules removed
- Code style and strictness improved

---

## [1.0.1] — 2025-10-10

**Period:** February 2025 – October 2025  
**Commits:** 194

### New Modules

- **Resource:SMB** — SMB file access via icewind/smb
- **Server:Log:Request** — Request logging with session ID, POST/PUT body, log file mode
- **Server:Measurement:InfluxDB** — InfluxDB metrics with SSL certificate validation, default tags, and exception hook

### Entity Migration

- **Server:IP:Lock** — Entity classes with unlock and cleanup jobs
- **UI:Navigation** — Menu item and pages entities
- **UI:Helper:Relations** — Entities for module relation
- **Resource:Downloads** — Model entities
- **Resource:Sitemap** — Sitemap links entity
- **Resource:Payment** — Payment entity
- **Shop:Payment** — Entity for payment backend
- **Shop:Bridge** — Entities
- **Catalog:Bookstore** — Entities and measurements
- **Shop** — Order entity, order positions entity
- **Resource:Jobs** — Result entity

### Shop & Payment

- Shop modules refactored
- Payment backend handling refactored
- PayPal: strategies, store payment request/response, billing address instead of user
- Stripe: Giropay and Sofort disabled
- Abstract shop mail class
- Cart positions strategies and quantity buttons extracted
- Shipping view helper
- Payment backend register

### Resource:Jobs

- Scheduler: job definition migrated from XML to module config
- Job discovery via config (by request / by job call)
- Strict entity usage

### Work:Meetings

- Initial prototype through 0.2.4
- Job scheduler instead of crond

### Work:Notifications

- Initial commit 0.2.0 through 0.3.0 (feature-complete)

### Resource:Mail

- Improved mail queue pagination performance

### UI

- **TimePhraser** — Now supports cases (dative, etc.) in addition to nominative

### Code Quality

- Auth session keys replaced by constants
- Hooks changed from `App:response` to `Page::render`
- Code style and strictness improved
- Numerous hook extractions (Manage:Shop, Manage:Catalog, Manage:Customer, Work:Mail:Sync, etc.)

---

## [1.0.0] — 2025-02-11

**Period:** May 2023 – February 2025  
**Commits:** 355

### PHP 8.1 Migration

- Complete migration of all modules to PHP 8.1 with strict types
- PHP 8.1 minimum required (support for older versions removed)
- PHPStan Level 0 achieved, work on Level 1 begun

### New Modules

- **Resource:Mail:Sent** — Saves sent mails to IMAP server
- **Resource:Downloads** — Extracted from Info:Downloads and Info:Files

### Entity Migration (widespread)

- First model entity classes in Resource:Users and Work:Missions
- **Resource:Mail** — Entity for mail, attachment, and template models
- **Security:CSRF** — Model entity
- **UI:Image:Slider** — Model entity classes
- **Resource:Pages** — Page entity
- **Resource:Shares** — Model entity
- **Resource:FileBucket** — Model entity
- **Resource:Database:Lock** — Model entity
- **Resource:Forms** — Entities for all models
- **Resource:Import** — Model entity classes
- **Resource:Jobs** — Model entity classes
- **Manage:Projects** — Model entity classes
- **Info:Manual** — Model entities

### Shop & Payment

- Payment registration refactored
- Payment method costs supported
- Unified payment fee calculation with logic class

### Resource:Import

- Import logic extracted
- Connector model extended with type and MIME types

### UI

- **UI:Helper:StatusBadge** — Bootstrap badge for entity status information
- **UI:Bootstrap:Switch** — Hook extracted
- **UI:Bootstrap:Datepicker** — Hook extracted
- **UI:ContextHelp** — Hook extracted
- **UI:Panel** — Hook extracted
- **Manage:Content:Styles** — JS:Ace support

### Resource:Mail

- Connection separated from Resource (connection pool preparation)
- Limit and order on job Mail.Archive.dump
- Older mail libraries removed

### Infrastructure

- Common 1.0.x and HydrogenFramework 1.0.x support
- New autoloader syntax
- Database table IDs can be integer or string
- Rector: Elvis operator, null-coalescing operator
- Tool config files moved to their own folder
- Unit tests added
- HTTP links updated to HTTPS

---

## [0.9.0] — 2023-05-28

**Period:** February 2021 – May 2023  
**Commits:** 364

### Namespace Migration

- Complete migration to CeusMedia/Common 0.9 and Hydrogen 0.9 with namespaces
- All `CMF_Hydrogen_*` classes replaced by namespaced classes
- All `ADT_*` classes migrated
- File extensions changed from `.php5` to `.php`
- Module folders moved to new `src` folder

### PHP Support

- PHP 7.4 support added
- PHP 8.0 support (match is reserved)
- PHP 8.1 support in separate branch
- PHPStan added
- Short array syntax applied consistently

### New Modules

- **Resource:Import** — Import rules, transfer targets, job definition
- **Manage:Import** — Import rule management
- **Resource:Disclosure** — Privacy disclosure
- **Server:Log:Sentry** — Sentry error logging with environment and release
- **UI:Font:Muli** — Muli font
- **UI:Font:Signika** — Signika font

### Resource:Mail

- Attachment rules and attachment folders
- File path on attachments and rules
- Regenerate mail objects from raw (job)
- Show attachments in mail list
- Strict mail generation

### Resource:Forms

- Form transfer data processor alongside data mapper
- Transfer target management
- Fill filter with ID
- Multiple forms in fill filter, list, and export
- Fill transfer fail report

### UI

- Bootstrap library supported
- Font Awesome supported
- Submenu divider (via page chapters) in dropdown menu
- Module source indexer for index files

### Work:Missions

- ICal export extracted to view helper

### Infrastructure

- Migration script for transition to v0.9
- Module index as Composer scripts
- ACL handler for Hydrogen Framework 0.8.7.8
- Module XML files updated
- Composer file and PHPStan config updated, Rector added

---

## [0.8] — 2017-11-08

**First tagged version.**

### Highlights

- SVG images supported in thumbnail helper
- Thumbnailer style improved
- File browser improved

---

## Version Overview

| Version | Date | Commits | Milestone |
|---------|-------|---------|-------------|
| 0.8 | 2017-11-08 | — | First tagged version |
| 0.9.0 | 2023-05-28 | 364 | Namespace migration, PHP 7.4/8.x, new modules |
| 1.0.0 | 2025-02-11 | 355 | PHP 8.1, entity migration, Hydrogen 1.0 |
| 1.0.1 | 2025-10-10 | 194 | Resource:SMB, Server:Log:Request, InfluxDB |
| 1.0.2 | 2026-11-13 | 534 | Security modules, newsletter, shop, entity migration |
| 1.1.0 | 2026-05-01 | — | Branch split from 1.0.x, refactoring preparation |
| 1.1.1 | 2026-11-13 | 2 | Bugfixes: Undo, Forms AJAX, IP check entity |

---

*The version numbers of individual modules may differ from the repository version, as modules are versioned independently.*