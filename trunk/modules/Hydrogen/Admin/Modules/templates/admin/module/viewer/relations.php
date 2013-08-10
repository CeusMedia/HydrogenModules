<?php
$w		= (object) $words['view'];
$count	= count( $module->neededModules ) + count( $module->supportedModules ) + count( $module->neededByModules ) + count( $module->supportedByModules );

$url	= './admin/module/viewer/index/';

$relationsNeeded	= '-';
$panelGraphNeeds	= '';
if( $module->neededModules ){
	$relationsNeeded	= $this->renderRelatedModulesList( $modules, $module->neededModules, $url, 'relations relations-needed' );
	$panelGraphNeeds	= '
	<h4>Abhängigkeiten</h4>
	<img src="./admin/module/showRelationGraph/'.$moduleId.'/out/needs/recursive" style="max-width: 100%"/><br/><br/>';
}

$relationsSupported	= '-';
$panelGraphSupports	= '';
if( $module->supportedModules ){
	$relationsSupported	= $this->renderRelatedModulesList( $modules, $module->supportedModules, $url, 'relations relations-supported' );
	$panelGraphSupports	= '
	<h4>Unterstützung</h4>
	<img src="./admin/module/showRelationGraph/'.$moduleId.'/out/supports/recursive" style="max-width: 100%"/><br/><br/>';
}

$panelGraphNeededBy	= '';
$relationsNeededBy	= '-';
if( $module->neededByModules ){
	$relationsNeededBy	= $this->renderRelatedModulesList( $modules, $module->neededByModules, $url, 'relations relations-supported' );
	$panelGraphNeededBy	= '
	<h4>Wird benötigt von</h4>
	<img src="./admin/module/showRelationGraph/'.$moduleId.'/in/needs" style="max-width: 100%"/><br/><br/>';
}
$panelGraphSupportedBy	= '';
$relationsSupportedBy	= '-';
if( $module->supportedByModules ){
	$relationsSupportedBy	= $this->renderRelatedModulesList( $modules, $module->supportedByModules, $url, 'relations relations-supported' );
	$panelGraphSupportedBy	= '
	<h4>Wird unterstützt von</h4>
	<img src="./admin/module/showRelationGraph/'.$moduleId.'/in/supports" style="max-width: 100%"/><br/><br/>';
}

/*
$solver	= new Logic_Module_Relation( Logic_Module::getInstance( $this->env ) );
//$graph	= $solver->renderRelatingGraph( $moduleId );
$graph	= $solver->renderRelatingGraph( 'Resource_Database', 'supports', FALSE );

try{
	$fileName	= 'test.png';
	Graph_Renderer::convertGraphToImage( $graph, $fileName );
	print( UI_HTML_Tag::create( 'img', NULL, array( 'src' => '/sandbox/Setup/'.$fileName ) ) );
}
catch( Exception $e ){
	UI_HTML_Exception_Page::display( $e );
}
die;
*/



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
'.$panelGraphNeededBy.'
'.$panelGraphSupportedBy.'
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
<div class="column-left-50">
	<dl>
		<dt>'.$w->relationsNeededBy.'</dt>
		<dd>'.$relationsNeededBy.'</dd>
	</dl>
</div>
<div class="column-left-50">
	<dl>
		<dt>'.$w->relationsSupportedBy.'</dt>
		<dd>'.$panelGraphSupportedBy.'</dd>
	</dl>
</div>
<div class="column-clear"></div>
';
?>