<?php

$w	= (object) $words['view'];

$w	= (object) $words['tab-relations'];

$tableRelations	= '<br/><div>'.$w->listNone.'</div><br/>';

$relations	= array();
foreach( $module->relations->needs as $relatedModuleId ){
	if( isset( $modules[$relatedModuleId] ) ){
		$module	= $modules[$relatedModuleId];
		$module->relationType	= 'needs';
	}
	else{
		$module	= (object) array( 'id' => $relatedModuleId, 'title' => $relatedModuleId );
		$module->relationType	= 'needs';
	}
	$relations[$relatedModuleId]	= $module;
}
foreach( $module->relations->supports as $relatedModuleId ){
	if( isset( $modules[$relatedModuleId] ) ){
		$module	= $modules[$relatedModuleId];
		$module->relationType	= 'supports';
	}
	else{
		$module	= (object) array( 'id' => $relatedModuleId, 'title' => $relatedModuleId );
		$module->relationType	= 'supports';
	}
	$relations[$relatedModuleId]	= $module;
}
$count	= count( $relations );

if( $relations ){
	$rows	= array();
	foreach( $relations as $relatedModuleId => $module ){
		$status		= 2;
		if( !array_key_exists( $relatedModuleId, $modules ) )
			$status		= 4;
		else if( $modules[$relatedModuleId]->type == Model_Module::TYPE_SOURCE )
			$status		= 0;
		$link		= UI_HTML_Elements::Link( './manage/module/viewer/index/'.$relatedModuleId, $module->title );
		$urlRemove	= './manage/module/editor/removeRelation/'.$moduleId.'/'.$module->relationType.'/'.$relatedModuleId;
		$linkRemove	= UI_HTML_Elements::LinkButton( $urlRemove, '', 'button icon tiny remove' );
		$class	= 'icon module module-status-'.$status;
		$label	= UI_HTML_Tag::create( 'span', $link, array( 'class' => $class ) );
		$type	= $words['relation-types'][$module->relationType];
		$status	= $words['types'][$modules[$relatedModuleId]->type];
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
$optModule	= UI_HTML_Elements::Options( $optModule );
$optType	= UI_HTML_Elements::Options( $words['relation-types'] );

$wf	= (object) $words['tab-relations-add'];

$panelAdd	= '
<form action="./manage/module/editor/addRelation/'.$moduleId.'?tab=relations" method="post">
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


$panelGraph	= '';
if( $relations ){
	$panelGraph	= '
	<br/>
	<h4>Graph der Abhängigkeiten</h4>
	<br/>
	<div style="overflow: auto;">
		<img src="./manage/module/showRelationGraph/'.$moduleId.'"/>
	</div>
	';
}

return '
<div class="column-left-70">
	'.$tableRelations.'
	'.$panelGraph.'
</div>
<div class="column-left-30">
	'.$panelAdd.'
</div>
<div class="column-clear"></div>';
?>
