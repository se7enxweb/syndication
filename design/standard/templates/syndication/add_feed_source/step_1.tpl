{ezcss_require( 'syndication.css' )}
<form action={concat( 'syndication/add_feed_source/', $feed_id, '/', $next_step )|ezurl} method="post" name="Syndication">
<div class="context-block syn">
    <div class="box-header">
        <h1 class="context-title">{'Add a source to the feed "%name"'|i18n( 'extension/syndication',, hash( '%name', $syndication_feed.name ) )|wash}</h1>
        <div class="header-mainline"></div>
    </div>
    <div class="box-content">
        {include uri='design:syndication/add_feed_source/add_feed_source_steps.tpl' step=1}
        <div class="block">
            <label>{'What does the source export?'|i18n( 'extension/syndication' )}</label>
            <label><input type="radio" name="SourceType" checked="checked" value="tree" /> {'A subtree: the node and everything below it'|i18n( 'extension/syndication' )}</label><br />
            <label><input type="radio" name="SourceType" value="node" /> {'A single node'|i18n( 'extension/syndication' )}</label>
        </div>
    </div>
    <div class="controlbar">
        <div class="block">
            <input class="defaultbutton" type="submit" name="NextStepButton" value="{'Next'|i18n( 'extension/syndication' )|wash}" />
            <a class="button" href={concat( 'syndication/edit/', $feed_id )|ezurl}>{'Back to the feed'|i18n( 'extension/syndication' )}</a>
        </div>
    </div>
</div>
</form>
