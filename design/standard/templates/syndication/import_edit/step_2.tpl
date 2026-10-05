{def $imp = $wizard.syndication_import
     $feeds = first_set( $wizard.feed_list.feed_item, array() )}
<h2>{'Choose the feed to import'|i18n( 'extension/syndication' )}</h2>
{if $feeds|count}
<div class="block">
    <label for="syn-import-feed">{'Feed'|i18n( 'extension/syndication' )}</label>
    <select id="syn-import-feed" name="FeedID">
        {foreach $feeds as $feedItem}
        <option value="{$feedItem.feed_id|wash}"{if eq( $feedItem.feed_id, $imp.feed_id )} selected="selected"{/if}>{$feedItem.name|wash}</option>
        {/foreach}
    </select>
</div>
{else}
<div class="syn-empty"><p>{'The other site offers no feed. Make sure a feed is active there, then go back and try again.'|i18n( 'extension/syndication' )}</p></div>
{/if}
