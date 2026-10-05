{ezcss_require( 'syndication.css' )}
{def $step = $wizard.current_step|sum( 1 )
     $step_names = array( 'Server'|i18n( 'extension/syndication' ), 'Feed'|i18n( 'extension/syndication' ), 'Filters'|i18n( 'extension/syndication' ), 'Options'|i18n( 'extension/syndication' ), 'Done'|i18n( 'extension/syndication' ) )}
<form action={$wizard.url|ezurl} method="post" name="Syndication">

<div class="context-block syn">
    <div class="box-header">
        <h1 class="context-title">{if $syndication_import.name}{'Import: %name'|i18n( 'extension/syndication',, hash( '%name', $syndication_import.name ) )|wash}{else}{'New import'|i18n( 'extension/syndication' )}{/if}</h1>
        <div class="header-mainline"></div>
    </div>
    <div class="box-content">

        <p class="syn-status-facts">
            {foreach $step_names as $index => $step_name}
                <span class="syn-pill {cond( eq( $index|sum( 1 ), $step ), 'is-info', lt( $index|sum( 1 ), $step ), 'is-ok', 'is-muted' )}">{$index|sum( 1 )}. {$step_name|wash}</span>
            {/foreach}
        </p>

        {foreach $wizard.error_list as $error}
        <div class="message-error" role="alert"><h2>{$error|wash}</h2></div>
        {/foreach}
        {foreach $wizard.warning_list as $warning}
        <div class="message-warning" role="alert"><h2>{$warning|wash}</h2></div>
        {/foreach}

        <div class="block">
            {include uri=$wizard.step_template wizard=$wizard}
        </div>
    </div>
    <div class="controlbar">
        <div class="block">
            {if $step|gt( 1 )}<input class="button" type="submit" name="PreviousButton" value="{'Previous'|i18n( 'extension/syndication' )|wash}" formnovalidate="formnovalidate" />{/if}
            <input class="defaultbutton" type="submit" name="NextButton" value="{if eq( $step, 5 )}{'Finish'|i18n( 'extension/syndication' )|wash}{else}{'Next'|i18n( 'extension/syndication' )|wash}{/if}" />
            <a class="button" href={'syndication/import_list'|ezurl}>{'Leave'|i18n( 'extension/syndication' )}</a>
        </div>
    </div>
</div>
</form>
