# HydrogenModules — Changelog

Alle bemerkenswerten Änderungen an diesem Projekt werden in dieser Datei dokumentiert.

---

## [1.0.2] — 2026-11-13

**Zeitraum:** Oktober 2025 – November 2026  
**Commits seit 1.0.1:** 534

### Neue Module

- **Security:IP:Check** (0.9.1) — IP-Prüfung mit Data-Entities für Query, Source und Result
- **Security:Altcha** (0.2.1) — ALTCHA CAPTCHA-Integration mit JS-Modul für Client-seitige Verifikation
- **Security:Limiter:Request** (0.2.1) — Request-Ratenbegrenzung basierend auf Server:Log:Request
- **Server:Measurement:InfluxDB** — InfluxDB-Metriken mit SSL-Zertifikatsprüfung, Default-Tags und Exception-Hook
- **Server:Log:Request** (0.2.8) — Request-Logging mit Session-ID, POST/PUT-Body, Logfile-Modus und Microtime-Vergleich
- **Resource:SMB** — SMB-Dateizugriff via icewind/smb
- **Work:Notifications** (0.3.0) — Benachrichtigungs-Modul, feature-complete zum ersten Konzept
- **Work:Meetings** (0.2.7) — Meeting-Management mit Modal-Ansicht, Teilnehmer-Verwaltung und Job-Scheduler

### Manage:Forms — Major-Update (0.6.6 → 0.7.2)

- API-Import mit Request-Limits via Server:Log:Request
- SPAM-Check-Hook
- AJAX-Controller für Form-Fill-Filter
- ALTCHA-Unterstützung und Redirect on Submit
- Fill-Token für Bestätigung
- Form-Fill-Receiver vollständig auf AJAX migriert
- Rejected Requests werden geloggt

### Newsletter — Major-Überarbeitung

#### Resource:Newsletter (0.5.3 → 0.6.2)

- Link-Click-Tracking für Reader-Letter
- Tester-Flag im Reader-Modell
- Mail-Template um Newsletter-Template herum
- Reader-Letter können in der Zukunft versendet werden
- Theme/Template-Snapshot mit Mail-Template-ID
- Vollständige Entity-Implementierung
- Englische Sprachdatei

#### Work:Newsletter (1.4.4 → 1.5.6)

- Import-Strategien mit Deduplizierung
- Link-Views und responsives Design
- Queue-basierter Versand für zukünftige Reader-Letter
- Template-Snapshot (Theme) verbessert
- Group-Relations-Unterstützung
- Module-Assets on-demand laden

#### Info:Newsletter (0.7.3 → 0.7.6)

- Verbesserte Vorschau des neuesten Newsletters
- Link-Click-Tracking
- Queue-Job ohne Limit

### Resource:Mail — Refactoring (1.1.3 → 1.1.7)

- Mail-Priorität auf Mail-Objekt und Queue
- HTML-Mail-Rendering mit modulbasierten Placeholdern
- Statischer Konstruktor auf abstrakter Mail-Klasse
- Mail-Sprache-Unterstützung
- Timestamp-Spalte für gesendete Mails
- Hook `Mail::onSent` nach Transport
- Queue-Pagination Performance verbessert

#### Admin:Mail (1.0.4 → 1.0.5)

- Mehr Bulk-Aktionen in Queue-Liste
- Autocomplete-E-Mail-Filter in Queue
- Verbesserte Spalten und Page-Handling bei Bulk-Aktionen

### Security & CSRF

- **Security:CSRF** (0.3.5) — Refresh und Hints im View-Helper, Hauptschalter
- **Manage:My:User** (0.6.3) — CSRF-Schutz aktiviert
- **UI:LanguageSelector** (0.2.7) — Hauptschalter

### User-Management

#### Resource:Users (0.4.6 → 0.4.9)

- Logic-Klassen für Group-Relations und Roles
- Language-Spalte im User-Modell
- Password-Status „update"
- Methoden: `countRoleUsers`, `getRoles`, `getRoleUsers`, `getUser`, `getGroupUsers`

