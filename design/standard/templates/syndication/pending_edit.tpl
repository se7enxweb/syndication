{let base_uri=concat( 'syndication/pending_edit/', $import.id ) }
<script type="text/javascript">
<!--

{literal}
function setAllPending()
{
   selects = document.getElementsByTagName( 'select' );

   for ( var i=0; i<selects.length; i++ )
   {
      if (selects[i].name.substring(0,11) == 'StatusMode_' )
      {
          selects[i].selectedIndex=1;
      }
   } 
}
{/literal}

-->
</script>
<form name="pending_edit" method="post" action={$base_uri|ezurl}>

<div class="context-block">
{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">
<h1 class="context-title">{'Syndication - Set object import status - %importName'|i18n( 'crm',, hash( '%importName', $import.name))}</h1>

{* DESIGN: Mainline *}<div class="header-mainline"></div>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-ml"><div class="box-mr"><div class="box-content">

{* Items per page selector. *}
<div class="context-toolbar">
<div class="block">
<div class="left">
{if $statusFilter|eq( -1 )}
    <span class="current">{'All'|i18n( 'design/standard/syndication/list' )}</span>
{else}
    <a href={concat( $base_uri, '/(statusFilter)/-1' )|ezurl}>{'All'|i18n( 'design/standard/syndication/list' )}</a>
{/if}
{foreach $statusNameMap as $key => $name}
    {if $statusFilter|eq( $key )}
        <span class="current">{$statusNameMap[$key]|wash}</span>
    {else}
        <a href={concat( $base_uri, '/(statusFilter)/', $key )|ezurl}>{$statusNameMap[$key]|wash}</a>
    {/if}
{/foreach}
</div>
<div class="break"></div>
</div>
</div>
<input type="button" value="Set all pending" onclick="javascript:setAllPending();" />
{* Branch list table. *}
<table class="list" cellspacing="0">
<tr>
    <th class="tight">{'ID'|i18n( 'crm' )}</th>
    <th>{'Name'|i18n( 'crm' )}</th>
    <th>{'View original'|i18n( 'crm' )}</th>
    <th>{'Created'|i18n( 'crm' )}</th>
    <th>{'Modified'|i18n( 'crm' )}</th>
    <th class="tight">{'Current status'|i18n( 'crm' )}</th>
    <th class="tight">{'Approve'|i18n( 'crm' )}</th>
</tr>
{section loop=$statusList sequence=array( bglight, bgdark )}
    <input name="StatusIDList[]" type="hidden" value="{$:item.id}" />
    <tr class="{$sequence}">
        <td>{$:item.id}</td>
        <td>{$:item.feed_item.option_array.name|wash}</td>
        <td><a href="{$:item.feed_item.option_array.original_url}" target="TOP">[{'open in new window'|i18n( 'syndication' )}]</a></td>
        <td>{$:item.feed_item.option_array.published|datetime( 'custom', '%H:%i %D %j. %M' )}</td>
        <td>{$:item.feed_item.option_array.modified|datetime( 'custom', '%H:%i %D %j. %M' )}</td>
        <td><div title="{$:item.option_array.error|wash}">{$statusNameMap[$:item.status]|wash}</div></td>
        <td>
        {if $allowChangeFromStatusList|contains( $:item.status )}
            <select name="StatusMode_{$:item.id}">
            {foreach $allowUserStatusList as $status}
                <option value="{$status}" {cond( $:item.status|eq( $status ), 'selected="selected"', '' )}>{$statusNameMap[$status]|wash}</option>
            {/foreach}
            </select>
        {/if}
        </td>
    </tr>
{/section}
</table>

{* Navigator. *}
<div class="context-toolbar">
{include name=navigator
         uri='design:navigator/google.tpl'
         page_uri=$base_uri
         view_parameters=$view_parameters
         item_count=$statusListCount
         item_limit=$view_parameters.limit}
</div>

{* DESIGN: Content END *}</div></div></div>

{* Buttons. *}
<div class="controlbar">
{* DESIGN: Control bar START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
<div align="right">
    <input class="button" name="Update" type="submit" value="{'Update'|i18n( 'crm' )}" />
</div>
{* DESIGN: Control bar END *}</div></div></div></div></div></div>

</form>

{/let}
