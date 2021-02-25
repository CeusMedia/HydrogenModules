<?php

$w	= (object) $words['view'];

$w	= (object) $words['tab-relations'];

$tableRelations	= '<br/><div>'.$w->listNone.'</div><br/>';

$relations	= array();
foreach( $module->relations->needs as $relatedModuleId ){
	if( isset( $modules[$relatedModuleId] ) )
		$item	= $modules[$relatedModuleId];
	else
		$item	= (object) array( 'id' => $relatedModuleId, 'title' => $relatedModuleId );
	$item->relationType	= 'needs';
	$relations[$relatedModuleId]	= $item;
}
foreach( $module->relations->supports as $relatedModuleId ){
	if( isset( $modules[$relatedModuleId] ) )
		$item	= $modules[$relatedModuleId];
	else
		$item	= (object) array( 'id' => $relatedModuleId, 'title' => $relatedModuleId );
	$item->relationType	= 'supports';
	$relations[$relatedModuleId]	= $item;
}
$count	= count( $relations );

if( $relations ){
	$rows	= array();
	foreach( $relations as $relatedModuleId => $relatedModule ){
		$status		= 2;
		if( !array_key_exists( $relatedModuleId, $modules ) )
			$status		= 4;
		else if( $modules[$relatedModuleId]->type == Model_Module::TYPE_SOURCE )
			$status		= 0;
		$link		= UI_HTML_Elements::Link( './admin/module/viewer/index/'.$relatedModuleId, $relatedModule->title );
		$urlRemove	= './admin/module/editor/removeRelation/'.$moduleId.'/'.$relatedModule->relationType.'/'.$relatedModuleId;
		$linkRemove	= UI_HTML_Elements::LinkButton( $urlRemove, '', 'button icon tiny remove' );
		$class	= 'icon module module-status-'.$status;
		$label	= UI_HTML_Tag::create( 'span', $link, array( 'class' => $class ) );
		$type	= $words['relation-types'][$relatedModule->relationType];
		$status	= isset( $modules[$relatedModuleId] ) ? $modules[$relatedModuleId]->type : 0;
		$status	= $words['types'][$status];
		$rows[]	= '<tr><td>'.$type.'</td><td>'.$label.'</td><td>'.$status.'</td><td>'.$linkRemove.'</td></tr>';
	}
	$colgroup		= UI_HTML_Elements::ColumnGroup( '20%', '50%', '20%', '10%' );
	$heads			= array(
		$w->headRelation,
		$w->headModule,
		$w->headStatus,
		$w->headAction,
	);
	$heads			= UI_HTML_Elements::TableHeads( $heads );
	$tableRelations	= '<table>'.$colgroup.$heads.join( $rows ).'</table>';
}


$optModule	= array();
foreach( $modules as $id => $item )
	if( $moduleId != $id )
		if( !in_array( $id, $module->relations->needs ) )
			if( !in_array( $id, $module->relations->supports ) )
				$optModule[$id]	= $item->title;
asort( $optModule );
$optModule	= UI_HTML_Elements::Options( $optModule );
$optType	= UI_HTML_Elements::Options( $words['relation-types'] );

$wf	= (object) $words['tab-relations-add'];

$panelAdd	= '
<form action="./admin/module/editor/addRelation/'.$moduleId.'?tab=relations" method="post">
	<fieldset>
		<legend class="icon add">'.$wf->legend.'</legend>
		<ul class="input">
			<li>
				<label for="input_type">'.$wf->labelType.'</label><br/>
				<select name="type" id="input_type" class="max">'.$optType.'</select>
			</li>
			<li>
				<label for="input_module">'.$wf->labelModule.'</label><br/>
				<select name="module" id="input_module" class="max">'.$optModule.'</select>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::Button( 'addRelation', $wf->buttonAdd, 'button add' ).'
		</div>
	</fieldset>
</form>';

$panelGraphNeeds	= '';
if( $module->relations->needs ){
	$panelGraphNeeds	= '
	<h4>Abhängigkeiten</h4>
	<img src="./admin/module/showRelationGraph/'.$moduleId.'" style="max-width: 100%"/><br/><br/>';
}

$panelGraphSupports	= '';
if( $module->relations->supports ){
	$panelGraphSupports	= '
	<h4>Unterstützung</h4>
	<img src="./admin/module/showRelationGraph/'.$moduleId.'/supports" style="max-width: 100%"/><br/><br/>';
}

return '
<div class="column-left-70">
	'.$tableRelations.'
</div>
<div class="column-left-30">
	'.$panelAdd.'
</div>
<div class="column-clear">
	'.$panelGraphNeeds.'
	'.$panelGraphSupports.'
</div>
<div class="column-clear"></div>';
?>