#### Manage:Users (0.8.2 → 0.8.3)

- Role-Rights-Indikator auf Index
- Helper für Module-Entity-Group-Relations
- E-Mail-Filter

#### Manage:My:User

- Passwort-Verwaltung in eigenem Tab
- Benutzer-sprache änderbar
- Leere E-Mail erlaubt

#### Resource:Authentication

- User-Logic-Extensions in allen Backends (Local, JSON, OAuth, OAuth2, REST)
- Password-Status „update"

### Admin:Log:Exception (0.6.6 → 0.7.0)

- Rechte, Index und View implementiert
- Öffentliche Statistiken (als JSON)
- ID-Filter
- View mit View-Helper und Style refaktoriert
- IO und Logic Exception Extra-Data

### Shop & Catalog

- **Catalog:Bookstore** — Entities und Messungen
- **Shop:Payment:PayPal** — Strategien, Payment-Request/Response speichern, Billing-Address statt User
- **Shop:Payment:Stripe** — Giropay und Sofort deaktiviert
- Order-Entity, Shipping-View-Helper
- Cart-Positions-Strategien und Quantity-Buttons extrahiert
- Abstrakte Shop-Mail-Klasse
- Payment-Backend-Refactoring

### Resource:Jobs (0.9.0)

- Job-Discovery per Config: by request (always) oder by job call (job)
- Result-Entity
- Scheduler: Job-Definition von XML auf Module-Config migriert
- Strikte Entity-Nutzung

### Entity-Migration (großflächig)

- **Catalog:\***, **Manage:Catalog:\*** — Entity-Migration
- **Resource:Downloads** — Model-Entities
- **Resource:Payment** — Payment-Entity
- **Resource:Sitemap** — Sitemap-Links-Entity
- **UI:Helper:Relations** — Entities für Module-Relation
- **UI:Navigation** — Menu-Item und Pages Entities
- **Server:IP:Lock** — Entity-Klassen mit Unlock- und Cleanup-Jobs
- **Resource:Address** (0.3.0) — Logic-Klasse mit ersten Methoden

### Info:News / Manage:News

- Neuer Status: **outdated** (veraltet)

### Manage:Relocation

- CSV-Export

### UI & Frontend

- **UI:Bootstrap** — v4.6.2 und v5.3.3 hinzugefügt
- **JS:TinyMCE** — Version 8.2.2 unterstützt
- **UI:Helper:Forms** — ALTCHA-Unterstützung
- **TimePhraser** — unterstützt nun Kasus (Dativ etc.) neben Nominativ

### Work:Mail

- **Work:Mail:Check** (0.3.7) — Datenbank-Tabellen und Primary Keys umbenannt
- **Work:Mail:Sync** — Hook extrahiert

### Work:FTP (0.2.5)

- AJAX-Controller extrahiert

### Lokalisierung (großflächig)

- Admin:\*, Catalog:\*, Info:\*, Manage:\*, Resource:\*, Server:\*, UI:\*, Work:\* — Lokalisierung abgeschlossen

### Infrastruktur & Code-Qualität

- Hooks von `App:response` auf `Page::render` geändert
- Module-XML auf neues Schema `module-1.0.0.xsd` aktualisiert
- PHPStan von 1 auf 2 aktualisiert
- Auth-Session-Keys durch Konstanten ersetzt
- Singleton-Fixes
- Deprecated Module entfernt
- Code-Style und Strictness verbessert

---

## [1.0.1] — 2025-10-10

**Zeitraum:** Februar 2025 – Oktober 2025  
**Commits:** 194

### Neue Module

- **Resource:SMB** — SMB-Dateizugriff via icewind/smb
- **Server:Log:Request** — Request-Logging mit Session-ID, POST/PUT-Body, Logfile-Modus
- **Server:Measurement:InfluxDB** — InfluxDB-Metriken mit SSL-Zertifikatsprüfung, Default-Tags und Exception-Hook

### Entity-Migration

