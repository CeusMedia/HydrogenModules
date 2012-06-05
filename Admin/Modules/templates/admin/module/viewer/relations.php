<?php
$w		= (object) $words['view'];

$count	= count( $module->neededModules ) + count( $module->supportedModules );

$url	= './manage/module/viewer/index/';

$relationsNeeded	= '-';
if( $module->neededModules )
	$relationsNeeded	= $this->renderRelatedModulesList( $modules, $module->neededModules, $url, 'relations-needed' );

$relationsSupported	= '-';
if( $module->supportedModules )
	$relationsSupported	= $this->renderRelatedModulesList( $modules, $module->supportedModules, $url, 'relations-supported' );

return '
<div class="column-right-60">
	<fieldset>
		<legend>Abhängigkeiten</legend>
		<img src="./manage/module/showRelationGraph/'.$moduleId.'"/>
	</fieldset>
</div>
<div class="column-left-40">
	<dl>
		<dt>'.$w->relationsNeeded.'</dt>
		<dd>'.$relationsNeeded.'</dd>
		<dt>'.$w->relationsSupported.'</dt>
		<dd>'.$relationsSupported.'</dd>
	</dl>
	<div class="clearfix"></div>
</div>';
?>