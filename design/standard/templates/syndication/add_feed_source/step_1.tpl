<form action={concat("syndication/add_feed_source/", $feed_id, "/", $next_step)|ezurl} method="post" name="Syndication">

<div class="context-block">
{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">
<h2 class="context-title">{"Add Source to feed - %name"|i18n("design/standard/syndication/edit", "", hash( "%name", $syndication_feed.name|wash ) )}</h2>

{* DESIGN: Mainline *}<div class="header-mainline"></div>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-ml"><div class="box-mr"><div class="box-content">

<div class="context-attributes">

{include uri="design:syndication/add_feed_source/add_feed_source_steps.tpl}

<div class="block">
<label>{"Choose source type"|i18n("design/standard/syndication/edit")}</label>
<input type="radio" name="SourceType" checked="checked" value="tree">{"Subtree"|i18n("design/standard/syndication/edit")}</input><br />
<input type="radio" name="SourceType" value="node">{"Node"|i18n("design/standard/syndication/edit")}</input><br />
</div>

</div>

{* DESIGN: Content END *}</div></div></div>

    {* Buttons. *}
    <div class="controlbar">
{* DESIGN: Control bar START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
    <div class="block">
        {include uri="design:gui/button.tpl" id_name="NextStepButton" value="Next >>"|i18n("design/standard/syndication/edit")}
    </div>
{* DESIGN: Control bar END *}</div></div></div></div></div></div>
    </div>

</form>
