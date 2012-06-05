<?php

UI_HTML_Tabs::$version	= 3;
$tabs	= new UI_HTML_Tabs();
/*$this->env->page->js->addUrl( 'http://js.ceusmedia.com/jquery/ui/1.8.4/min.js' );
$this->env->page->css->theme->addUrl( 'http://js.ceusmedia.com/jquery/ui/1.8.4/css/smoothness.css' );
*/

$mapTabs	= array(
	'resources'	=> 'tabResources',
	'config'	=> 'tabConfiguration',
	'database'	=> 'tabDatabase',
	'relations'	=> 'tabRelations',
);

$nr			= 0;
$disabled	= array();
foreach( $mapTabs as $key => $tabLabel ){
	$count		= 0;
	$content	= require_once( 'templates/manage/module/viewer/'.$key.'.php' );
	$label		= $words['view'][$tabLabel];
	$label		.= $count ? ' <small>('.$count.')</small>' : '';
	if( $key != 'general' && !$count )
		$disabled[]	= $nr;
	$tabs->addTab( $label, $content );
	$nr++;
}

$options	= array( 'disabled'	=> $disabled );

$this->env->page->js->addScript( '$(document).ready(function(){'.$tabs->buildScript( '#tabs-module', $options ).'});' );

$panelGeneral	= $this->loadTemplateFile( 'manage/module/viewer/general.php' );

return '
<style>
dl.general dt {
	width: 140px;
	display: block;
	float: left;
	}
dl.general dd {
	margin-left: 150px;
	}
</style>
<h3>
	<span style="color: #777; font-weight: normal; font-size: 0.9em;">Modul Viewer:</span>
	<cite>'.$module->title.'</cite>
</h3>
<div class="nav-position" style="margin-bottom: 0.8em">
	&laquo;&nbsp;<a href="./manage/module">Liste</a>
</div>
'.$panelGeneral.'
'.$tabs->buildTabs( 'tabs-module' ).'
';
?>
