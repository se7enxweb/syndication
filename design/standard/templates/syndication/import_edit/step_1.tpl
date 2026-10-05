{def $imp = $wizard.syndication_import}
<p class="syn-muted">{'Enter the address of the SOAP server of the other site and, when it is protected, a login. The next step lists the feeds that site offers.'|i18n( 'extension/syndication' )}</p>

<div class="block">
    <label for="syn-import-name">{'Name'|i18n( 'extension/syndication' )}</label>
    <input id="syn-import-name" class="halfbox" type="text" name="Name" value="{$imp.name|wash}" maxlength="255" />
</div>
<div class="block">
    <label for="syn-import-server">{'Server (example: https://example.com/soap.php)'|i18n( 'extension/syndication' )}</label>
    <input id="syn-import-server" class="halfbox" type="text" name="Server" value="{$imp.server|wash}" spellcheck="false" autocomplete="off" />
    <p class="syn-hint">{'A user name and password inside the address are not shown again; use the fields below instead.'|i18n( 'extension/syndication' )}</p>
</div>
<div class="block">
    <label for="syn-import-login">{'Login'|i18n( 'extension/syndication' )}</label>
    <input id="syn-import-login" class="halfbox" type="text" name="Login" value="{$imp.option_array.login|wash}" autocomplete="off" />
</div>
<div class="block">
    <label for="syn-import-password">{'Password'|i18n( 'extension/syndication' )}</label>
    <input id="syn-import-password" class="halfbox" type="password" name="Password" value="" autocomplete="new-password" />
    <p class="syn-hint">{if $imp.option_array.password}{'A password is stored. Leave the field empty to keep it.'|i18n( 'extension/syndication' )}{else}{'Optional.'|i18n( 'extension/syndication' )}{/if}</p>
</div>
