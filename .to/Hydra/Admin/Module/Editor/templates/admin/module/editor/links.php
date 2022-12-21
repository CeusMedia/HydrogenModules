<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/*
		'label'		=> $label,
		'language'	=> $language,

		'path'		=> $path,
		'link'		=> $link,

		'access'	=> $access,
		'parent'	=> $parent,
		'rank'		=> $rank,

 */

$w		= (object) $words['tab-links'];

$optAccess	= ['' => $words['tab-links']['optAccessUndefined']] + $words['access-types'];

$panelEdit	= '';
if( isset( $linkNr ) && strlen( trim( $linkNr ) ) ){
	if( isset( $module->links[$linkNr] ) ){
		$link	= $module->links[$linkNr];
#		print_m( $link );
		$optAccess	= HtmlElements::Options( $optAccess, $link->access );
		$panelEdit	= '
<form id="form-module-editor-link-edit" action="./admin/module/editor/editLink/'.$moduleId.'/'.$linkNr.'" method="post">
	<fieldset>
		<legend>'.$w->legendEdit.'</legend>
		<ul class="input">
			<li class="column-left-40">
				<label for="input_path" class="mandatory">'.$w->labelPath.'</label><br/>
				<input type="text" name="path" id="input_path" class="max mandatory" value="'.$link->path.'"/>
			</li>
			<li class="column-left-30">
				<label for="input_access" class="">'.$w->labelAccess.'</label><br/>
				<select name="access" id="input_access" class="max">'.$optAccess.'</select>
			</li>
			<li class="column-left-30">
				<label for="input_access_public" class="">'.$w->labelAccess.'</label><br/>
				<input type="text" id="input_access_public" class="max" readonly="readonly" name="access" value="public"/>
			</li>
			<li class="column-left-30">
				<label for="input_link" class="">'.$w->labelLink.'</label><br/>
				<input type="text" name="link" id="input_link" class="max" value="'.$link->link.'"/>
			</li>
			<li class="column-clear column-left-40">
				<label for="input_label" class="">'.$w->labelTitle.'</label><br/>
				<input type="text" name="label" id="input_label" class="max" value="'.$link->label.'" onkeyup="showLinkEditOptionals();"/>
			</li>
<!--			<li class="column-left-33">
				<label for="input_parent" class="">'.$w->labelParent.'</label><br/>
				<input type="text" name="parent" id="input_parent" class="max" value="'.$link->parent.'"/>
			</li>-->
			<li class="column-left-10">
				<label for="input_rank" class="">'.$w->labelRank.'</label><br/>
				<input type="text" name="rank" id="input_rank" class="xs numeric" value="'.$link->rank.'"/>
			</li>
			<li class="column-left-20">
				<label for="input_language" class="">'.$w->labelLanguage.'</label><br/>
				<input type="text" name="language" id="input_language" class="s" value="'.$link->language.'"/>
			</li>
		</ul>
		<div class="buttonbar">
			'.HtmlElements::LinkButton( './admin/module/editor/view/'.$moduleId.'?tab=links', $w->buttonCancel, 'button cancel' ).'
			'.HtmlElements::Button( 'add', $w->buttonSave, 'button edit save' ).'
		</div>
	</fieldset>
</form>';
	}
}


$panelList	= '<br/><div><em>Keine Links definiert.</em></div><br/>';
if( $module->links ){
	$rows	= [];
	foreach( $module->links as $nr => $link ){
		$access	= $words['tab-links']['optAccessUndefined'];
		if( in_array( $link->access, array_keys( $words['access-types'] ) ) )
			$access	= $words['access-types'][$link->access];
		if( !empty( $link->language ) )
			$labelLanguage	= HtmlElements::Image( 'https://cdn.ceusmedia.de/img/famfamfam/flags/png/'.$link->language.'.png', $link->language );
		else
			$labelLanguage	= "*";

		$actions	= HtmlElements::LinkButton( './admin/module/editor/view/'.$moduleId.'?tab=links&linkNr='.$nr, '', 'button icon tiny edit' );
		$actions	.= HtmlElements::LinkButton( './admin/module/editor/removeLink/'.$moduleId.'/'.$nr, '', 'button icon tiny remove' );

		$row	= array(
			HtmlTag::create( 'td', $labelLanguage ),
			HtmlTag::create( 'td', $link->label ),
			HtmlTag::create( 'td', $link->path ),
//			HtmlTag::create( 'td', $link->parent ),
			HtmlTag::create( 'td', $access ),
			HtmlTag::create( 'td', $link->rank ),
			HtmlTag::create( 'td', $actions ),
		);
		$rows[]	= HtmlTag::create( 'tr', $row );
	}
	$colgroup	= HtmlElements::ColumnGroup( ['5%', '30%', '25%', '20%', '10%', '10%'] );
	$heads		= HtmlElements::TableHeads( ['', 'Beschriftung', 'Pfad', /*'Parent',*/ 'Zugriff', 'Rank', 'Aktion'] );
	$panelList	='
<table>
	'.$colgroup.'
	'.$heads.'
	'.join( $rows ).'
</table>
';
}

