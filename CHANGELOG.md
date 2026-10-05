# Changelog

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
