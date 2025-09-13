{let syndication_import=$wizard.syndication_import}

<label>{"Name"|i18n("design/standard/syndication/edit")}:</label><div class="labelbreak"></div>
{include uri="design:gui/lineedit.tpl" id_name=Name value=$syndication_import.name|wash}
<br/>

<label>{"Server ( example : http://ez.no/soap.php )"|i18n("design/standard/syndication/edit")}:</label><div class="labelbreak"></div>
{include uri="design:gui/lineedit.tpl" id_name=Server value=$syndication_import.server|wash}
<br/>

{/let}