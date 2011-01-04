<?php

$classes	= '-';
if( $module->files->classes ){
	$classes	= array();
	foreach( $module->files->classes as $item )
		$classes[]	= UI_HTML_Elements::ListItem( $item, 1 );
	$classes	= UI_HTML_Elements::unorderedList( $classes, 1, array( 'class' => 'classes' ) );
}

$locales	= '-';
if( $module->files->locales ){
	$locales	= array();
	foreach( $module->files->locales as $item )
		$locales[]	= UI_HTML_Elements::ListItem( $item, 1 );
	$locales		= UI_HTML_Elements::unorderedList( $locales, 1, array( 'class' => 'locales' ) );
}

$templates	= '-';
if( $module->files->templates ){
	$templates	= array();
	foreach( $module->files->templates as $item )
		$templates[]	= UI_HTML_Elements::ListItem( $item, 1 );
	$templates	= UI_HTML_Elements::unorderedList( $templates, 1, array( 'class' => 'templates' ) );
}

$styles	= '-';
if( $module->files->styles ){
	$styles	= array();
	foreach( $module->files->styles as $item )
		$styles[]	= UI_HTML_Elements::ListItem( $item, 1 );
	$styles		= UI_HTML_Elements::unorderedList( $styles, 1, array( 'class' => 'styles' ) );
}

$scripts	= '-';
if( $module->files->scripts ){
	$scripts	= array();
	foreach( $module->files->scripts as $item )
		$scripts[]	= UI_HTML_Elements::ListItem( $item, 1 );
	$scripts		= UI_HTML_Elements::unorderedList( $scripts, 1, array( 'class' => 'scripts' ) );
}

$images	= '-';
if( $module->files->images ){
	$images	= array();
	foreach( $module->files->images as $item )
		$images[]	= UI_HTML_Elements::ListItem( $item, 1 );
	$images		= UI_HTML_Elements::unorderedList( $images, 1, array( 'class' => 'images' ) );
}

$config	= '-';
if( $module->config ){
	$config	= array();
	foreach( $module->config as $key => $value )
		$config[]	= UI_HTML_Tag::create( 'dt', $key ).UI_HTML_Tag::create( 'dd', $value );
	$config	= UI_HTML_Tag::create( 'dl', join( $config ) );
}

$sql	= '-';
if( $module->sql ){
	$sql	= array();
	foreach( $module->sql as $type => $content )
		$sql[]	= UI_HTML_Tag::create( 'dt', $type ).UI_HTML_Tag::create( 'dd', UI_HTML_Tag::create( 'xmp', trim( $content ) ) );
	$sql	= UI_HTML_Tag::create( 'dl', join( $sql ) );
}

$disabled			= $module->type == 3 ? '' : 'disabled';
$buttonInstall		= UI_HTML_Elements::LinkButton( './admin/module/install/'.$module->id, 'installieren', 'button add', 'Das Modul wird referenziert. Änderungen sind bedingt möglich. Fortfahren?', $disabled );
$buttonCopy			= UI_HTML_Elements::LinkButton( './admin/module/copy/'.$module->id, 'kopieren', 'button add', 'Das Modul wird kopiert und damit von der Quelle entkoppelt. Wirklich?', $disabled );
$disabled			= $module->type == 3 ? 'disabled' : '';
$buttonUninstall	= UI_HTML_Elements::LinkButton( './admin/module/uninstall/'.$module->id, 'deinstallieren', 'button remove', 'Die Modulkopie oder -referenz wird gelöscht. Wirklich?', $disabled );

UI_HTML_Tabs::$version	= 3;
$tabs	= new UI_HTML_Tabs();
$this->env->page->js->addScript( '$(document).ready(function(){'.$tabs->buildScript( '#tabs-module' ).'});' );
$this->env->page->js->addUrl( 'http://js.ceusmedia.com/jquery/ui/1.8.4/min.js' );
$this->env->page->css->addUrl( 'http://js.ceusmedia.com/jquery/ui/1.8.4/css/smoothness.css' );


$contentGeneral	= '
<dl>
	<dt>Title</dt>
	<dd>'.$module->title.'</dd>
	<dt>Description</dt>
	<dd>'.$module->description.'</dd>
	<dt>Type</dt>
	<dd><span class="module-type type-'.$module->type.'">'.$words['types'][$module->type].'</span></dd>
</dl>
<div class="clearfix"></div>
<div class="buttonbar">
	'.$buttonInstall.'
	'.$buttonCopy.'
	'.$buttonUninstall.'
</div>';
$tabs->addTab( 'General', $contentGeneral );

$contentResources	= '
<dl class="resources">
	<dt>Classes</dt>
	<dd>'.$classes.'</dd>
	<dt>Locales</dt>
	<dd>'.$locales.'</dd>
	<dt>Templates</dt>
	<dd>'.$templates.'</dd>
	<dt>Styles</dt>
	<dd>'.$styles.'</dd>
	<dt>Scripts</dt>
	<dd>'.$scripts.'</dd>
	<dt>Images</dt>
	<dd>'.$images.'</dd>
</dl>
<div class="clearfix"></div>
';
$tabs->addTab( 'Resources', $contentResources );

$contentConfig	= $config.'<div class="clearfix"></div>';
$tabs->addTab( 'Configuration', $contentConfig );

$contentDatabase	= $sql.'<div class="clearfix"></div>';
$tabs->addTab( 'Database', $contentDatabase );


return '
<h2>Module "'.$module->title.'"</em></h2>
'.$tabs->buildTabs( 'tabs-module' ).'
';
?>