- **Server:IP:Lock** — Entity-Klassen mit Unlock- und Cleanup-Jobs
- **UI:Navigation** — Menu-Item und Pages Entities
- **UI:Helper:Relations** — Entities für Module-Relation
- **Resource:Downloads** — Model-Entities
- **Resource:Sitemap** — Sitemap-Links-Entity
- **Resource:Payment** — Payment-Entity
- **Shop:Payment** — Entity für Payment-Backend
- **Shop:Bridge** — Entities
- **Catalog:Bookstore** — Entities und Messungen
- **Shop** — Order-Entity, Order-Positions-Entity
- **Resource:Jobs** — Result-Entity

### Shop & Payment

- Shop-Module refaktoriert
- Payment-Backend-Handling refaktoriert
- PayPal: Strategien, Payment-Request/Response speichern, Billing-Address statt User
- Stripe: Giropay und Sofort deaktiviert
- Abstrakte Shop-Mail-Klasse
- Cart-Positions-Strategien und Quantity-Buttons extrahiert
- Shipping-View-Helper
- Payment-Backend-Register

### Resource:Jobs

- Scheduler: Job-Definition von XML auf Module-Config migriert
- Job-Discovery per Config (by request / by job call)
- Strikte Entity-Nutzung

### Work:Meetings

- Initialer Prototype bis 0.2.4
- Job-Scheduler statt crond

### Work:Notifications

- Initial commit 0.2.0 bis 0.3.0 (feature-complete)

### Resource:Mail

- Mail-Queue-Pagination Performance verbessert

### UI

- **TimePhraser** — unterstützt nun Kasus (Dativ etc.) neben Nominativ

### Code-Qualität

- Auth-Session-Keys durch Konstanten ersetzt
- Hooks von `App:response` auf `Page::render` geändert
- Code-Style und Strictness verbessert
- Numerous hook extractions (Manage:Shop, Manage:Catalog, Manage:Customer, Work:Mail:Sync, etc.)

---

## [1.0.0] — 2025-02-11

**Zeitraum:** Mai 2023 – Februar 2025  
**Commits:** 355

### PHP 8.1 Migration

- Vollständige Migration aller Module zu PHP 8.1 mit strict types
- Mindestens PHP 8.1 erforderlich (Support für ältere Versionen entfernt)
- PHPStan Level 0 erreicht, Arbeit an Level 1 begonnen

### Neue Module

- **Resource:Mail:Sent** — Speichert gesendete Mails auf IMAP-Server
- **Resource:Downloads** — Aus Info:Downloads und Info:Files extrahiert

### Entity-Migration (großflächig)

- Erste Model-Entity-Klassen in Resource:Users und Work:Missions
- **Resource:Mail** — Entity für Mail-, Attachment- und Template-Modelle
- **Security:CSRF** — Model-Entity
- **UI:Image:Slider** — Model-Entity-Klassen
- **Resource:Pages** — Page-Entity
- **Resource:Shares** — Model-Entity
- **Resource:FileBucket** — Model-Entity
- **Resource:Database:Lock** — Model-Entity
- **Resource:Forms** — Entities für alle Modelle
- **Resource:Import** — Model-Entity-Klassen
- **Resource:Jobs** — Model-Entity-Klassen
- **Manage:Projects** — Model-Entity-Klassen
- **Info:Manual** — Model-Entities

### Shop & Payment

- Payment-Registration refaktoriert
- Payment-Method-Kosten unterstützt
- Unified payment fee calculation mit Logic-Klasse

### Resource:Import

- Import-Logik extrahiert
- Connector-Model mit Type und MIME Types erweitert

### UI

- **UI:Helper:StatusBadge** — Bootstrap-Badge für Entity-Status-Informationen
- **UI:Bootstrap:Switch** — Hook extrahiert
- **UI:Bootstrap:Datepicker** — Hook extrahiert
- **UI:ContextHelp** — Hook extrahiert
- **UI:Panel** — Hook extrahiert
- **Manage:Content:Styles** — JS:Ace-Unterstützung

