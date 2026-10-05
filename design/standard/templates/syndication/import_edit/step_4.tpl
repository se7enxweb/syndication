{def $imp = $wizard.syndication_import}
<h2>{'Import options'|i18n( 'extension/syndication' )}</h2>

<div class="block">
    <label>{'Location for the imported content'|i18n( 'extension/syndication' )}</label>
    {if $imp.placement_node}<strong>{$imp.placement_node.name|wash}</strong>{else}<span class="syn-pill is-warn">{'not chosen'|i18n( 'extension/syndication' )}</span>{/if}
    <input class="button" type="submit" name="BrowseNodeLocation" value="{'Browse location'|i18n( 'extension/syndication' )|wash}" formnovalidate="formnovalidate" />
</div>
<div class="block">
    <label>{'Location for related objects (images, files and so on)'|i18n( 'extension/syndication' )}</label>
    {if $imp.related_node}<strong>{$imp.related_node.name|wash}</strong>{else}<span class="syn-muted">{'not chosen'|i18n( 'extension/syndication' )}</span>{/if}
    <input class="button" type="submit" name="BrowseRelatedLocation" value="{'Browse location'|i18n( 'extension/syndication' )|wash}" formnovalidate="formnovalidate" />
</div>
<div class="block">
    <label><input type="checkbox" name="Active" value="1"{if $imp.enabled} checked="checked"{/if} /> {'Active: the cronjob fetches this import'|i18n( 'extension/syndication' )}</label>
</div>
<div class="block">
    <label><input type="checkbox" name="AutomaticImport"{cond( $imp.option_array.auto_import, ' checked="checked"', '' )} /> {'Import the objects automatically (otherwise they wait for approval)'|i18n( 'extension/syndication' )}</label>
</div>
<div class="block">
    <label><input type="checkbox" name="ExcludeTopNode"{cond( $imp.option_array.exclude_top_node, ' checked="checked"', '' )} /> {'Exclude the top node'|i18n( 'extension/syndication' )}</label>
</div>
<div class="block">
    <label><input type="checkbox" name="IncludeRelatedObjects"{cond( $imp.option_array.include_related_objects, ' checked="checked"', '' )} /> {'Import related objects too'|i18n( 'extension/syndication' )}</label>
</div>
<div class="block">
    <label><input type="checkbox" name="UseHiddenStatus"{cond( $imp.option_array.use_hidden_status, ' checked="checked"', '' )} /> {'Take over the hidden status of the exporting site'|i18n( 'extension/syndication' )}</label>
</div>
