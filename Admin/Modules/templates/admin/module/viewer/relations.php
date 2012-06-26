<?php
$w		= (object) $words['view'];

$count	= count( $module->neededModules ) + count( $module->supportedModules );

$url	= './admin/module/viewer/index/';

$relationsNeeded	= '-';
if( $module->neededModules )
	$relationsNeeded	= $this->renderRelatedModulesList( $modules, $module->neededModules, $url, 'relations relations-needed' );

$relationsSupported	= '-';
if( $module->supportedModules )
	$relationsSupported	= $this->renderRelatedModulesList( $modules, $module->supportedModules, $url, 'relations relations-supported' );


$panelGraphNeeds	= '';
if( $module->neededModules ){
	$panelGraphNeeds	= '
	<h4>Abhängigkeiten</h4>
	<img src="./admin/module/showRelationGraph/'.$moduleId.'" style="max-width: 100%"/><br/><br/>';
}

$panelGraphSupports	= '';
if( $module->supportedModules ){
	$panelGraphSupports	= '
	<h4>Unterstützung</h4>
	<img src="./admin/module/showRelationGraph/'.$moduleId.'/supports" style="max-width: 100%"/><br/><br/>';
}
/*
$panelGraphNeeds	= '';
if( $module->neededModules ){
	$panelGraphNeeds	= '
	<fieldset>
		<legend>Abhängigkeiten</legend>
		<img src="./admin/module/showRelationGraph/'.$moduleId.'" style="max-width: 100%"/>
	</fieldset>';
}

$panelGraphSupports	= '';
if( $module->supportedModules ){
	$panelGraphSupports	= '
	<fieldset>
		<legend>Unterstützung</legend>
		<img src="./admin/module/showRelationGraph/'.$moduleId.'/supports" style="max-width: 100%"/>
	</fieldset>';
}
*/

return '
'.$panelGraphNeeds.'
'.$panelGraphSupports.'
<br/>
<div class="column-left-50">
	<dl>
		<dt>'.$w->relationsNeeded.'</dt>
		<dd>'.$relationsNeeded.'</dd>
	</dl>
</div>
<div class="column-left-50">
	<dl>
		<dt>'.$w->relationsSupported.'</dt>
		<dd>'.$relationsSupported.'</dd>
	</dl>
</div>
<div class="column-clear"></div>
';
?>