### Resource:Mail

- Connection von Resource getrennt (Connection-Pool-Vorbereitung)
- Limit und Order auf Job Mail.Archive.dump
- Ältere Mail-Libraries entfernt

### Infrastruktur

- Common 1.0.x und HydrogenFramework 1.0.x Unterstützung
- Neuer Autoloader-Syntax
- Database table IDs können integer oder string sein
- Rector: Elvis-Operator, null-coalescing operator
- Tool-Config-Dateien in eigenen Ordner verschoben
- Unit-Tests hinzugefügt
- HTTP-Links zu HTTPS aktualisiert

---

## [0.9.0] — 2023-05-28

**Zeitraum:** Februar 2021 – Mai 2023  
**Commits:** 364

### Namespace-Migration

- Vollständige Migration zu CeusMedia/Common 0.9 und Hydrogen 0.9 mit Namespaces
- Alle `CMF_Hydrogen_*` Klassen durch namespaced Klassen ersetzt
- Alle `ADT_*` Klassen migriert
- Dateiendungen von `.php5` auf `.php` geändert
- Module-Ordner in neuen `src`-Ordner verschoben

### PHP-Support

- PHP 7.4 Support hinzugefügt
- PHP 8.0 Support (Match ist reserviert)
- PHP 8.1 Support in separater Branch
- PHPStan hinzugefügt
- Short array syntax durchgängig angewendet

### Neue Module

- **Resource:Import** — Import-Regeln, Transfer-Targets, Job-Definition
- **Manage:Import** — Import-Regel-Management
- **Resource:Disclosure** — Datenschutz-Disklosure
- **Server:Log:Sentry** — Sentry-Fehlerprotokollierung mit Environment und Release
- **UI:Font:Muli** — Muli-Schriftart
- **UI:Font:Signika** — Signika-Schriftart

### Resource:Mail

- Attachment-Regeln und Attachment-Folders
- File-Path auf Attachments und Rules
- Mail-Objekte aus Raw regenerieren (Job)
- Attachments in Mail-Liste anzeigen
- Strikte Mail-Generierung

### Resource:Forms

- Form-Transfer-Data-Processor neben Data-Mapper
- Transfer-Target-Management
- Fill-Filter mit ID
- Multiple Forms in Fill-Filter, Liste und Export
- Fill-Transfer-Fail-Report

### UI

- Bootstrap-Bibliothek unterstützt
- Font Awesome unterstützt
- Submenu-Divider (via Page-Chapters) in Dropdown-Menü
- Module-Source-Indexer für Index-Dateien

### Work:Missions

- ICal-Export in View-Helper extrahiert

### Infrastruktur

- Migration-Skript für Transition zu v0.9
- Module-Index als Composer-Scripts
- ACL-Handler für Hydrogen Framework 0.8.7.8
- Module-XML-Dateien aktualisiert
- Composer-Datei und PHPStan-Config aktualisiert, Rector hinzugefügt

---

## [0.8] — 2017-11-08

**Erste getaggte Version.**

### Highlights

- SVG-Bilder im Thumbnail-Helper unterstützt
- Thumbnailer-Style verbessert
- Filebrowser verbessert

---

## Versionsübersicht

| Version | Datum | Commits | Meilenstein |
|---------|-------|---------|-------------|
| 0.8 | 2017-11-08 | — | Erste getaggte Version |
| 0.9.0 | 2023-05-28 | 364 | Namespace-Migration, PHP 7.4/8.x, neue Module |
| 1.0.0 | 2025-02-11 | 355 | PHP 8.1, Entity-Migration, Hydrogen 1.0 |
| 1.0.1 | 2025-10-10 | 194 | Resource:SMB, Server:Log:Request, InfluxDB |
| 1.0.2 | 2026-11-13 | 534 | Security-Module, Newsletter, Shop, Entity-Migration |

---

*Die Versionsnummern der einzelnen Module können von der Repository-Version abweichen, da Module eigenständig versioniert werden.*