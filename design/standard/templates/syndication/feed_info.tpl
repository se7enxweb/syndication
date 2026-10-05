{ezcss_require( 'syndication.css' )}
<div class="context-block syn">
    <div class="box-header">
        <h1 class="context-title">{'Feed'|i18n( 'extension/syndication' )}: {$feed.name|wash}</h1>
        <div class="header-mainline"></div>
    </div>
    <div class="box-content">

        {include uri='design:parts/syndication/notices.tpl' notices=$notices}

        {include uri='design:parts/syndication/job.tpl' job_id=$job_id}

        <div class="syn-status">
            <div>
                {if $feed.enabled}<span class="syn-pill is-ok">{'active'|i18n( 'extension/syndication' )}</span>{else}<span class="syn-pill is-warn">{'not active'|i18n( 'extension/syndication' )}</span>{/if}
                {if $soap_enabled}<span class="syn-pill is-ok">{'SOAP on'|i18n( 'extension/syndication' )}</span>{else}<span class="syn-pill is-warn">{'SOAP off'|i18n( 'extension/syndication' )}</span>{/if}
            </div>
            <div class="syn-status-facts">
                <span><strong>{$sources|count}</strong> {'sources'|i18n( 'extension/syndication' )}</span>
                <span><strong>{$item_count}</strong> {'exported items'|i18n( 'extension/syndication' )}</span>
                {if $last_run}<span>{'Last export run'|i18n( 'extension/syndication' )} {$last_run.time|l10n( 'shortdatetime' )}</span>{/if}
            </div>
        </div>

        <div class="syn-cards">
            <section class="syn-card">
                <h2>{'Settings'|i18n( 'extension/syndication' )}</h2>
                <dl class="syn-kv">
                    <dt>{'Identifier'|i18n( 'extension/syndication' )}</dt><dd><code>{$feed.identifier|wash}</code></dd>
                    <dt>{'Feed ID'|i18n( 'extension/syndication' )}</dt><dd>{$feed.id}</dd>
                    <dt>{'Object expiry time'|i18n( 'extension/syndication' )}</dt><dd>{$feed.object_expiry_time} {'days'|i18n( 'extension/syndication' )}</dd>
                    <dt>{'Cache expiry time'|i18n( 'extension/syndication' )}</dt><dd>{if $feed.cache_timeout}{$feed.cache_timeout} {'minutes'|i18n( 'extension/syndication' )}{else}{'cache off'|i18n( 'extension/syndication' )}{/if}</dd>
                    <dt>{'Cronjob cache'|i18n( 'extension/syndication' )}</dt><dd>{if $feed.force_cronjob_cache}{'forced'|i18n( 'extension/syndication' )}{else}{'on request'|i18n( 'extension/syndication' )}{/if}</dd>
                    <dt>{'Description'|i18n( 'extension/syndication' )}</dt><dd>{if $feed.public_comment}{$feed.public_comment|wash|nl2br}{else}<span class="syn-muted">-</span>{/if}</dd>
                </dl>
            </section>
            <section class="syn-card">
                <h2>{'How another site subscribes'|i18n( 'extension/syndication' )}</h2>
                <p>{'On the other site, create an import with this server address and pick this feed:'|i18n( 'extension/syndication' )}</p>
                <p><code>{$soap_url|wash}</code></p>
                <p class="syn-hint">{'Protect the address with HTTP access control and give the importing site a login.'|i18n( 'extension/syndication' )}</p>
            </section>
            <section class="syn-card">
                <h2>{'Actions'|i18n( 'extension/syndication' )}</h2>
                {if $can_edit}
                <form action={concat( 'syndication/feed_info/', $feed.id )|ezurl} method="post" data-syn-confirm="{'Write the export cache of this feed now? This can take a while on a large tree.'|i18n( 'extension/syndication' )|wash}">
                    <input class="defaultbutton" type="submit" name="ExportNowButton" value="{'Export now'|i18n( 'extension/syndication' )|wash}"{if $sources|count|eq( 0 )} disabled="disabled" title="{'The feed has no source'|i18n( 'extension/syndication' )|wash}"{/if} />
                </form>
                <div class="syn-links">
                    <a class="button" href={concat( 'syndication/edit/', $feed.id )|ezurl}>{'Edit the feed'|i18n( 'extension/syndication' )}</a>
                </div>
                {else}<p class="syn-muted">{'You may look at this feed but not change it.'|i18n( 'extension/syndication' )}</p>{/if}
                <div class="syn-links"><a class="button" href={'syndication/list'|ezurl}>&laquo; {'All feeds'|i18n( 'extension/syndication' )}</a></div>
            </section>
        </div>

        <section>
            <h2>{'Sources'|i18n( 'extension/syndication' )}</h2>
            {if $sources|count}
            <table class="list syn-table">
                <tr><th>{'Name'|i18n( 'extension/syndication' )}</th><th>{'Type'|i18n( 'extension/syndication' )}</th><th>{'Location'|i18n( 'extension/syndication' )}</th><th>{'Filters'|i18n( 'extension/syndication' )}</th></tr>
                {foreach $sources as $row sequence array( 'bglight', 'bgdark' ) as $seq}
                <tr class="{$seq}">
                    <td>{if $row.node}<a href={$row.node.url_alias|ezurl}>{$row.node.name|wash}</a>{else}<span class="syn-pill is-bad">{'node %id is missing'|i18n( 'extension/syndication',, hash( '%id', $row.source.node_id ) )}</span>{/if}</td>
                    <td>{$row.source.type_string|wash}</td>
                    <td class="syn-wrap">{if $row.node}{$row.node.path_identification_string|wash}{/if}</td>
                    <td>{if $row.filters|count}{foreach $row.filters as $filter}<span class="syn-pill is-info">{$filter.name|wash}{if $filter.limitation}: {$filter.limitation|wash}{/if}</span> {/foreach}{else}<span class="syn-muted">{'all objects'|i18n( 'extension/syndication' )}</span>{/if}</td>
                </tr>
                {/foreach}
            </table>
            {else}
            <div class="syn-empty"><p><strong>{'This feed has no source.'|i18n( 'extension/syndication' )}</strong></p><p>{'Edit the feed and add the node or subtree it exports.'|i18n( 'extension/syndication' )}</p></div>
            {/if}
        </section>

        <section>
            <h2>{'Exported items'|i18n( 'extension/syndication' )}</h2>
            {if $export_items|count}
            <table class="list syn-table">
                <tr><th>{'Object'|i18n( 'extension/syndication' )}</th><th>{'Remote ID'|i18n( 'extension/syndication' )}</th><th class="syn-num">{'Version'|i18n( 'extension/syndication' )}</th><th class="syn-num">{'Depth'|i18n( 'extension/syndication' )}</th><th>{'Changed'|i18n( 'extension/syndication' )}</th></tr>
                {foreach $export_items as $item sequence array( 'bglight', 'bgdark' ) as $seq}
                <tr class="{$seq}">
                    <td>{if $item.node_url}<a href={$item.node_url|ezurl}>{$item.name|wash}</a>{elseif $item.name}{$item.name|wash}{else}<span class="syn-muted">{'object no longer on this site'|i18n( 'extension/syndication' )}</span>{/if}</td>
                    <td><code>{$item.remote_id|wash}</code></td>
                    <td class="syn-num">{$item.contentobject_version}</td>
                    <td class="syn-num">{$item.depth}</td>
                    <td>{$item.modified|l10n( 'shortdatetime' )}</td>
                </tr>
                {/foreach}
            </table>
            <div class="syn-pager">
                <span>{'%count items'|i18n( 'extension/syndication',, hash( '%count', $item_count ) )}</span>
                {include name=navigator uri='design:navigator/google.tpl' page_uri=concat( 'syndication/feed_info/', $feed.id ) item_count=$item_count view_parameters=$view_parameters item_limit=$limit}
            </div>
            {else}
            <div class="syn-empty"><p>{'Nothing exported yet. The cronjob part export_feed (or "Export now") writes the items.'|i18n( 'extension/syndication' )}</p></div>
            {/if}
        </section>
    </div>
</div>
{include uri='design:parts/syndication/script.tpl'}
