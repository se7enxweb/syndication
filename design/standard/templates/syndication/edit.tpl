{ezcss_require( 'syndication.css' )}
<form action={concat( 'syndication/edit/', $syndication_feed.id )|ezurl} method="post" name="Syndication">
<input type="submit" name="Store" value="{'Save'|i18n( 'extension/syndication' )|wash}" style="position:absolute;left:-9999px" tabindex="-1" aria-hidden="true" />

<div class="context-block syn">
    <div class="box-header">
        <h1 class="context-title">{if $syndication_feed.name}{'Edit feed: %name'|i18n( 'extension/syndication',, hash( '%name', $syndication_feed.name ) )|wash}{else}{'New feed'|i18n( 'extension/syndication' )}{/if}</h1>
        <div class="header-mainline"></div>
    </div>
    <div class="box-content">

        {include uri='design:parts/syndication/notices.tpl' notices=$notices}
        {if $errors|count}
        <div class="message-error" role="alert"><h2>{'The feed was not saved. Look at the marked fields.'|i18n( 'extension/syndication' )}</h2></div>
        {/if}

        <div class="block{if is_set( $errors.Name )} syn-field-error{/if}">
            <label for="syn-name">{'Name'|i18n( 'extension/syndication' )}</label>
            <input id="syn-name" class="halfbox" type="text" name="Name" value="{$syndication_feed.name|wash}" maxlength="255" required="required" />
            {if is_set( $errors.Name )}<p class="syn-form-error">{$errors.Name|wash}</p>{/if}
        </div>

        <div class="block{if is_set( $errors.Identifier )} syn-field-error{/if}">
            <label for="syn-identifier">{'Identifier'|i18n( 'extension/syndication' )}</label>
            <input id="syn-identifier" class="halfbox" type="text" name="Identifier" value="{$syndication_feed.identifier|wash}" maxlength="255" />
            <p class="syn-hint">{'A short name without spaces, for example "news". Optional.'|i18n( 'extension/syndication' )}</p>
            {if is_set( $errors.Identifier )}<p class="syn-form-error">{$errors.Identifier|wash}</p>{/if}
        </div>

        <div class="block">
            <label><input type="checkbox" name="Active" value="1"{if $syndication_feed.enabled|eq( 1 )} checked="checked"{/if} /> {'Active: the cronjob exports this feed and other sites can fetch it'|i18n( 'extension/syndication' )}</label>
        </div>

        <div class="block{if is_set( $errors.ObjectExpiryTime )} syn-field-error{/if}">
            <label for="syn-expiry">{'Object expiry time'|i18n( 'extension/syndication' )}</label>
            <input id="syn-expiry" type="text" name="ObjectExpiryTime" value="{$syndication_feed.object_expiry_time|wash}" size="6" inputmode="numeric" /> {'days'|i18n( 'extension/syndication' )}
            {if is_set( $errors.ObjectExpiryTime )}<p class="syn-form-error">{$errors.ObjectExpiryTime|wash}</p>{/if}
        </div>

        <div class="block{if is_set( $errors.CacheTimeout )} syn-field-error{/if}">
            <label for="syn-timeout">{'Cache expiry time (0 turns the cache off)'|i18n( 'extension/syndication' )}</label>
            <input id="syn-timeout" type="text" name="CacheTimeout" value="{$syndication_feed.cache_timeout|wash}" size="6" inputmode="numeric" /> {'minutes'|i18n( 'extension/syndication' )}
            {if is_set( $errors.CacheTimeout )}<p class="syn-form-error">{$errors.CacheTimeout|wash}</p>{/if}
        </div>

        <div class="block">
            <label><input type="checkbox" name="ForceCronjobCache" value="1"{if $syndication_feed.force_cronjob_cache|eq( 1 )} checked="checked"{/if} /> {'Force the cronjob cache: no cache is created during SOAP calls (recommended)'|i18n( 'extension/syndication' )}</label>
        </div>

        <div class="block">
            <label for="syn-description">{'Description for subscribers'|i18n( 'extension/syndication' )}</label>
            <textarea id="syn-description" name="PublicDescription" class="halfbox" cols="60" rows="6">{$syndication_feed.public_comment|wash}</textarea>
        </div>

        <div class="block{if is_set( $errors.Sources )} syn-field-error{/if}">
            <label>{'Sources'|i18n( 'extension/syndication' )}</label>
            {if is_set( $errors.Sources )}<p class="syn-form-error">{$errors.Sources|wash}</p>{/if}
            {if $syndication_feed.draft_source_list|count}
            <table class="list syn-table">
                <tr>
                    <th>{'Name'|i18n( 'extension/syndication' )}</th>
                    <th>{'Type'|i18n( 'extension/syndication' )}</th>
                    <th>{'Location'|i18n( 'extension/syndication' )}</th>
                    <th class="tight">{'Filters'|i18n( 'extension/syndication' )}</th>
                    <th class="tight">{'Remove'|i18n( 'extension/syndication' )}</th>
                </tr>
                {foreach $syndication_feed.draft_source_list as $source sequence array( 'bglight', 'bgdark' ) as $seq}
                <tr class="{$seq}">
                    <td>{if $source.node}<a href={$source.node.url_alias|ezurl}>{$source.node.name|wash}</a>{else}<span class="syn-pill is-bad">{'node %id is missing'|i18n( 'extension/syndication',, hash( '%id', $source.node_id ) )}</span>{/if}</td>
                    <td>{$source.type_string|wash}</td>
                    <td class="syn-wrap">{if $source.node}{$source.node.path_identification_string|wash}{/if}</td>
                    <td><a class="button" href={concat( 'syndication/list_source_filter/', $source.id, '/(generate_drafts)/1' )|ezurl}>{'Edit filters'|i18n( 'extension/syndication' )}</a></td>
                    <td><input type="checkbox" name="RemoveSourceIDArray[]" value="{$source.id}" aria-label="{'Remove'|i18n( 'extension/syndication' )|wash}" /></td>
                </tr>
                {/foreach}
            </table>
            {else}
            <div class="syn-empty"><p>{'No source yet. A feed without a source exports nothing.'|i18n( 'extension/syndication' )}</p></div>
            {/if}
            <p>
                <input class="button" type="submit" name="AddSourceButton" value="{'Add a source'|i18n( 'extension/syndication' )|wash}" />
                {if $syndication_feed.draft_source_list|count}<input class="button" type="submit" name="RemoveSourceButton" value="{'Remove selected sources'|i18n( 'extension/syndication' )|wash}" data-syn-confirm-button="{'Remove the selected sources from this feed?'|i18n( 'extension/syndication' )|wash}" formnovalidate="formnovalidate" />{/if}
            </p>
        </div>
    </div>
    <div class="controlbar">
        <div class="block">
            <input class="defaultbutton" type="submit" name="Store" value="{'Save'|i18n( 'extension/syndication' )|wash}" />
            <input class="button" type="submit" name="Cancel" value="{'Cancel'|i18n( 'extension/syndication' )|wash}" formnovalidate="formnovalidate" />
        </div>
    </div>
</div>
</form>
{include uri='design:parts/syndication/script.tpl'}
