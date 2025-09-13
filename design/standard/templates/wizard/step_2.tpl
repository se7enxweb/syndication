<h2>{"Please select import feed"|i18n("design/standard/syndication/edit")}</h2>

<select name="FeedID">
    {foreach $wizard.feed_list.feed_item as $feedItem}
        <option value={$feedItem.feed_id}>{$feedItem.name|wash}</option>
    {/foreach}
</select>