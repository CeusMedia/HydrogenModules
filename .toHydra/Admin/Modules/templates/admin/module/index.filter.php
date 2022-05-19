<?php

$filterQuery		= $session->get( 'filter-modules-query' );
$filterTypes		= $session->get( 'filter-modules-types' );
$filterCategories	= $session->get( 'filter-modules-categories' );
$filterSources		= $session->get( 'filter-modules-sources' );

if( $filterTypes === NULL )
	$filterTypes	= array(
		Model_Module::TYPE_CUSTOM,
		Model_Module::TYPE_COPY,
		Model_Module::TYPE_LINK,
		Model_Module::TYPE_SOURCE
	);

$typeMap	= array(
	Model_Module::TYPE_SOURCE		=> 'verfügbar',
	Model_Module::TYPE_LINK			=> 'eingebunden',
	Model_Module::TYPE_COPY			=> 'kopiert',
	Model_Module::TYPE_CUSTOM		=> 'eigenständig',
);

$list	= [];
$types[]	= '';
foreach( $typeMap as $typeKey => $typeLabel ){
	$attributes	= array(
		'type'		=> 'checkbox',
		'name'		=> 'filter_types[]',
		'value'		=> $typeKey,
		'id'		=> 'filter_type_'.$typeKey,
		'checked'	=> in_array( $typeKey, $filterTypes ) ? 'checked' : NULL,
	);
	$input	= UI_HTML_Tag::create( 'input', NULL, $attributes );
	$label	= UI_HTML_Tag::create( 'label', $input.'&nbsp;'.$typeLabel, array( 'for' => 'filter_type_'.$typeKey, 'class' => 'checkbox' ) );
	$list[]	= UI_HTML_Tag::create( 'li', $label );
}
$typeList	= UI_HTML_Tag::create( 'ul', join( $list ), array( 'style' => 'margin: 0px; padding: 0px; list-style: none; max-height: 240px; overflow-y: auto; border: 1px solid lightgray; border-radius: 2px; padding: 0.5em 1em; margin-bottom: 1em;' ) );

if( !$filterCategories )
	$filterCategories	= array_keys( $categories );

$list	= [];
$categories[]	= '';
foreach( $categories as $nr => $category ){
	$attributes	= array(
		'type'		=> 'checkbox',
		'name'		=> 'filter_category[]',
		'value'		=> $category,
		'id'		=> 'filter_category_'.$nr,
		'checked'	=> in_array( $category, $filterCategories ) ? 'checked' : NULL,
	);
	if( !strlen( $category ) )
		$category	= '<em>(keine Kategorie)</em>';
	$input	= UI_HTML_Tag::create( 'input', NULL, $attributes );
	$label	= UI_HTML_Tag::create( 'label', $input.'&nbsp;'.$category, array( 'for' => 'filter_category_'.$nr, 'class' => 'checkbox' ) );
	$list[]	= UI_HTML_Tag::create( 'li', $label );
}
$categoryList	= UI_HTML_Tag::create( 'ul', join( $list ), array( 'style' => 'margin: 0px; padding: 0px; list-style: none; max-height: 240px; overflow-y: auto; border: 1px solid lightgray; border-radius: 2px; padding: 0.5em 1em; margin-bottom: 1em;' ) );

$filterItemSources		= "";
if( count( $sources ) > 1 ){
	$sources['Local']	= (object) array( 'title' => 'Lokal, ohne Quelle' );
	if( !$filterSources )
		$filterSources	= array_keys( $sources );
	$list	= [];
	foreach( $sources as $sourceId => $source ){
		$attributes	= array(
			'type'		=> 'checkbox',
			'name'		=> 'filter_source[]',
			'value'		=> $sourceId,
			'id'		=> 'filter_source_'.$sourceId,
			'checked'	=> in_array( $sourceId, $filterSources ) ? 'checked' : NULL,
		);
		$input	= UI_HTML_Tag::create( 'input', NULL, $attributes );
		$title	= UI_HTML_Tag::create( 'acronym', $sourceId, array( 'title' => $source->title ) );
		$label	= UI_HTML_Tag::create( 'label', $input.'&nbsp;'.$title, array( 'for' => 'filter_source_'.$sourceId, 'class' => 'checkbox' ) );
		$list[]	= UI_HTML_Tag::create( 'li', $label );
	}
	$sourceList	= UI_HTML_Tag::create( 'ul', join( $list ), array( 'style' => 'margin: 0px; padding: 0px; list-style: none; max-height: 240px; overflow-y: auto; border: 1px solid lightgray; border-radius: 2px; padding: 0.5em 1em; margin-bottom: 1em;' ) );
	$filterItemSources	= '
		<div class="row-fluid">
			<div class="span12">
				<label><b>Quellen</b></label>
				'.$sourceList.'
			</div>
		</div>';
}

$panelFilter	= '
<form id="form_module_filter" action="./admin/module/filter" method="post">
	<div class="content-panel">
		<h3 class="filter">Filter</h3>
		<div class="content-panel-inner">
			<div class="row-fluid">
				<div class="span12">
					<label for="filter_query"><b>Suchwort</b></label>
					<input type="text" name="filter_query" id="filter_query" class="span12 max" value="'.$filterQuery.'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label><b>Zustandstyp</b></label>
					'.$typeList.'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label><b>Kategorien</b></label>
					'.$categoryList.'
				</div>
			</div>
			'.$filterItemSources.'
			<div class="buttonbar">
			'.UI_HTML_Elements::Button( 'filter', 'filtern', 'btn btn-small btn-primary button filter' ).'&nbsp;&nbsp;|&nbsp;&nbsp;
			'.UI_HTML_Elements::LinkButton( './admin/module/filter?reset', 'kein Filter', 'btn btn-small btn-inverse button reset' ).'
			</div>
		</div>
	</div>
</form>
<script>
$(document).ready(function(){
	var form = $("#form_module_filter");
	form.find("input[type=checkbox]").on("change",function(){
		form.find("button[type=submit]").trigger("click");
	});
});
</script>';

return $panelFilter;
