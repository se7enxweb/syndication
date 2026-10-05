{ezcss_require( 'syndication.css' )}
<form action={concat( 'syndication/list_source_filter/', $feed_source.id )|ezurl} method="post" name="Syndication">
<div class="context-block syn">
    <div class="box-header">
        <h1 class="context-title">{'Filters of the source of "%name"'|i18n( 'extension/syndication',, hash( '%name', $feed_source.feed.name ) )|wash}</h1>
        <div class="header-mainline"></div>
    </div>
    <div class="box-content">
        {include uri='design:syndication/add_feed_source/add_feed_source_steps.tpl' step=3}
        <p class="syn-muted">{'Filters limit the objects of the source that go into the feed. Without a filter every object of the source is exported. With several filters, only objects that match all of them are exported.'|i18n( 'extension/syndication' )}</p>

        {if $feed_source.filter_list|count}
        <table class="list syn-table">
            <tr>
                <th>{'Type'|i18n( 'extension/syndication' )}</th>
                <th>{'Limitation'|i18n( 'extension/syndication' )}</th>
                <th class="tight">{'Edit'|i18n( 'extension/syndication' )}</th>
                <th class="tight">{'Remove'|i18n( 'extension/syndication' )}</th>
            </tr>
            {foreach $feed_source.filter_list as $filter sequence array( 'bglight', 'bgdark' ) as $seq}
            <tr class="{$seq}">
                <td>{$filter.filter.type|wash}</td>
                <td>{$filter.filter.limitation_text|wash}</td>
                <td><a class="button" href={concat( 'syndication/edit_source_filter/', $filter.id )|ezurl}>{'Edit'|i18n( 'extension/syndication' )}</a></td>
                <td><input type="checkbox" name="RemoveFilterIDArray[]" value="{$filter.id}" aria-label="{'Remove'|i18n( 'extension/syndication' )|wash}" /></td>
            </tr>
            {/foreach}
        </table>
        {else}
        <div class="syn-empty"><p>{'No filter: every object of the source is exported.'|i18n( 'extension/syndication' )}</p></div>
        {/if}
        <p>
            <select name="FilterType" aria-label="{'Filter type'|i18n( 'extension/syndication' )|wash}">
                {foreach $filter_array as $filterType}
                <option value="{$filterType.type|wash}">{$filterType.name|wash}</option>
                {/foreach}
            </select>
            <input class="button" type="submit" name="AddFilter" value="{'New filter'|i18n( 'extension/syndication' )|wash}" />
            {if $feed_source.filter_list|count}<input class="button" type="submit" name="RemoveFilter" value="{'Remove selected filters'|i18n( 'extension/syndication' )|wash}" data-syn-confirm-button="{'Remove the selected filters?'|i18n( 'extension/syndication' )|wash}" />{/if}
        </p>
    </div>
    <div class="controlbar">
        <div class="block">
            <input class="defaultbutton" type="submit" name="Finnish" value="{'Done'|i18n( 'extension/syndication' )|wash}" />
        </div>
    </div>
</div>
</form>
{include uri='design:parts/syndication/script.tpl'}