$optAccess	= ['' => $words['tab-links']['optAccessUndefined']] + $words['access-types'];
$optAccess	= HtmlElements::Options( $optAccess );

$panelAdd	= '
<form id="form-module-editor-link-add" action="./admin/module/editor/addLink/'.$moduleId.'" method="post">
	<fieldset>
		<legend class="icon add">'.$w->legendAdd.'</legend>
		<ul class="input">
			<li class="not-column-left-25">
				<label for="input_add_path" class="mandatory">'.$w->labelPath.'</label><br/>
				<input type="text" name="add_path" id="input_add_path" class="max mandatory" value=""/>
			</li>
			<li class="column-left-60">
				<label for="input_add_label" class="">'.$w->labelTitle.'</label><br/>
				<input type="text" name="add_label" id="input_add_label" class="max" value="" onkeyup="showLinkAddOptionals();"/>
			</li>
			<li class="column-right-20">
				<label for="input_add_language" class="">'.$w->labelLanguage.'</label><br/>
				<input type="text" name="add_language" id="input_add_language" class="xs" value=""/>
			</li>
			<li class="column-right-20">
				<label for="input_add_rank" class="">'.$w->labelRank.'</label><br/>
				<input type="text" name="add_rank" id="input_add_rank" class="xs numeric" value=""/>
			</li>
			<li class="column-clear not-column-left-25">
				<label for="input_add_access" class="">'.$w->labelAccess.'</label><br/>
				<select name="add_access" id="input_add_access" class="max">'.$optAccess.'</select>
			</li>
			<li class="column-clear not-column-left-25">
				<label for="input_add_access_public" class="">'.$w->labelAccess.'</label><br/>
				<input type="text" id="input_add_access_public" class="max" readonly="readonly" name="add_access" value="public"/>
			</li>
		</ul>
		<div class="buttonbar">
			'.HtmlElements::Button( 'add', $w->buttonAdd, 'button add' ).'
		</div>
	</fieldset>
</form>
';


return '
<script>
var UI = {
	initHighlightChangingInputs: function(options){
		var defaults = {
			parent: document,
			class: null,
			color: null,
			event: "keyup"
		};
		var options = $.extend(defaults,options);
		var container = $(options.parent);
		$(":input",container).each(function(){
			var input = $(this);
			if(input.data("original-value") === undefined)
				input.data("original-value",input.val());
			var eventName = options.event;
			if(input.prop("tagName").toLowerCase() == "select")
				eventName	= "change";
			input.on(eventName,function(){
				var input = $(this);
				var isChanged = input.val() != input.data("original-value");
				if(options.color)
					input.css("background-color",(isChanged ? options.color : ""));
				if(options.class)
					isChanged ? input.addClass(options.class) : input.removeClass(options.class);
			});
		});
	}
};

function showLinkAddOptionals(animate){
	var form = $("#form-module-editor-link-add");
	var elements1 = $("#input_add_rank",form).add("#input_add_language",form).add("#input_add_access",form);
	var elements2 = $("#input_add_access_public",form);
	var value = form.find("#input_add_label").val();
	elements1.prop("disabled",value.length == 0);
	elements2.prop("disabled",value.length > 0);
	if(value.length){
		animate ? elements1.parent().fadeIn() : elements1.parent().show();
		animate ? elements2.parent().fadeOut() : elements2.parent().hide();
	}
	else{
		animate ? elements1.parent().fadeOut() : elements1.parent().hide();
		animate ? elements2.parent().fadeIn() : elements2.parent().show();
	}
}

function showLinkEditOptionals(animate){
	var form = $("#form-module-editor-link-edit");
	if(form.length){
		var elements1 = $("#input_rank",form).add("#input_language",form).add("#input_access",form);
		var elements2 = $("#input_access_public",form);
		var value = form.find("#input_label").val();
		elements1.prop("disabled",value.length == 0);
		elements2.prop("disabled",value.length > 0);
		if(value.length){
			animate ? elements1.parent().fadeIn() : elements1.parent().show();
			animate ? elements2.parent().fadeOut() : elements2.parent().hide();
		}
		else{
			animate ? elements1.parent().fadeOut() : elements1.parent().hide();
			animate ? elements2.parent().fadeIn() : elements2.parent().show();
		}
	}
}

$(document).ready(function(){
	showLinkAddOptionals();
	showLinkEditOptionals();
	UI.initHighlightChangingInputs({color: "#FFFFBF"});
});
</script>

<div class="column-left-70">
	'.$panelList.'
	'.$panelEdit.'
</div>
<div class="column-right-30">
	'.$panelAdd.'
</div>
<div class="column-clear"></div>
';

?>
