<?php

use CeusMedia\Common\UI\HTML\Tabs as HtmlTabs;

$w	= (object) $words['view'];

UI_HTML_Tabs::$version	= 4;
$tabs	= new HtmlTabs();

$tab	= $request->get( 'tab' );
$mapTabs	= [
	'general'	=> 'tabGeneral',
	'resources'	=> 'tabResources',
	'config'	=> 'tabConfiguration',
	'database'	=> 'tabDatabase',
	'relations'	=> 'tabRelations',
	'links'		=> 'tabLinks',
	'xml'		=> 'tabXml',
];

$selected	= 0;
$nr	= 0;
foreach( $mapTabs as $tabKey => $tabLabel ){
	$count		= 0;
	$content	= $view->loadTemplateFile( 'admin/module/editor/'.$tabKey.'.php' );
	$label		= $w->$tabLabel;
	$label		.= $count ? ' <small>('.$count.')</small>' : '';
	$selected	= ( $tab == $tabKey ) ? $nr : $selected;											//  
	$tabs->addTab( $label, $content );
	$nr++;
}
$options	= ['selected' => $selected];
$this->env->page->js->addScript( '$(document).ready(function(){'.$tabs->buildScript( '#tabs-module', $options ).'});' );


return '
<h3 class="position">
	<span>'.$words['view']['heading'].'</span>
	<cite>'.$module->title.'</cite>
</h3>
<div class="nav-position" style="margin-bottom: 0.8em">
	&laquo;&nbsp;<a href="./admin/module">Liste</a>&nbsp;&nbsp;|&nbsp;&nbsp;
	&laquo;&nbsp;<a href="./admin/module/viewer/index/'.$moduleId.'">Ansicht</a>
</div>

'.$tabs->buildTabs( 'tabs-module' ).'
';
?>
