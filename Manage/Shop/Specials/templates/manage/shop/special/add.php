<?php
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$bridgeMap	= array();
foreach( $catalogs as $bridge )
	$bridgeMap[$bridge->data->bridgeId]	= $bridge->data->title;

$optBridge	= array();
if( count($bridgeMap) > 1 )
	$optBridge['']	= '- auswählen -';
foreach( $bridgeMap as $bridgeId => $label )
	$optBridge[$bridgeId]	= $label;
$optBridge	= UI_HTML_Elements::Options( $optBridge );

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

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Neue Spezialität' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'form', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Katalog', array( 'for' => 'input_bridgeId' ) ),
					UI_HTML_Tag::create( 'select', $optBridge, array(
						'name'	=> 'bridgeId',
						'id'	=> 'input_bridgeId',
						'class'	=> 'span12',
					) ),
				), array( 'class' => 'span3' ) ),
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Artikel', array( 'for' => 'input_articleId' ) ),
					UI_HTML_Tag::create( 'select', '', array(
						'name'	=> 'articleId',
						'id'	=> 'input_articleId',
						'class'	=> 'span12',
					) ),
				), array( 'class' => 'span9' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Titel', array( 'for' => 'input_title' ) ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'	=> 'text',
						'name'	=> 'title',
						'id'	=> 'input_title',
						'class'	=> 'span12',
					) ),
				), array( 'class' => 'span6' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;speichern', array(
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