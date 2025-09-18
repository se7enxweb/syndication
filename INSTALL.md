# Installing the extension

1. Extract the extension "syndication" to <exponential>/<extension dir>/syndication

2. Import the DB definition in <exponential>/<extension dir>/syndication/sql/mysql.sql

3. Optional but recommended. Install the provided code diff changes documented into a kernel override class (a great feature to add to this solution in the future), [kernel_patch/addrelated.diff](kernel_patch/addrelated.diff). We recommend a kernel override class instead of manually patching the kernel which is a poor long term solution which affects future upgrades, maintenance and support.

4. Enable the extension in the admin interface, or add to `settings/override/site.ini.append.php`
`   [ExtensionSettings]
   ActiveExtensions[]=syndication`

# Setting up an export

1. Enter <Exponential admin>/syndication/menu

2. Click "Export"

3. Click "New Feed"

4. Complete the wizard.

5. Set up the cronjob part "export_feed"

* Note: Selecting subtree source will include the subtree root in the export feed.

# Setting up an import

1. Enter <Exponential admin>/syndication/menu

2. Click "Import"

3. Click "New Import"

4. Complete the wizard.

5. Set up the cronjob part "import_feed"


# Features Tested and Working in General

Test & works:

- 1 way syndication

- 2 way syndication ( example: forum )
