{ezcss_require( 'syndication.css' )}
<form action={concat( 'syndication/edit_source_filter/', $source_filter.id )|ezurl} method="post" name="Syndication">
<div class="context-block syn">
    <div class="box-header">
        <h1 class="context-title">{'Edit a filter of the feed "%name"'|i18n( 'extension/syndication',, hash( '%name', $source_filter.feed.name ) )|wash}</h1>
        <div class="header-mainline"></div>
    </div>
    <div class="box-content">
        <div class="block">{include uri=$filter.edit_template}</div>
    </div>
    <div class="controlbar">
        <div class="block">
            <input class="defaultbutton" type="submit" name="Store" value="{'Save'|i18n( 'extension/syndication' )|wash}" />
            <input class="button" type="submit" name="Cancel" value="{'Cancel'|i18n( 'extension/syndication' )|wash}" />
        </div>
    </div>
</div>
</form>
