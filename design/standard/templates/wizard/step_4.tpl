<h2>{"Select syndication import options"|i18n("design/standard/syndication/edit")}.</h2>

{* Select main placement *}
<div class="block">
    <div class="element">
       {'Select import placement'|i18n( 'design/standard/syndication/edit' )}
    </div>
    <div class="element">
        <strong>{$syndication_import.placement_node.name|wash}</strong>
    </div>
    <div class="element">
        <input type="Submit" name="BrowseNodeLocation" value="{'Browse location'|i18n( 'design/standard/syndication/edit' )}" />
    </div>
</div>

{* Select related placement *}
<div class="block">
    <div class="element">
       {'Select placement for related objects ( article images, etc )'|i18n( 'design/standard/syndication/edit' )}
    </div>
    <div class="element">
        <strong>{$syndication_import.related_node.name|wash}</strong>
    </div>
    <div class="element">
        <input type="Submit" name="BrowseRelatedLocation" value="{'Browse location'|i18n( 'design/standard/syndication/edit' )}" />
    </div>
</div>

{* Automatic import *}
<div class="block">
    <div class="element">
        {'Import of objects should be done automaticly'|i18n( 'design/standard/syndication/edit' )}:
    </div>
    <div class="element">
        <label><input type="checkbox" name="AutomaticImport" {cond( $syndication_import.option_array.auto_import, 'checked="checked"', '' )} />
            {'Automatic import.'|i18n( 'design/standard/syndication/edit' )}</label>
    </div>
</div>

{* Exclude top node *}
<div class="block">
    <div class="element">
        {'Exclude top node'|i18n( 'design/standard/syndication/edit' )}:
    </div>
    <div class="element">
        <label><input type="checkbox" name="ExcludeTopNode" {cond( $syndication_import.option_array.exclude_top_node, 'checked="checked"', '' )} />
            {'Exclude top node.'|i18n( 'design/standard/syndication/edit' )}</label>
    </div>
</div>

{* Include related objects *}
<div class="block">
    <div class="element">
        {'Related objects should be automaticly imported'|i18n( 'design/standard/syndication/edit' )}:
    </div>
    <div class="element">
        <label><input type="checkbox" name="IncludeRelatedObjects" {cond( $syndication_import.option_array.include_related_objects, 'checked="checked"', '' )} />
            {'Include related objects.'|i18n( 'design/standard/syndication/edit' )}</label>
    </div>
</div>

{* Use remote hidden settings *}
<div class="block">
    <div class="element">
        {'Set if hidden status should be imported from content feed'|i18n( 'design/standard/syndication/edit' )}:
    </div>
    <div class="element">
        <label><input type="checkbox" name="UseHiddenStatus" {cond( $syndication_import.option_array.use_hidden_status, 'checked="checked"', '' )} />
            {'Use hidden status.'|i18n( 'design/standard/syndication/edit' )}</label>
    </div>
</div>
