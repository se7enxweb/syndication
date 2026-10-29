# Changelog

## 1.3.17 (2026-10-29)

- Updated: Both version fields of extension.xml name the release.

## 1.3.16 (2026-10-29)

- Updated: One copyright notice, Exponential Foundation and 7x.

## 1.3.15 (2026-10-29)

- Updated: The admin stylesheets use the shared admin tokens for weights, durations, radii, table headers and muted text, with the old values as fallbacks where a design has no tokens.

## 1.3.14 (2026-10-29)

- Updated: The SOAP server of Exponential is `index_soap.php` since Exponential 6.0.15 (`soap.php` before, a forwarding shim since). The dashboard and a feed's page show the address of the site's SOAP server, `index_soap.php` when the kernel has it and `soap.php` on an older kernel (`eZSyndicationUI::soapEntryScript()`, `soapURL()`). The import wizard gives `https://example.com/index_soap.php` as the example, in every translation. An import that calls an exporting site on 6.0.15 or later should name `index_soap.php`; `soap.php` keeps answering there until a later major version. An exporting site on 6.0.14 or older is still called at `soap.php`.
- Updated: README: the SOAP server's address by kernel release, what the exporting site's web server must let through, the upgrade step for existing imports, and a `curl` check in the troubleshooting table.
- Updated: extension.xml states the release in both of its version fields (the second still said 1.3.11).

## 1.3.13 (2026-10-28)

- Updated: The translations of syndication are loaded.

## 1.3.12

- Added: Syndication declares itself in Exponential's transfer registry (`settings/kernelservices.ini.append.php`, kernel 6.0.15 and later). The kernel lists it in its "Which import or export tool do I need?" page, the other tools' "Looking for…?" boxes, the "Import and export" lists, an item's Import/Export menu ("Copy to another site…") and the administration's search.
- Added: The Syndication dashboard shows the kernel's "Looking for another import or export?" box, and the module's left menu the "Import and export" list; on an older kernel both are left out.

## 1.3.11

- Updated: The Syndication tab of the administration shows the rss icon (`Icon=rss` in `[Topmenu_syndication]` of menu.ini).
- Updated: A modern README: how syndication works, requirements, installation with Composer and by hand, every setting, the export and import wizards step by step, console commands and cronjobs with examples, permissions, upgrading, known limits and troubleshooting. INSTALL.md points to it.
- Updated: composer.json states PHP 8.0 or newer and the dom extension, suggests curl, and has support links and keywords; extension.xml has a `<metadata>` block (name, version, copyright, license, website) and its dependencies; ezinfo.php and extension.xml state version 1.3.11.

## 1.3.10

- Fixed: The status and the log lines of a background export or import (syndication/job) are answered only for the user who started the run. A job records the id of the user who starts it; a job written by an earlier version has no owner and can no longer be read (it is pruned as before).
- Updated: The status pages use the shared .a4-badge classes of the administration's components.css instead of a pill of their own.

## 1.3.9

- Added: Translations in Croatian, Czech, Danish, Dutch, Italian, Norwegian, Polish, Brazilian Portuguese, Swedish and Turkish.

## 1.3.8

- Updated: The administration pages run without inline code (a Content Security Policy without 'unsafe-inline'): the confirmations, the check-all boxes, the progress of a background run and the "Approve all" button are in design/standard/javascript/syndication_admin.js.

## 1.3.4

- Removed: stray .DS_Store files and a leftover patch reject (modules/syndication/import_edit/ezimporteditfeed.php.rej); .gitignore keeps them out.

## 1.3.3

Fixed

- Removing a feed, a source, a filter or an import was a fatal error on PHP 8 (an instance method called statically); removing an import now also removes its filters and the draft row.
- The import wizard started over on every click and never got past its first step; the name step lost what was typed.
- The type of a filter, taken from a form, was evaluated as PHP code; it must now be one of `[SyndicationFilters] FilterArray`.
- Option arrays and the SOAP answer of the exporting server are no longer unserialized with objects.
- The first fetch of an import failed because the `modified` parameter was left out; an unreachable server (an int answer of the SOAP client) was a fatal error.
- `import_info` and `feed_info` are complete views; the policy of `import_info` is `view_import`.
- The export and import cronjob parts skip feeds and imports that are not active, and a source whose node is gone no longer stops the run.
- An undefined constant, an `ORDER BY` in a `COUNT` query (PostgreSQL), a non-static `version()`, `=&` on function results.
- `syndication.ini.append.php` has its PHP wrapper.

Added

- A dashboard as the start page: what is exported, what is imported, the last runs and the problems found; it creates the tables when they are missing.
- Lists with filter, sort and paging, detail views for feeds and imports, removal with a confirmation step, inline notices, validation of the feed form, English and German strings; the left menu comes from `parts/syndication/menu.tpl` (navigation part `ezsyndicationnavigationpart`).
- Console commands `ext:syndication:export`, `import`, `install` and `status`, runnable cronjob classes for `export_feed` and `import_feed` (one run at a time, run time and result recorded), and "run now" in the admin as a background run with progress.
