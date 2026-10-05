{ezcss_require( 'syndication.css' )}
<div class="context-block syn">
    <div class="box-header">
        <h1 class="context-title">{'Import'|i18n( 'extension/syndication' )}: {$import.name|wash}</h1>
        <div class="header-mainline"></div>
    </div>
    <div class="box-content">

        {include uri='design:parts/syndication/notices.tpl' notices=$notices}

        {include uri='design:parts/syndication/job.tpl' job_id=$job_id}

        <div class="syn-status">
            <div>
                {if $import.enabled}<span class="syn-pill is-ok">{'active'|i18n( 'extension/syndication' )}</span>{else}<span class="syn-pill is-warn">{'not active'|i18n( 'extension/syndication' )}</span>{/if}
                {if $import.option_array.auto_import}<span class="syn-pill is-info">{'imports automatically'|i18n( 'extension/syndication' )}</span>{else}<span class="syn-pill is-muted">{'items wait for approval'|i18n( 'extension/syndication' )}</span>{/if}
            </div>
            <div class="syn-status-facts">
                <span><strong>{$import.installed_count}</strong> {'installed'|i18n( 'extension/syndication' )}</span>
                <span><strong>{sum( $import.pending_count, $import.installing_count )}</strong> {'waiting'|i18n( 'extension/syndication' )}</span>
                <span><strong>{$import.failed_count}</strong> {'failed'|i18n( 'extension/syndication' )}</span>
                {if $last_run}<span>{'Last import run'|i18n( 'extension/syndication' )} {$last_run.time|l10n( 'shortdatetime' )}</span>{/if}
            </div>
        </div>

        <div class="syn-cards">
            <section class="syn-card">
                <h2>{'Settings'|i18n( 'extension/syndication' )}</h2>
                <dl class="syn-kv">
                    <dt>{'Server'|i18n( 'extension/syndication' )}</dt><dd><code>{$server|wash}</code></dd>
                    <dt>{'Login'|i18n( 'extension/syndication' )}</dt><dd>{if $has_login}{$import.option_array.login|wash} (<span class="syn-muted">{'password is stored, not shown'|i18n( 'extension/syndication' )}</span>){else}<span class="syn-muted">{'none'|i18n( 'extension/syndication' )}</span>{/if}</dd>
                    <dt>{'Feed ID on the server'|i18n( 'extension/syndication' )}</dt><dd>{if $import.feed_id}{$import.feed_id}{else}<span class="syn-pill is-warn">{'not chosen'|i18n( 'extension/syndication' )}</span>{/if}</dd>
                    <dt>{'Location for content'|i18n( 'extension/syndication' )}</dt><dd>{if $import.placement_node}<a href={$import.placement_node.url_alias|ezurl}>{$import.placement_node.name|wash}</a>{else}<span class="syn-pill is-warn">{'not chosen'|i18n( 'extension/syndication' )}</span>{/if}</dd>
                    <dt>{'Original placement'|i18n( 'extension/syndication' )}</dt><dd>{if $import.option_array.original_placement}{'yes'|i18n( 'extension/syndication' )}{else}{'no'|i18n( 'extension/syndication' )}{/if}</dd>
                    <dt>{'Related objects'|i18n( 'extension/syndication' )}</dt><dd>{if $import.option_array.include_related_objects}{'imported too'|i18n( 'extension/syndication' )}{else}{'not imported'|i18n( 'extension/syndication' )}{/if}</dd>
                </dl>
            </section>
            <section class="syn-card">
                <h2>{'Actions'|i18n( 'extension/syndication' )}</h2>
                {if $can_edit}
                <form action={concat( 'syndication/import_info/', $import.id )|ezurl} method="post">
                    <input class="button" type="submit" name="FetchButton" value="{'Fetch the item list now'|i18n( 'extension/syndication' )|wash}" />
                </form>
                <form action={concat( 'syndication/import_info/', $import.id )|ezurl} method="post" data-syn-confirm="{'Import up to 10 pending or failed items now? New content objects are created.'|i18n( 'extension/syndication' )|wash}">
                    <input class="defaultbutton" type="submit" name="ImportButton" value="{'Import pending items now'|i18n( 'extension/syndication' )|wash}"{if sum( $import.pending_count, $import.failed_count )|eq( 0 )} disabled="disabled"{/if} />
                </form>
                <div class="syn-links">
                    <a class="button" href={concat( 'syndication/import_edit/', $import.id )|ezurl}>{'Edit the import'|i18n( 'extension/syndication' )}</a>
                    <a class="button" href={concat( 'syndication/pending_edit/', $import.id )|ezurl}>{'Edit waiting items'|i18n( 'extension/syndication' )}</a>
                </div>
                {else}<p class="syn-muted">{'You may look at this import but not change it.'|i18n( 'extension/syndication' )}</p>{/if}
                <div class="syn-links"><a class="button" href={'syndication/import_list'|ezurl}>&laquo; {'All imports'|i18n( 'extension/syndication' )}</a></div>
            </section>
        </div>

        <section>
            <h2>{'Items of the feed'|i18n( 'extension/syndication' )}</h2>
            {if $status_rows|count}
            <table class="list syn-table">
                <tr><th>{'Object'|i18n( 'extension/syndication' )}</th><th>{'Remote ID'|i18n( 'extension/syndication' )}</th><th>{'Changed'|i18n( 'extension/syndication' )}</th><th>{'Status'|i18n( 'extension/syndication' )}</th></tr>
                {foreach $status_rows as $row sequence array( 'bglight', 'bgdark' ) as $seq}
                <tr class="{$seq}">
                    <td>{if $row.name}{$row.name|wash}{else}<span class="syn-muted">{'not imported'|i18n( 'extension/syndication' )}</span>{/if}</td>
                    <td><code>{$row.remote_id|wash}</code></td>
                    <td>{$row.modified|l10n( 'shortdatetime' )}</td>
                    <td><span class="syn-pill {cond( eq( $row.status, 3 ), 'is-ok', eq( $row.status, 4 ), 'is-bad', eq( $row.status, 1 ), 'is-info', eq( $row.status, 2 ), 'is-warn', 'is-muted' )}">{$row.status_name|wash}</span></td>
                </tr>
                {/foreach}
            </table>
            <div class="syn-pager">
                <span>{'%count items'|i18n( 'extension/syndication',, hash( '%count', $item_count ) )}</span>
                {include name=navigator uri='design:navigator/google.tpl' page_uri=concat( 'syndication/import_info/', $import.id ) item_count=$item_count view_parameters=$view_parameters item_limit=$limit}
            </div>
            {else}
            <div class="syn-empty"><p>{'No item fetched yet. Use "Fetch the item list now" or wait for the cronjob part import_feed.'|i18n( 'extension/syndication' )}</p></div>
            {/if}
        </section>
    </div>
</div>
{include uri='design:parts/syndication/script.tpl'}
