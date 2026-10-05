{ezcss_require( 'syndication.css' )}
{def $base_uri = concat( 'syndication/pending_edit/', $import.id )}
<form name="pending_edit" method="post" action={$base_uri|ezurl}>
<div class="context-block syn">
    <div class="box-header">
        <h1 class="context-title">{'Items of the import "%name"'|i18n( 'extension/syndication',, hash( '%name', $import.name ) )|wash}</h1>
        <div class="header-mainline"></div>
    </div>
    <div class="box-content">

        {include uri='design:parts/syndication/notices.tpl' notices=$notices}

        <p class="syn-muted">{'Approve an item to have it imported by the next run, deny it to keep it out. Items that are installed or being installed cannot be changed.'|i18n( 'extension/syndication' )}</p>

        <p class="syn-status-facts">
            {if eq( $statusFilter, -1 )}<span class="syn-pill is-info">{'All'|i18n( 'extension/syndication' )}</span>{else}<a class="syn-pill" href={$base_uri|ezurl}>{'All'|i18n( 'extension/syndication' )}</a>{/if}
            {foreach $statusNameMap as $key => $name}
                {if eq( $statusFilter, $key )}<span class="syn-pill is-info">{$name|wash}</span>{else}<a class="syn-pill" href={concat( $base_uri, '/(statusFilter)/', $key )|ezurl}>{$name|wash}</a>{/if}
            {/foreach}
        </p>

        {if $statusList|count}
        <p><button class="button" type="button" id="syn-set-pending">{'Approve all that can be changed'|i18n( 'extension/syndication' )}</button></p>
        <table class="list syn-table">
            <tr>
                <th class="tight">{'ID'|i18n( 'extension/syndication' )}</th>
                <th>{'Name'|i18n( 'extension/syndication' )}</th>
                <th>{'Original'|i18n( 'extension/syndication' )}</th>
                <th>{'Created'|i18n( 'extension/syndication' )}</th>
                <th>{'Modified'|i18n( 'extension/syndication' )}</th>
                <th>{'Status'|i18n( 'extension/syndication' )}</th>
                <th class="tight">{'Approve'|i18n( 'extension/syndication' )}</th>
            </tr>
            {foreach $statusList as $item sequence array( 'bglight', 'bgdark' ) as $seq}
            <tr class="{$seq}">
                <td>{$item.id}<input name="StatusIDList[]" type="hidden" value="{$item.id}" /></td>
                <td class="syn-wrap">{$item.feed_item.option_array.name|wash}</td>
                <td>{if $item.feed_item.option_array.original_url}<a href="{$item.feed_item.option_array.original_url|wash}" target="_blank" rel="noopener noreferrer">{'open'|i18n( 'extension/syndication' )}</a>{/if}</td>
                <td>{if $item.feed_item.option_array.published}{$item.feed_item.option_array.published|l10n( 'shortdatetime' )}{/if}</td>
                <td>{if $item.feed_item.option_array.modified}{$item.feed_item.option_array.modified|l10n( 'shortdatetime' )}{/if}</td>
                <td><span class="syn-pill {cond( eq( $item.status, 3 ), 'is-ok', eq( $item.status, 4 ), 'is-bad', eq( $item.status, 1 ), 'is-info', eq( $item.status, 2 ), 'is-warn', 'is-muted' )}" title="{$item.option_array.error|wash}">{$statusNameMap[$item.status]|wash}</span></td>
                <td>
                {if $allowChangeFromStatusList|contains( $item.status )}
                    <select name="StatusMode_{$item.id}" data-syn-changeable="1">
                    {foreach $allowUserStatusList as $status}
                        <option value="{$status}"{if eq( $item.status, $status )} selected="selected"{/if}>{$statusNameMap[$status]|wash}</option>
                    {/foreach}
                    </select>
                {/if}
                </td>
            </tr>
            {/foreach}
        </table>
        <div class="syn-pager">
            <span>{'%count items'|i18n( 'extension/syndication',, hash( '%count', $statusListCount ) )}</span>
            {include name=navigator uri='design:navigator/google.tpl' page_uri=$base_uri view_parameters=$view_parameters item_count=$statusListCount item_limit=$view_parameters.limit}
        </div>
        {else}
        <div class="syn-empty"><p>{'No item has this status.'|i18n( 'extension/syndication' )}</p></div>
        {/if}
    </div>
    <div class="controlbar">
        <div class="block">
            <input class="defaultbutton" name="Update" type="submit" value="{'Save the changes'|i18n( 'extension/syndication' )|wash}" />
            <a class="button" href={concat( 'syndication/import_info/', $import.id )|ezurl}>{'Back to the import'|i18n( 'extension/syndication' )}</a>
        </div>
    </div>
</div>
</form>
<script>
(function () {ldelim}
    var button = document.getElementById('syn-set-pending');
    if (!button) {ldelim} return; {rdelim}
    button.addEventListener('click', function () {ldelim}
        document.querySelectorAll('select[data-syn-changeable]').forEach(function (select) {ldelim}
            for (var i = 0; i < select.options.length; i++) {ldelim}
                if (select.options[i].value === '1') {ldelim} select.selectedIndex = i; {rdelim}
            {rdelim}
        {rdelim});
    {rdelim});
{rdelim})();
</script>
