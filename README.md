# Syndication for Exponential

Syndication copies content from one Exponential site to another. One site **exports** parts of its content tree as
feeds; other sites **import** those feeds over SOAP and create (or update) the same objects in their own content
tree. It works one way (a central site feeds several satellite sites) or both ways (each site exports some subtrees
and imports the others).

Typical uses:

- a group of sites that share news, press releases or product pages written once on a central site;
- a portal that pulls the content of member sites into one place;
- satellite sites that share a user or member area kept on a main site.

Everything is managed from the **Syndication** tab of the administration (shown with the rss icon in admin4), from
the console, and from two cronjob parts.

- Source: <https://github.com/se7enxweb/syndication>
- Composer package: [`se7enxweb/syndication`](https://packagist.org/packages/se7enxweb/syndication)
- Issues: <https://github.com/se7enxweb/syndication/issues>

## Contents

- [How it works](#how-it-works)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Console commands and cronjobs](#console-commands-and-cronjobs)
- [Permissions](#permissions)
- [Upgrading](#upgrading)
- [Known limits](#known-limits)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)
- [Credits and history](#credits-and-history)

## How it works

```
 exporting site                                        importing site
 --------------                                        --------------
 feed "news"                                           import "Partner news"
   source: subtree /News        cronjob export_feed      server: https://export.example.com/index_soap.php
   filters (section, attribute) ------------------>      feed:   news
          |                     writes an XML cache      filters, placement, options
          v                     per object                       |
 var/<site>/storage/syndication/feed/...                         | cronjob import_feed
          ^                                                      |  1. fetch the item list (SOAP)
          |           SOAP functions of index_soap.php           |  2. import the waiting items
          +------------------------------------------------------+
```

1. On the exporting site you create a **feed** and give it one or more **sources**: a single node, or a subtree (the
   node and everything below it). **Filters** on a source narrow it down by section or by attribute value.
2. The cronjob part `export_feed` writes every object of every active feed to an XML cache (with its related objects
   and binary files).
3. The extension registers SOAP functions (`fetchSyndicationFeedList`, `fetchSyndicationFeedItemList`,
   `fetchSyndicationFeedContentObject`, `fetchSyndicationFeedRelatedContentObject`, `hostID` and
   `fetchSyndicationFeedObjectList`) in the site's SOAP server, `index_soap.php` (`soap.php` before Exponential
   6.0.15).
4. On the importing site you create an **import**: the address of the other site's SOAP server, the feed to take, the
   location where the objects go, optional import filters and options.
5. The cronjob part `import_feed` fetches the list of new or changed items and imports them, either at once
   (automatic import) or after an editor approves them.

## Requirements

- Exponential 6.x (the legacy kernel) on **both** sites, each with this extension active
- PHP 8.0 or newer with the `dom` extension; `curl` when the exporting site is addressed over `https://`
- SOAP enabled on the exporting site (the extension's `soap.ini` does this, see [Configuration](#configuration)),
  and its web server letting the SOAP server through (see [The SOAP server's address](#the-soap-servers-address))
- MySQL / MariaDB or PostgreSQL (the tables come from `share/db_schema.dba`; `sql/mysql.sql` is the MySQL dump)
- Shell access for the cronjobs

## Installation

Install the extension on every site that exports or imports.

### With Composer

```bash
composer require se7enxweb/syndication
```

The installer plugin places the extension in `extension/syndication`.

### By hand

1. Download a release from <https://github.com/se7enxweb/syndication/releases> (or `git clone` the repository) and
   copy it to `extension/syndication` of your installation.

### Then, in both cases

2. Activate the extension in `settings/override/site.ini.append.php`:

   ```ini
   [ExtensionSettings]
   ActiveExtensions[]=syndication
   ```

3. Regenerate the autoload arrays and clear the caches:

   ```bash
   php bin/php/ezpgenerateautoloads.php -e
   php bin/php/ezcache.php --clear-all
   ```

4. Create the database tables, with one of:

   ```bash
   ./console ext:syndication:install            # or: php extension/syndication/bin/php/install.php
   ./console ext:syndication:install --dry-run  # only lists the missing tables
   ```

   or open the Syndication tab: while tables are missing, the dashboard offers a **Create the tables** button (it
   needs the policies `syndication/edit_export` and `syndication/edit_import`). On MySQL you can also load
   `extension/syndication/sql/mysql.sql` yourself.

5. Give the editors who work with syndication a role with the policies they need (see [Permissions](#permissions)).

6. Add the cronjobs (see [Console commands and cronjobs](#console-commands-and-cronjobs)).

## Configuration

The extension brings its settings in `extension/syndication/settings/`. Do not edit those files: put your changes in
`settings/override/<file>.append.php` (or a siteaccess) instead.

### syndication.ini

```ini
[SyndicationFilters]
# The filter types offered for feed sources and imports. A type is the name of a class in classes/filter/
# (eZFilterSection, eZFilterAttribute) without its prefix. A type not in this list is refused.
FilterArray[]
FilterArray[]=Section
FilterArray[]=Attribute

[Syndication]
# The ID of the user the cronjobs and console commands run as. 14 is the administrator of a fresh installation;
# use a user that can read the exported subtrees and create objects in the import locations.
CronUser=14
# Declared for the cache directory; the code currently writes to var/<site>/storage/syndication/feed and
# var/<site>/cache/syndication whatever this says.
CacheDir=syndication
```

### soap.ini

The exporting site must answer SOAP requests. The extension turns SOAP on and registers itself:

```ini
[GeneralSettings]
EnableSOAP=true

[ExtensionSettings]
SOAPExtensions[]=syndication
```

If the SOAP server of the exporting site is protected (for example by HTTP authentication), enter the login and
password in the first step of the import wizard.

### The SOAP server's address

The SOAP server of an Exponential installation is `index_soap.php` since Exponential 6.0.15; before, it was
`soap.php`. On 6.0.15 and later `soap.php` still answers, as a shim that forwards to `index_soap.php` and writes a
notice to `var/log/warning.log` once a day, and it will be removed in a later major version.

| Exporting site runs | Address an import uses |
| --- | --- |
| Exponential 6.0.15 or later | `https://export.example.com/index_soap.php` |
| Exponential 6.0.14 or older | `https://export.example.com/soap.php` |

The dashboard and a feed's page show the address of the site you are on: `index_soap.php` when the kernel has it,
`soap.php` on an older kernel. Imports created before keep the address they were given; change them to
`index_soap.php` once the exporting site runs 6.0.15 (edit the import, step 1).

The exporting site's web server must let `index_soap.php` through: with the kernel's `.htaccess_root`, uncomment
`RewriteRule ^index_soap\.php - [L]`; in an nginx server block add a `location = /index_soap.php` that hands it to
PHP-FPM. The kernel's documentation page "The SOAP entry script is now index_soap.php"
(`doc/eng-us/bc/6.0/soap-entry-script-index-soap.md`) has the lines for Apache, Plesk, nginx and the other servers
and a test with `curl`.

### browse.ini

Two browse modes are used by the wizards: `[SyndicationFeedSourceBrowse]` (choosing the node of a feed source) and
`[SyndicationSetImportPlacement]` (choosing where imported objects go). Change their `StartNode` or
`TopLevelNodes[]` to start browsing somewhere else.

### Other settings files

| File | What it does |
| --- | --- |
| `menu.ini` | The **Syndication** tab (`[Topmenu_syndication]`, `Icon=rss`) and its left menu (`[Leftmenu_syndication]`) |
| `cronjob.ini` | The cronjob parts `export_feed` and `import_feed` |
| `module.ini`, `design.ini`, `site.ini` | Register the `syndication` module, the design and the translations |
| `content.ini` | Adds the custom tag `syndication`; the `ezsyndicate` datatype lines are commented out (see [Known limits](#known-limits)) |

## Usage

Open the **Syndication** tab. Its start page is a dashboard: what is exported, what is imported, the last runs, and a
list of problems (a feed without a source, an import without a server, feed or location, failed items). The left
menu leads to **Feeds**, **Imports** and **Feed Sources**.

### Export: offer a feed

On the exporting site:

1. **Syndication > Feeds > New feed** (or **New feed** on the dashboard).
2. Fill in the form:
   - **Name** and an optional **Identifier** (a short name without spaces, for example `news`);
   - **Description for subscribers**;
   - **Cache expiry time** (0 turns the cache off) and **Object expiry time**;
   - **Force the cronjob cache** (recommended): no cache is written during SOAP calls, only by the cronjob;
   - **Active**: only active feeds are exported and offered to other sites.
3. **Add a source**: choose **A single node** or **A subtree: the node and everything below it**, then browse to the
   node. A subtree source includes its top node.
4. Optional: **Edit filters** of a source. With several filters, an object is exported only when it matches all of
   them. Example: only objects in the section *Standard*, or only objects whose attribute `show_on_partner_sites`
   is set.
5. Write the cache once by hand to check the feed (dashboard **Run now > Export all active feeds**, or
   `./console ext:syndication:export --feed=<id>`), then let the cronjob keep it up to date.

The feed's page (**feed_info**) shows its sources, the exported items and whether SOAP is on.

### Import: take a feed from another site

On the importing site:

1. **Syndication > Imports > New import**.
2. **Step 1**: a name and the address of the other site's SOAP server, for example
   `https://export.example.com/index_soap.php` (`.../soap.php` when that site runs Exponential 6.0.14 or older), plus
   a login and password when it is protected.
3. **Step 2**: choose one of the feeds the other site offers. An empty list means that site has no active feed.
4. **Step 3**: import filters (optional). Without a filter every object of the feed is imported; with filters, an
   object is imported when one of them accepts it.
5. **Step 4**: options:
   - **Location for the imported content** and **Location for related objects** (images, files and so on);
   - **Active**: the cronjob fetches this import;
   - **Import the objects automatically** (otherwise they wait for approval);
   - **Exclude the top node**, **Import related objects too**, **Take over the hidden status of the exporting site**.
6. **Step 5**: **Finish** saves the import.

The import's page (**import_info**) has **Fetch the item list now** and lists the items with their status: waiting,
installing, installed or failed. When automatic import is off, editors with the `import_object_status` policy approve
the waiting items on **syndication/pending_edit/<import id>** (**Approve all that can be changed** approves them in one go).

### Run now

The dashboard's **Run now** box starts the export of all active feeds or the fetch of all active imports as a
background run. Its page (**syndication/job/<id>**) shows the progress and the log lines; only the user who started
a run can see it. Only one export and one import run at a time, whoever starts them (cron, console or admin).

## Console commands and cronjobs

### Cronjobs

Run both parts regularly, on the exporting site and on the importing site respectively:

```cron
# exporting site: write the export cache every 5 minutes
*/5 * * * * cd /path/to/exponential && php runcronjobs.php export_feed
# importing site: fetch and import every 5 minutes
*/5 * * * * cd /path/to/exponential && php runcronjobs.php import_feed
```

Run them by hand the first time, so you can watch the initial (largest) sync. Later runs only handle new and changed
objects.

### Console commands

Each command also exists as a script in `extension/syndication/bin/php/` for installations without `./console`.
`--help` lists the options.

| Command | Script | What it does |
| --- | --- | --- |
| `ext:syndication:install [--dry-run]` | `install.php` | Creates the missing tables |
| `ext:syndication:status` | `status.php` | What is exported and imported, the last runs, the problems found |
| `ext:syndication:export` | `export.php` | Writes the export cache (the `export_feed` part) |
| `ext:syndication:import` | `import.php` | Fetches the item lists and imports the waiting items (the `import_feed` part) |

Examples:

```bash
./console ext:syndication:status
./console ext:syndication:export --dry-run              # count what would be written
./console ext:syndication:export --feed=3 --max-objects=500
./console ext:syndication:import --fetch-only           # only fetch the item lists
./console ext:syndication:import --import=2 --limit=50  # import up to 50 items of import 2
php extension/syndication/bin/php/import.php --import-only
```

Options of `export`: `--dry-run`, `--feed=<id>` (only this feed, active or not), `--max-objects=<n>` (0: no limit).
Options of `import`: `--dry-run`, `--import=<id>`, `--fetch-only`, `--import-only`, `--limit=<n>` (items per import,
default 10).

## Permissions

The module `syndication` has these policy functions (**Roles and policies** in the administration):

| Function | Allows |
| --- | --- |
| `menu` | The Syndication tab, its dashboard and the background runs |
| `view_export` | The lists of feeds and imports |
| `view_export_info` | The page of a feed |
| `edit_export` | Creating and editing feeds, their sources and source filters |
| `create_feed`, `remove_feed` | Creating and removing feeds |
| `view_import` | The page of an import |
| `edit_import` | Creating and editing imports and import filters |
| `import_object_status` | Approving waiting import items |
| `create_import`, `fetch_feed` | Declared for completeness; not checked by the current views |

Creating the tables from the dashboard needs both `edit_export` and `edit_import`.

## Upgrading

1. Replace the extension's files (`composer update se7enxweb/syndication`, or copy the new release over
   `extension/syndication`).
2. Regenerate the autoloads and clear the caches (see [Installation](#installation)).
3. Run `./console ext:syndication:install --dry-run`; if it lists missing tables, run it without `--dry-run`.
4. When the exporting site moves to Exponential 6.0.15 or later, let `index_soap.php` through at its web server and
   change the server address of each import on the importing sites to `.../index_soap.php` (see
   [The SOAP server's address](#the-soap-servers-address)). `soap.php` keeps answering until then.
5. Read [CHANGELOG.md](CHANGELOG.md) for what changed.

## Known limits

- Both sites need this extension; it is not an RSS or Atom importer. (For RSS feeds use the kernel's RSS
  import and export.)
- The SOAP protocol is the extension's own and is not versioned: keep both sites on similar releases.
- The `ezsyndicate` datatype (`datatypes/ezsyndicate`) is disabled in `content.ini` and nothing in the extension uses
  the queue it writes to; it is kept from the original release.
- `kernel_patch/addrelated.diff` is a change to two kernel datatypes from the original release that records object
  relations for embedded and related objects. It is not applied by the extension; prefer an override class over
  patching the kernel.
- `[Syndication] CacheDir` is not read yet (see [Configuration](#configuration)).

## Troubleshooting

| Symptom | Check |
| --- | --- |
| The tab is missing | The extension is in `ActiveExtensions[]`, the caches are cleared, the user has the `syndication/menu` policy |
| The dashboard says the tables are missing | Run `./console ext:syndication:install` |
| "SOAP feed list request ... did not return a valid result" in the import wizard | The address points to the SOAP server (`index_soap.php`, or `soap.php` on Exponential 6.0.14 and older), the exporting site's web server lets it through (`curl -i https://export.example.com/index_soap.php` answers "only understand POST methods", not 404 or a page), SOAP is on at the exporting site (the dashboard shows **SOAP on**), the login is right, `curl` is loaded for `https://` |
| Step 2 of the import wizard lists no feed | The exporting site has no **active** feed |
| Items stay waiting | **Import the objects automatically** is off: approve them on the pending page, or turn it on |
| Items fail | The import's page lists them; check that `CronUser` can create objects in the chosen locations |
| "Another export is running." or "Another import is running." | A run is already going (cron, console or admin); wait for it to end |
| A new or renamed class is not found | `php bin/php/ezpgenerateautoloads.php -e` |

## Contributing

Bug reports and pull requests are welcome at <https://github.com/se7enxweb/syndication>. Please describe what you
changed and how you tested it. The DB-less tests in `tests/` run with PHPUnit from an installation root, for example:

```bash
php vendor/bin/phpunit extension/syndication/tests/eZSyndicationStylesTest.php
```

## License

GNU General Public License v2.0 (or any later version). See [LICENSE](LICENSE).

This program is distributed WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE.

## Credits and history

Syndication was written by eZ Systems around 2004 to 2008 for eZ Publish, the content management system that was
discontinued by its creator after about eighteen years. 7x published the extension in 2025 and has maintained it for
Exponential since: PHP 8 support, a dashboard, console commands, background runs, translations in twelve languages
and a modern administration. 7x (<https://se7enx.com>) is a web design, development, support and hosting company,
formerly known as Brookins Consulting.
