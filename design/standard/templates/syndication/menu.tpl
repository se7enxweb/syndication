{ezcss_require( 'syndication.css' )}
{def $feeds = $summary.feeds
     $imports = $summary.imports
     $item_counts = $summary.items
     $runs = $summary.runs}

<div class="context-block syn">
    <div class="box-header">
        <h1 class="context-title">{'Syndication'|i18n( 'extension/syndication' )}</h1>
        <div class="header-mainline"></div>
    </div>
    <div class="box-content">

        {include uri='design:parts/syndication/notices.tpl' notices=$notices}

        {include uri='design:parts/syndication/job.tpl' job_id=$job_id}

        {if $summary.tables_missing|count}
        <div class="syn-confirm">
            <h2>{'The extension is not installed in this database yet'|i18n( 'extension/syndication' )}</h2>
            <p>{'These tables are missing: %tables.'|i18n( 'extension/syndication',, hash( '%tables', $summary.tables_missing|implode( ', ' ) ) )|wash}</p>
            {if $can_install}
            <form action={'syndication/menu'|ezurl} method="post">
                <input class="defaultbutton" type="submit" name="InstallTablesButton" value="{'Create the tables'|i18n( 'extension/syndication' )|wash}" />
            </form>
            <p class="syn-hint">{'The tables are created from the extension\'s own schema file, on every database Exponential supports. Existing tables are not touched.'|i18n( 'extension/syndication' )}</p>
            {else}
            <p>{'You need the policies Syndication / edit_export and edit_import to create them.'|i18n( 'extension/syndication' )}</p>
            {/if}
        </div>
        {else}

        {* Status strip. *}
        <div class="syn-status">
            <div class="syn-status-facts">
                <span><strong>{$feeds.total}</strong> {'feeds'|i18n( 'extension/syndication' )} ({$feeds.enabled} {'active'|i18n( 'extension/syndication' )})</span>
                <span><strong>{$imports.total}</strong> {'imports'|i18n( 'extension/syndication' )} ({$imports.enabled} {'active'|i18n( 'extension/syndication' )})</span>
                <span><strong>{$feeds.sources}</strong> {'sources'|i18n( 'extension/syndication' )}</span>
            </div>
            <div>
                {if $summary.soap.enabled}<span class="syn-pill is-ok">{'SOAP on'|i18n( 'extension/syndication' )}</span>{else}<span class="syn-pill is-warn">{'SOAP off'|i18n( 'extension/syndication' )}</span>{/if}
                {if $summary.problems|count}<span class="syn-pill is-warn">{'%count to look at'|i18n( 'extension/syndication',, hash( '%count', $summary.problems|count ) )}</span>
                {else}<span class="syn-pill is-ok">{'No problems'|i18n( 'extension/syndication' )}</span>{/if}
            </div>
        </div>

        <div class="syn-cards">
            <section class="syn-card">
                <h2>{'Export'|i18n( 'extension/syndication' )}</h2>
                <p class="syn-muted">{'Feeds that other sites fetch from this one.'|i18n( 'extension/syndication' )}</p>
                <ul class="syn-stats">
                    <li><strong>{$feeds.total}</strong><span class="syn-muted">{'feeds'|i18n( 'extension/syndication' )}</span></li>
                    <li><strong>{$feeds.sources}</strong><span class="syn-muted">{'sources'|i18n( 'extension/syndication' )}</span></li>
                    <li><strong>{$feeds.items}</strong><span class="syn-muted">{'exported items'|i18n( 'extension/syndication' )}</span></li>
                </ul>
                <p class="syn-muted">
                    {if $runs.export}{'Last run'|i18n( 'extension/syndication' )}: <time>{$runs.export.time|l10n( 'shortdatetime' )}</time> ({$runs.export.by|wash})
                    {else}{'The export has not run yet.'|i18n( 'extension/syndication' )}{/if}
                </p>
                <div class="syn-links">
                    <a class="button" href={'syndication/list'|ezurl}>{'Feeds'|i18n( 'extension/syndication' )}</a>
                    {if $can_create_feed}<form action={'syndication/list'|ezurl} method="post" style="display:inline"><input class="defaultbutton" type="submit" name="CreateButton" value="{'New feed'|i18n( 'extension/syndication' )|wash}" /></form>{/if}
                </div>
            </section>

            <section class="syn-card">
                <h2>{'Import'|i18n( 'extension/syndication' )}</h2>
                <p class="syn-muted">{'Feeds of other sites that this site fetches.'|i18n( 'extension/syndication' )}</p>
                <ul class="syn-stats">
                    <li><strong>{$imports.total}</strong><span class="syn-muted">{'imports'|i18n( 'extension/syndication' )}</span></li>
                    <li><strong>{$item_counts.installed}</strong><span class="syn-muted">{'installed'|i18n( 'extension/syndication' )}</span></li>
                    <li><strong>{sum( $item_counts.pending, $item_counts.installing )}</strong><span class="syn-muted">{'waiting'|i18n( 'extension/syndication' )}</span></li>
                    <li><strong{if $item_counts.failed} class="syn-danger"{/if}>{$item_counts.failed}</strong><span class="syn-muted">{'failed'|i18n( 'extension/syndication' )}</span></li>
                </ul>
                <p class="syn-muted">
                    {if $runs.import}{'Last run'|i18n( 'extension/syndication' )}: <time>{$runs.import.time|l10n( 'shortdatetime' )}</time> ({$runs.import.by|wash})
                    {else}{'The import has not run yet.'|i18n( 'extension/syndication' )}{/if}
                </p>
                <div class="syn-links">
                    <a class="button" href={'syndication/import_list'|ezurl}>{'Imports'|i18n( 'extension/syndication' )}</a>
                    {if $can_create_import}<form action={'syndication/import_list'|ezurl} method="post" style="display:inline"><input class="defaultbutton" type="submit" name="Create" value="{'New import'|i18n( 'extension/syndication' )|wash}" /></form>{/if}
                </div>
            </section>

            <section class="syn-card">
                <h2>{'Run now'|i18n( 'extension/syndication' )}</h2>
                <p class="syn-muted">{'The cronjob parts do this on a schedule. Here you can run them by hand.'|i18n( 'extension/syndication' )}</p>
                <form action={'syndication/menu'|ezurl} method="post" data-syn-confirm="{'Write the export cache of every active feed now? This can take a while on a large tree.'|i18n( 'extension/syndication' )|wash}">
                    <input class="button" type="submit" name="ExportAllButton" value="{'Export all active feeds'|i18n( 'extension/syndication' )|wash}"{if or( $can_edit_export|not, $feeds.enabled|eq( 0 ) )} disabled="disabled"{/if} />
                </form>
                <form action={'syndication/menu'|ezurl} method="post" data-syn-confirm="{'Fetch the item list of every active import now?'|i18n( 'extension/syndication' )|wash}">
                    <input class="button" type="submit" name="FetchAllButton" value="{'Fetch all active imports'|i18n( 'extension/syndication' )|wash}"{if or( $can_edit_import|not, $imports.enabled|eq( 0 ) )} disabled="disabled"{/if} />
                </form>
                <p class="syn-hint">{'In cron:'|i18n( 'extension/syndication' )} <code>php runcronjobs.php export_feed</code> &middot; <code>php runcronjobs.php import_feed</code></p>
            </section>
        </div>

        <section>
            <h2>{'Problems and hints'|i18n( 'extension/syndication' )}</h2>
            {if $summary.problems|count}
            <ul class="syn-problems">
                {foreach $summary.problems as $problem}
                <li>
                    <span class="syn-pill {cond( eq( $problem.level, 'error' ), 'is-bad', eq( $problem.level, 'warning' ), 'is-warn', 'is-info' )}">{cond( eq( $problem.level, 'error' ), 'Problem'|i18n( 'extension/syndication' ), eq( $problem.level, 'warning' ), 'Warning'|i18n( 'extension/syndication' ), 'Hint'|i18n( 'extension/syndication' ) )}</span>
                    <span>{$problem.text|wash}{if $problem.url} <a href={$problem.url|ezurl}>{'Open'|i18n( 'extension/syndication' )}</a>{/if}</span>
                </li>
                {/foreach}
            </ul>
            {else}
            <p class="syn-ok">{'Nothing to report: every feed has a source, every import has a server, a feed and a location, and no item failed.'|i18n( 'extension/syndication' )}</p>
            {/if}
        </section>

        {if $recent_feeds|count}
        <section>
            <h2>{'Feeds'|i18n( 'extension/syndication' )}</h2>
            <table class="list syn-table">
                <tr><th>{'Name'|i18n( 'extension/syndication' )}</th><th>{'Active'|i18n( 'extension/syndication' )}</th><th class="syn-num">{'Sources'|i18n( 'extension/syndication' )}</th></tr>
                {foreach $recent_feeds as $feed sequence array( 'bglight', 'bgdark' ) as $seq}
                <tr class="{$seq}"><td><a href={concat( 'syndication/feed_info/', $feed.id )|ezurl}>{$feed.name|wash}</a></td>
                    <td>{if $feed.enabled}<span class="syn-pill is-ok">{'yes'|i18n( 'extension/syndication' )}</span>{else}<span class="syn-pill is-muted">{'no'|i18n( 'extension/syndication' )}</span>{/if}</td>
                    <td class="syn-num">{$feed.source_list|count}</td></tr>
                {/foreach}
            </table>
        </section>
        {/if}
        {if $recent_imports|count}
        <section>
            <h2>{'Imports'|i18n( 'extension/syndication' )}</h2>
            <table class="list syn-table">
                <tr><th>{'Name'|i18n( 'extension/syndication' )}</th><th>{'Active'|i18n( 'extension/syndication' )}</th><th class="syn-num">{'Waiting'|i18n( 'extension/syndication' )}</th><th class="syn-num">{'Failed'|i18n( 'extension/syndication' )}</th></tr>
                {foreach $recent_imports as $import sequence array( 'bglight', 'bgdark' ) as $seq}
                <tr class="{$seq}"><td><a href={concat( 'syndication/import_info/', $import.id )|ezurl}>{$import.name|wash}</a></td>
                    <td>{if $import.enabled}<span class="syn-pill is-ok">{'yes'|i18n( 'extension/syndication' )}</span>{else}<span class="syn-pill is-muted">{'no'|i18n( 'extension/syndication' )}</span>{/if}</td>
                    <td class="syn-num">{$import.pending_count}</td><td class="syn-num">{$import.failed_count}</td></tr>
                {/foreach}
            </table>
        </section>
        {/if}

        {/if}
    </div>
</div>
{include uri='design:parts/syndication/script.tpl'}
