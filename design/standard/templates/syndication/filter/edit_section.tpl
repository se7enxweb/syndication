<h2>{'Edit the section filter'|i18n( 'extension/syndication' )}</h2>
<label for="syn-section">{'Allowed section'|i18n( 'extension/syndication' )}</label>
<select id="syn-section" name="SectionID">
    {foreach $section_array as $section}
    <option value="{$section.id}"{if and( is_set( $default_section ), eq( $default_section, $section.id ) )} selected="selected"{/if}>{$section.name|wash}</option>
    {/foreach}
</select>
