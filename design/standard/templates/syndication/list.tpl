{ezcss_require( 'syndication.css' )}
{def $qpart = cond( $vp.q, concat( '/(q)/', $view_parameters.q ), '' )
     $other_order = cond( eq( $vp.order, 'asc' ), 'desc', 'asc' )
     $list_url = concat( 'syndication/list', $qpart )}

<div class="context-block syn">
    <div class="box-header">
        <h1 class="context-title">{'Feeds'|i18n( 'extension/syndication' )}</h1>
        <div class="header-mainline"></div>
    </div>
    <div class="box-content">

        {include uri='design:parts/syndication/notices.tpl' notices=$notices}

        <p class="syn-muted">{'A feed exports parts of the content tree. Other sites fetch it over SOAP. Open a feed to see its sources, its items and to export it now.'|i18n( 'extension/syndication' )}</p>

        {if $confirm_list|count}
        <form class="syn-confirm" action={'syndication/list'|ezurl} method="post">
            <h2>{'Remove these feeds?'|i18n( 'extension/syndication' )}</h2>
            <ul>
            {foreach $confirm_list as $feed}
                <li>{$feed.name|wash} <input type="hidden" name="RemoveFeedIDArray[]" value="{$feed.id}" /></li>
            {/foreach}
            </ul>
            <p>{'The feeds and their sources go. Content on this site and on the importing sites stays. This cannot be undone.'|i18n( 'extension/syndication' )}</p>
            <input class="button syn-danger" type="submit" name="ConfirmRemoveButton" value="{'Yes, remove'|i18n( 'extension/syndication' )|wash}" />
            <a class="button" href={$list_url|ezurl}>{'Cancel'|i18n( 'extension/syndication' )}</a>
        </form>
        {/if}

        <form class="syn-filter" action={'syndication/list'|ezurl} method="post">
            <label>{'Find a feed'|i18n( 'extension/syndication' )}
                <input type="search" name="Filter" value="{$vp.q|wash}" placeholder="{'Name, identifier or description'|i18n( 'extension/syndication' )|wash}" /></label>
            <input class="button" type="submit" name="FilterButton" value="{'Filter'|i18n( 'extension/syndication' )|wash}" />
            {if $vp.q}<a class="button" href={'syndication/list'|ezurl}>{'Clear'|i18n( 'extension/syndication' )}</a>{/if}
            {if $can_create}<span class="syn-spacer"><input class="defaultbutton" type="submit" name="CreateButton" value="{'New feed'|i18n( 'extension/syndication' )|wash}" /></span>{/if}
        </form>

        {if $feed_list|count}
        <form action={'syndication/list'|ezurl} method="post">
        <table class="list syn-table">
            <tr>
                {if $can_remove}<th class="tight"><input type="checkbox" data-syn-check-all="1" title="{'Select all'|i18n( 'extension/syndication' )|wash}" /></th>{/if}
                <th><a href={concat( 'syndication/list', $qpart, '/(sort)/name/(order)/', cond( eq( $vp.sort, 'name' ), $other_order, 'asc' ) )|ezurl} class="{if eq( $vp.sort, 'name' )}is-sorted{if eq( $vp.order, 'desc' )} is-desc{/if}{/if}">{'Name'|i18n( 'extension/syndication' )}</a></th>
                <th>{'Identifier'|i18n( 'extension/syndication' )}</th>
                <th><a href={concat( 'syndication/list', $qpart, '/(sort)/enabled/(order)/', cond( eq( $vp.sort, 'enabled' ), $other_order, 'desc' ) )|ezurl} class="{if eq( $vp.sort, 'enabled' )}is-sorted{if eq( $vp.order, 'desc' )} is-desc{/if}{/if}">{'Active'|i18n( 'extension/syndication' )}</a></th>
                <th class="syn-num">{'Sources'|i18n( 'extension/syndication' )}</th>
                <th><a href={concat( 'syndication/list', $qpart, '/(sort)/id/(order)/', cond( eq( $vp.sort, 'id' ), $other_order, 'asc' ) )|ezurl} class="{if eq( $vp.sort, 'id' )}is-sorted{if eq( $vp.order, 'desc' )} is-desc{/if}{/if}">{'ID'|i18n( 'extension/syndication' )}</a></th>
                <th class="tight">{'Actions'|i18n( 'extension/syndication' )}</th>
            </tr>
            {foreach $feed_list as $feed sequence array( 'bglight', 'bgdark' ) as $seq}
            <tr class="{$seq}">
                {if $can_remove}<td><input type="checkbox" name="RemoveFeedIDArray[]" value="{$feed.id}" aria-label="{'Select'|i18n( 'extension/syndication' )|wash} {$feed.name|wash}" /></td>{/if}
                <td class="syn-wrap"><a href={concat( 'syndication/feed_info/', $feed.id )|ezurl}>{cond( $feed.name, $feed.name|wash, concat( '(', 'no name'|i18n( 'extension/syndication' ), ')' ) )}</a></td>
                <td><code>{$feed.identifier|wash}</code></td>
                <td>{if $feed.enabled}<span class="syn-pill is-ok">{'active'|i18n( 'extension/syndication' )}</span>{else}<span class="syn-pill is-muted">{'not active'|i18n( 'extension/syndication' )}</span>{/if}</td>
                <td class="syn-num">{$feed.source_list|count}</td>
                <td>{$feed.id}</td>
                <td class="syn-actions">
                    <a class="button" href={concat( 'syndication/feed_info/', $feed.id )|ezurl}>{'Details'|i18n( 'extension/syndication' )}</a>
                    {if $can_edit}<a class="button" href={concat( 'syndication/edit/', $feed.id )|ezurl}>{'Edit'|i18n( 'extension/syndication' )}</a>{/if}
                </td>
            </tr>
            {/foreach}
        </table>
        {if $can_remove}<p><input class="button" type="submit" name="RemoveButton" value="{'Remove selected'|i18n( 'extension/syndication' )|wash}" /></p>{/if}
        </form>

        <div class="syn-pager">
            <span>{'%count of %all feeds'|i18n( 'extension/syndication',, hash( '%count', $total, '%all', $all_count ) )}</span>
            {include name=navigator uri='design:navigator/google.tpl' page_uri='syndication/list' item_count=$total view_parameters=$view_parameters item_limit=$vp.limit}
        </div>
        {else}
        <div class="syn-empty">
            {if $all_count}
            <p><strong>{'No feed matches "%q".'|i18n( 'extension/syndication',, hash( '%q', $vp.q ) )|wash}</strong></p>
            <p><a href={'syndication/list'|ezurl}>{'Show all feeds'|i18n( 'extension/syndication' )}</a></p>
            {else}
            <p><strong>{'There is no feed yet.'|i18n( 'extension/syndication' )}</strong></p>
            <p>{'Create a feed, add the part of the content tree it exports, and switch it on.'|i18n( 'extension/syndication' )}</p>
            {/if}
        </div>
        {/if}
    </div>
</div>
{include uri='design:parts/syndication/script.tpl'}
