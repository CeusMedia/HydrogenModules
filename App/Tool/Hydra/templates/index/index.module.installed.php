<?php
$listModulesInstalled	= array();
foreach( $modulesInstalled as $moduleId => $module ){
	$desc	= explode( "\n", $module->description );
	$desc	= trim( array_shift( $desc ) );
	$label	= $desc ? '<acronym title="'.$desc.'">'.$module->title.'</acronym>' : $module->title;
	$label	= '<span class="module">'.$label.'</span>';
	$link	= '<a href="./admin/module/viewer/view/'.$moduleId.'">'.$label.'</a>';
	$listModulesInstalled[$module->title]	= '<li>'.$link.'</li>';	
}
natcasesort( $listModulesInstalled );
$panel	= '
<fieldset style="position: relative">
	<legend class="info">Module installiert <span class="small">('.count( $listModulesInstalled ).')</span></legend>
	<div style="position: absolute; right: 8px; top: 16px;">
		'.UI_HTML_Elements::LinkButton( './admin/module/installer', '', 'button tiny icon add' ).'
	</div>
	<div style="max-height: 160px; overflow: auto">
		<ul>'.join( $listModulesInstalled ).'</ul>
	</div>
</fieldset>';
$env->clock->profiler->tick( 'Template: index/index - installed' );
return $panel;
?>