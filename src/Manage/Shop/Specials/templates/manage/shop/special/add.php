<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

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
					HtmlTag::create( 'label', 'Katalog', ['for' => 'input_bridgeId'] ),
					HtmlTag::create( 'select', $optBridge, [
						'name'	=> 'bridgeId',
						'id'	=> 'input_bridgeId',
						'class'	=> 'span12',
					] ),
				), ['class' => 'span3'] ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Artikel', ['for' => 'input_articleId'] ),
					HtmlTag::create( 'select', '', [
						'name'	=> 'articleId',
						'id'	=> 'input_articleId',
						'class'	=> 'span12',
					] ),
				), ['class' => 'span9'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Titel', ['for' => 'input_title'] ),
					HtmlTag::create( 'input', NULL, [
						'type'	=> 'text',
						'name'	=> 'title',
						'id'	=> 'input_title',
						'class'	=> 'span12',
					] ),
				), ['class' => 'span6'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'button', $iconSave.'&nbsp;speichern', [
					'type'	=> 'submit',
					'name'	=> 'save',
					'class'	=> 'btn btn-primary',
				] ),
			), ['class' => 'buttonbar'] ),
		), [
			'action'	=> './manage/shop/special/add',
			'method'	=> 'POST',
		] ),
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] ).$script;
