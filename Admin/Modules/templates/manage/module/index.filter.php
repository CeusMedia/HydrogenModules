<?php

$filterQuery		= $session->get( 'filter-modules-query' );
$filterTypes		= $session->get( 'filter-modules-types' );
$filterCategories	= $session->get( 'filter-modules-categories' );
$filterSources		= $session->get( 'filter-modules-sources' );

if( $filterTypes === NULL )
	$filterTypes	= array(
		Model_Module::TYPE_CUSTOM,
		Model_Module::TYPE_COPY,
#		Model_Module::TYPE_LINK,
		Model_Module::TYPE_SOURCE
	);

for( $i=1; $i<=4; $i++)
	$inputType[$i]	= UI_HTML_Tag::create( 'input', NULL, array(
		'type'		=> 'checkbox',
		'name'		=> 'filter_types[]',
		'value'		=> $i,
		'id'		=> 'filter_type_'.$i,
		'class'		=> 'filter-type',
		'checked'	=> in_array( $i, $filterTypes ) ? 'checked' : NULL,
	));

if( !$filterCategories )
	$filterCategories	= array_keys( $categories );

$list	= array();
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
	$label	= UI_HTML_Tag::create( 'label', $input.'&nbsp;'.$category, array( 'for' => 'filter_category_'.$nr ) );
	$list[]	= UI_HTML_Tag::create( 'li', $label );
}
$categoryList	= UI_HTML_Tag::create( 'ul', join( $list ), array( 'style' => 'margin: 0px; padding: 0px; list-style: none' ) );

$filterItemSources		= "";
if( count( $sources ) > 1 ){
	$sources['Local']	= (object) array( 'title' => 'Lokal, ohne Quelle' );
	if( !$filterSources )
		$filterSources	= array_keys( $sources );
	$list	= array();
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
		$label	= UI_HTML_Tag::create( 'label', $input.'&nbsp;'.$title, array( 'for' => 'filter_source_'.$sourceId ) );
		$list[]	= UI_HTML_Tag::create( 'li', $label );
	}
	$sourceList	= UI_HTML_Tag::create( 'ul', join( $list ), array( 'style' => 'margin: 0px; padding: 0px; list-style: none' ) );
	$filterItemSources	= '
			<li>
				<b><label>Quellen</label></b><br/>
				'.$sourceList.'
			</li>';
}

$panelFilter	= '
<form id="form_module_filter" action="./manage/module/filter" method="post">
	<fieldset>
		<legend class="filter">Filter</legend>
		<ul class="input">
			<li>
				<b><label for="filter_query">Suchwort</label></b><br/>
				<input type="text" name="filter_query" id="filter_query" class="max" value="'.$filterQuery.'"/>
			</li>
			<li>
				<b><label>Zustandstyp</label></b><br/>
				<ul style="margin: 0px; padding: 0px; list-style: none">
					<li>
						<label for="filter_type_4">
							'.$inputType[4].'
							&nbsp;verfügbar
						</label>
					</li>
<!--					<li>
						<label for="filter_type_3">
							'.$inputType[3].'
							&nbsp;eingebunden
						</label>
					</li>-->
					<li>
						<label for="filter_type_2">
							'.$inputType[2].'
							&nbsp;kopiert
						</label>
					</li>
					<li>
						<label for="filter_type_1">
							'.$inputType[1].'
							&nbsp;eigenständig
						</label>
					</li>
				</ul>
			</li>
			<li>
				<b><label>Kategorien</label></b><br/>
				'.$categoryList.'
			</li>
			'.$filterItemSources.'
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::Button( 'filter', 'filtern', 'button filter' ).'&nbsp;&nbsp;|&nbsp;&nbsp; 
			'.UI_HTML_Elements::LinkButton( './manage/module/filter?reset', 'kein Filter', 'button reset' ).'
		</div>
	</fieldset>
</form>
<script>
$(document).ready(function(){
	var form = $("#form_module_filter");
	form.find("input[type=checkbox]").bind("change",function(){
		form.find("button[type=submit]").trigger("click");
	});
});
</script>
';

return $panelFilter;
?>