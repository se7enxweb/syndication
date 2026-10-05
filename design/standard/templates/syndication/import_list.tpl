{ezcss_require( 'syndication.css' )}
{def $qpart = cond( $vp.q, concat( '/(q)/', $view_parameters.q ), '' )
     $other_order = cond( eq( $vp.order, 'asc' ), 'desc', 'asc' )
     $list_url = concat( 'syndication/import_list', $qpart )}

<div class="context-block syn">
    <div class="box-header">
        <h1 class="context-title">{'Imports'|i18n( 'extension/syndication' )}</h1>
        <div class="header-mainline"></div>
    </div>
    <div class="box-content">

        {include uri='design:parts/syndication/notices.tpl' notices=$notices}

        <p class="syn-muted">{'An import fetches a feed of another site. New items wait here until the cronjob (or you) imports them.'|i18n( 'extension/syndication' )}</p>

        {if $confirm_list|count}
        <form class="syn-confirm" action={'syndication/import_list'|ezurl} method="post">
            <h2>{'Remove these imports?'|i18n( 'extension/syndication' )}</h2>
            <ul>
            {foreach $confirm_list as $import}
                <li>{$import.name|wash} <input type="hidden" name="RemoveImportIDArray[]" value="{$import.id}" /></li>
            {/foreach}
            </ul>
            <p>{'The imports and their filters go. The content they already imported stays. This cannot be undone.'|i18n( 'extension/syndication' )}</p>
            <input class="button syn-danger" type="submit" name="ConfirmRemoveButton" value="{'Yes, remove'|i18n( 'extension/syndication' )|wash}" />
            <a class="button" href={$list_url|ezurl}>{'Cancel'|i18n( 'extension/syndication' )}</a>
        </form>
        {/if}

        <form class="syn-filter" action={'syndication/import_list'|ezurl} method="post">
            <label>{'Find an import'|i18n( 'extension/syndication' )}
                <input type="search" name="Filter" value="{$vp.q|wash}" placeholder="{'Name or server'|i18n( 'extension/syndication' )|wash}" /></label>
            <input class="button" type="submit" name="FilterButton" value="{'Filter'|i18n( 'extension/syndication' )|wash}" />
            {if $vp.q}<a class="button" href={'syndication/import_list'|ezurl}>{'Clear'|i18n( 'extension/syndication' )}</a>{/if}
            {if $can_create}<span class="syn-spacer"><input class="defaultbutton" type="submit" name="Create" value="{'New import'|i18n( 'extension/syndication' )|wash}" /></span>{/if}
        </form>

        {if $import_list|count}
        <form action={'syndication/import_list'|ezurl} method="post">
        <table class="list syn-table">
            <tr>
                {if $can_remove}<th class="tight"><input type="checkbox" data-syn-check-all="1" title="{'Select all'|i18n( 'extension/syndication' )|wash}" /></th>{/if}
                <th><a href={concat( 'syndication/import_list', $qpart, '/(sort)/name/(order)/', cond( eq( $vp.sort, 'name' ), $other_order, 'asc' ) )|ezurl} class="{if eq( $vp.sort, 'name' )}is-sorted{if eq( $vp.order, 'desc' )} is-desc{/if}{/if}">{'Name'|i18n( 'extension/syndication' )}</a></th>
                <th><a href={concat( 'syndication/import_list', $qpart, '/(sort)/enabled/(order)/', cond( eq( $vp.sort, 'enabled' ), $other_order, 'desc' ) )|ezurl} class="{if eq( $vp.sort, 'enabled' )}is-sorted{if eq( $vp.order, 'desc' )} is-desc{/if}{/if}">{'Active'|i18n( 'extension/syndication' )}</a></th>
                <th class="syn-num">{'Installed'|i18n( 'extension/syndication' )}</th>
                <th class="syn-num">{'Installing'|i18n( 'extension/syndication' )}</th>
                <th class="syn-num">{'Pending'|i18n( 'extension/syndication' )}</th>
                <th class="syn-num">{'Failed'|i18n( 'extension/syndication' )}</th>
                <th class="syn-num">{'Without status'|i18n( 'extension/syndication' )}</th>
                <th class="tight">{'Actions'|i18n( 'extension/syndication' )}</th>
            </tr>
            {foreach $import_list as $import sequence array( 'bglight', 'bgdark' ) as $seq}
            <tr class="{$seq}">
                {if $can_remove}<td><input type="checkbox" name="RemoveImportIDArray[]" value="{$import.id}" aria-label="{'Select'|i18n( 'extension/syndication' )|wash} {$import.name|wash}" /></td>{/if}
                <td class="syn-wrap"><a href={concat( 'syndication/import_info/', $import.id )|ezurl}>{cond( $import.name, $import.name|wash, concat( '(', 'no name'|i18n( 'extension/syndication' ), ')' ) )}</a></td>
                <td>{if $import.enabled}<span class="syn-pill is-ok">{'active'|i18n( 'extension/syndication' )}</span>{else}<span class="syn-pill is-muted">{'not active'|i18n( 'extension/syndication' )}</span>{/if}</td>
                <td class="syn-num">{$import.installed_count|wash}</td>
                <td class="syn-num">{$import.installing_count|wash}</td>
                <td class="syn-num">{$import.pending_count|wash}</td>
                <td class="syn-num">{if $import.failed_count}<span class="syn-pill is-bad">{$import.failed_count|wash}</span>{else}0{/if}</td>
                <td class="syn-num">{$import.none_count|wash}</td>
                <td class="syn-actions">
                    <a class="button" href={concat( 'syndication/import_info/', $import.id )|ezurl}>{'Details'|i18n( 'extension/syndication' )}</a>
                    <a class="button" href={concat( 'syndication/pending_edit/', $import.id )|ezurl}>{'Items'|i18n( 'extension/syndication' )}</a>
                    <a class="button" href={concat( 'syndication/import_edit/', $import.id )|ezurl}>{'Edit'|i18n( 'extension/syndication' )}</a>
                </td>
            </tr>
            {/foreach}
        </table>
        {if $can_remove}<p><input class="button" type="submit" name="Remove" value="{'Remove selected'|i18n( 'extension/syndication' )|wash}" /></p>{/if}
        </form>

        <div class="syn-pager">
            <span>{'%count of %all imports'|i18n( 'extension/syndication',, hash( '%count', $total, '%all', $all_count ) )}</span>
            {include name=navigator uri='design:navigator/google.tpl' page_uri='syndication/import_list' item_count=$total view_parameters=$view_parameters item_limit=$vp.limit}
        </div>
        {else}
        <div class="syn-empty">
            {if $all_count}
            <p><strong>{'No import matches "%q".'|i18n( 'extension/syndication',, hash( '%q', $vp.q ) )|wash}</strong></p>
            <p><a href={'syndication/import_list'|ezurl}>{'Show all imports'|i18n( 'extension/syndication' )}</a></p>
            {else}
            <p><strong>{'There is no import yet.'|i18n( 'extension/syndication' )}</strong></p>
            <p>{'Create an import, enter the address of the other site and pick one of its feeds.'|i18n( 'extension/syndication' )}</p>
            {/if}
        </div>
        {/if}
    </div>
</div>
{include uri='design:parts/syndication/script.tpl'}
