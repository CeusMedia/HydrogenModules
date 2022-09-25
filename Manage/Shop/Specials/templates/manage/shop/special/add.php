<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconSave	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$bridgeMap	= [];
foreach( $catalogs as $bridge )
	$bridgeMap[$bridge->data->bridgeId]	= $bridge->data->title;

$optBridge	= [];
if( count($bridgeMap) > 1 )
	$optBridge['']	= '- auswählen -';
foreach( $bridgeMap as $bridgeId => $label )
	$optBridge[$bridgeId]	= $label;
$optBridge	= HtmlElements::Options( $optBridge );

$script	= '
<script>
function loadCatalogArticles(bridgeId){
	jQuery.ajax({
		url: "manage/shop/special/ajaxLoadCatalogArticles/"+bridgeId,
		dataType: "JSON",
		success: function(data){
			jQuery("#input_articleId").empty();
			var id, option, item;
			for(i in data){
				item = data[i];
				option	= jQuery("<option></option>").html(item.title).attr("value", item.id);
				jQuery("#input_articleId").append(option);
			}
		}
	});
}
jQuery(document).ready(function(){
	jQuery("#input_bridgeId").on("change", function(){
		loadCatalogArticles($(this).val());
	});
	var nrBridges = jQuery("#input_bridgeId").children("option").length;
	if( nrBridges === 1){
		jQuery("#input_bridgeId").trigger("change");
	}
});
</script>

';

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Neue Spezialität' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'form', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Katalog', array( 'for' => 'input_bridgeId' ) ),
					HtmlTag::create( 'select', $optBridge, array(
						'name'	=> 'bridgeId',
						'id'	=> 'input_bridgeId',
						'class'	=> 'span12',
					) ),
				), array( 'class' => 'span3' ) ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Artikel', array( 'for' => 'input_articleId' ) ),
					HtmlTag::create( 'select', '', array(
						'name'	=> 'articleId',
						'id'	=> 'input_articleId',
						'class'	=> 'span12',
					) ),
				), array( 'class' => 'span9' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Titel', array( 'for' => 'input_title' ) ),
					HtmlTag::create( 'input', NULL, array(
						'type'	=> 'text',
						'name'	=> 'title',
						'id'	=> 'input_title',
						'class'	=> 'span12',
					) ),
				), array( 'class' => 'span6' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'button', $iconSave.'&nbsp;speichern', array(
					'type'	=> 'submit',
					'name'	=> 'save',
					'class'	=> 'btn btn-primary',
				) ),
			), array( 'class' => 'buttonbar' ) ),
		), array(
			'action'	=> './manage/shop/special/add',
			'method'	=> 'POST',
		) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) ).$script;
