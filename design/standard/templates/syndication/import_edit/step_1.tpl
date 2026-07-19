{let syndication_import=$wizard.syndication_import}

<label>{"Name"|i18n("design/standard/syndication/edit")}:</label><div class="labelbreak"></div>
{include uri="design:gui/lineedit.tpl" id_name=Name value=$syndication_import.name|wash}
<br/>

<label>{"Server ( example : http://ez.no/soap.php )"|i18n("design/standard/syndication/edit")}:</label><div class="labelbreak"></div>
{include uri="design:gui/lineedit.tpl" id_name=Server value=$syndication_import.server|wash}
<br/>

<label>{"Login"|i18n("design/standard/syndication/edit")}:</label><div class="labelbreak"></div>
{include uri="design:gui/lineedit.tpl" id_name=Login value=$syndication_import.option_array.login|wash}
<br/>

<label>{"Password"|i18n("design/standard/syndication/edit")}:</label><div class="labelbreak"></div>
<input class="box" type="password" name="Password" size="45" value="{$syndication_import.option_array.password|wash}" />
<br/>

{/let}