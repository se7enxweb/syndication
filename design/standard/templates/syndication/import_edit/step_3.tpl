<h2>{'Import filters'|i18n( 'extension/syndication' )}</h2>
<p class="syn-muted">{'Without a filter every object of the feed is imported. With filters, an object is imported when one of them accepts it.'|i18n( 'extension/syndication' )}</p>
{if $wizard.syndication_import.filter_list|count}
<table class="list syn-table">
    <tr>
        <th>{'Type'|i18n( 'extension/syndication' )}</th>
        <th>{'Limitation'|i18n( 'extension/syndication' )}</th>
        <th class="tight">{'Edit'|i18n( 'extension/syndication' )}</th>
        <th class="tight">{'Remove'|i18n( 'extension/syndication' )}</th>
    </tr>
    {foreach $wizard.syndication_import.filter_list as $filter sequence array( 'bglight', 'bgdark' ) as $seq}
    <tr class="{$seq}">
        <td>{$filter.filter.type|wash}</td>
        <td>{$filter.filter.limitation_text|wash}</td>
        <td><a class="button" href={concat( 'syndication/edit_import_filter/', $filter.id )|ezurl}>{'Edit'|i18n( 'extension/syndication' )}</a></td>
        <td><input type="checkbox" name="RemoveFilterIDArray[]" value="{$filter.id}" aria-label="{'Remove'|i18n( 'extension/syndication' )|wash}" /></td>
    </tr>
    {/foreach}
</table>
{else}
<div class="syn-empty"><p>{'No filter: every object of the feed is imported.'|i18n( 'extension/syndication' )}</p></div>
{/if}
<p>
    <select name="FilterType" aria-label="{'Filter type'|i18n( 'extension/syndication' )|wash}">
        {foreach $wizard.filter_array as $filterType}
        <option value="{$filterType.type|wash}">{$filterType.name|wash}</option>
        {/foreach}
    </select>
    <input class="button" type="submit" name="AddFilterButton" value="{'Add filter'|i18n( 'extension/syndication' )|wash}" formnovalidate="formnovalidate" />
    {if $wizard.syndication_import.filter_list|count}<input class="button" type="submit" name="RemoveFilterButton" value="{'Remove selected filters'|i18n( 'extension/syndication' )|wash}" formnovalidate="formnovalidate" />{/if}
</p>
