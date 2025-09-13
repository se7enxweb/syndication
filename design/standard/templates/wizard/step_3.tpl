<label>{"Import Filters"|i18n("design/standard/syndication/edit")}:</label><div class="labelbreak"></div>
<table class="list" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
    <th>{"Type"|i18n("design/standard/syndication/edit")}</th>
    <th>{"Limitation"|i18n("design/standard/syndication/edit")}</th>
    <th class="tight">{"Edit Filter"|i18n("design/standard/syndication/edit")}</th>
    <th class="tight">{"Remove"|i18n("design/standard/syndication/edit")}</th>
</tr>

{section name=Filter loop=$wizard.syndication_import.filter_list sequence=array(bglight,bgdark)}
    <tr>
        <td class="{$Filter:sequence}">{$Filter:item.filter.type|wash}</td>
        <td class="{$Filter:sequence}">{$Filter:item.filter.limitation_text|wash}</td>
        <td class="{$Filter:sequence}"><div class="listbutton"><a href={concat("syndication/edit_import_filter/",$Filter:item.id)|ezurl}><img class="button" src={"edit.png"|ezimage} width="16" height="16" alt="Edit" /></a></div></td>
        <td class="{$Filter:sequence}" width="1"><input type="checkbox" name="RemoveFilterIDArray[]" value="{$Filter:item.id}"></td>
    </tr>
{/section}

<tr>
    <td colspan="3">
    <select name="FilterType">
        {section loop=$wizard.filter_array}
            <option value="{$:item.type|wash}">{$:item.name|wash}</option>
        {/section}
    </select>
    {include uri="design:gui/button.tpl" id_name="AddFilterButton" value="Add Filter"|i18n("design/standard/syndication/edit")}
    </td>
    <td align="right">
    <input type="image" name="RemoveFilterButton" value="{'Remove'|i18n('design/standard/syndication/edit')}" src={"trash.png"|ezimage} />
    </td>
</tr>
</table>
