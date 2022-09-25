<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$classes	= '-';
if( $module->files->classes ){
	$classes	= [];
	foreach( $module->files->classes as $item )
		$classes[]	= HtmlElements::ListItem( $item, 1 );
	$classes	= HtmlElements::unorderedList( $classes, 1, array( 'class' => 'classes' ) );
}

//$classes	= xmp( CMF_Hydrogen_View_Helper_Diff::htmlDiff( file_get_contents( 'config.ini.inc' ), file_get_contents( 'config.ini.inc.dist' ) ) );
//die( $classes );

$locales	= '-';
if( $module->files->locales ){
	$locales	= [];
	foreach( $module->files->locales as $item )
		$locales[]	= HtmlElements::ListItem( $item, 1 );
	$locales		= HtmlElements::unorderedList( $locales, 1, array( 'class' => 'locales' ) );
}

$templates	= '-';
if( $module->files->templates ){
	$templates	= [];
	foreach( $module->files->templates as $item )
		$templates[]	= HtmlElements::ListItem( $item, 1 );
	$templates	= HtmlElements::unorderedList( $templates, 1, array( 'class' => 'templates' ) );
}

$styles	= '-';
if( $module->files->styles ){
	$styles	= [];
	foreach( $module->files->styles as $item )
		$styles[]	= HtmlElements::ListItem( $item, 1 );
	$styles		= HtmlElements::unorderedList( $styles, 1, array( 'class' => 'styles' ) );
}

$scripts	= '-';
if( $module->files->scripts ){
	$scripts	= [];
	foreach( $module->files->scripts as $item )
		$scripts[]	= HtmlElements::ListItem( $item, 1 );
	$scripts		= HtmlElements::unorderedList( $scripts, 1, array( 'class' => 'scripts' ) );
}

$images	= '-';
if( $module->files->images ){
	$images	= [];
	foreach( $module->files->images as $item )
		$images[]	= HtmlElements::ListItem( $item, 1 );
	$images		= HtmlElements::unorderedList( $images, 1, array( 'class' => 'images' ) );
}

$config	= '-';
if( $module->config ){
	$config	= [];
	foreach( $module->config as $key => $value )
		$config[]	= HtmlTag::create( 'dt', $key ).HtmlTag::create( 'dd', $value );
	$config	= HtmlTag::create( 'dl', join( $config ) );
}

$sql	= '-';
if( $module->sql ){
	$sql	= [];
	foreach( $module->sql as $type => $content )
		$sql[]	= HtmlTag::create( 'dt', $type ).HtmlTag::create( 'dd', HtmlTag::create( 'xmp', trim( $content ) ) );
	$sql	= HtmlTag::create( 'dl', join( $sql ) );
}

$disabled			= $module->type == 4 ? '' : 'disabled';
$buttonCancel		= HtmlElements::LinkButton( './admin/module/', $words['view']['buttonCancel'], 'button cancel' );
$buttonInstall		= HtmlElements::LinkButton( './admin/module/link/'.$module->id, $words['view']['buttonLink'], 'button add', 'Das Modul wird referenziert. Änderungen sind bedingt möglich. Fortfahren?', $disabled );
$buttonCopy			= HtmlElements::LinkButton( './admin/module/copy/'.$module->id, $words['view']['buttonCopy'], 'button add', 'Das Modul wird kopiert und damit von der Quelle entkoppelt. Wirklich?', $disabled );
$disabled			= $module->type == 4 ? 'disabled' : '';
$buttonUninstall	= HtmlElements::LinkButton( './admin/module/uninstall/'.$module->id, $words['view']['buttonRemove'], 'button remove', 'Die Modulkopie oder -referenz wird gelöscht. Wirklich?', $disabled );

UI_HTML_Tabs::$version	= 3;
$tabs	= new UI_HTML_Tabs();
$this->env->page->js->addScript( '$(document).ready(function(){'.$tabs->buildScript( '#tabs-module' ).'});' );
/*$this->env->page->js->addUrl( 'http://js.ceusmedia.com/jquery/ui/1.8.4/min.js' );
$this->env->page->css->theme->addUrl( 'http://js.ceusmedia.com/jquery/ui/1.8.4/css/smoothness.css' );
*/

$contentGeneral	= '
<dl>
	<dt>'.$words['view']['title'].'</dt>
	<dd>'.$module->title.'</dd>
	<dt>'.$words['view']['description'].'</dt>
	<dd>'.$module->description.'</dd>
	<dt>'.$words['view']['versionAvailable'].'</dt>
	<dd>'.$module->versionAvailable.'</dd>
	<dt>'.$words['view']['versionInstalled'].'</dt>
	<dd>'.$module->versionInstalled.'</dd>
	<dt>'.$words['view']['type'].'</dt>
	<dd><span class="module-type type-'.$module->type.'">'.$words['types'][$module->type].'</span></dd>
</dl>
<div class="clearfix"></div>
<div class="buttonbar">
	'.$buttonCancel.'
	'.$buttonInstall.'
	'.$buttonCopy.'
	'.$buttonUninstall.'
</div>';
$tabs->addTab( $words['view']['tabGeneral'], $contentGeneral );

$contentResources	= '
<dl class="resources">
	<dt>'.$words['view']['resourceClasses'].'</dt>
	<dd>'.$classes.'</dd>
	<dt>'.$words['view']['resourceLocales'].'</dt>
	<dd>'.$locales.'</dd>
	<dt>'.$words['view']['resourceTemplates'].'</dt>
	<dd>'.$templates.'</dd>
	<dt>'.$words['view']['resourceStyles'].'</dt>
	<dd>'.$styles.'</dd>
	<dt>'.$words['view']['resourceScripts'].'</dt>
	<dd>'.$scripts.'</dd>
	<dt>'.$words['view']['resourceImages'].'</dt>
	<dd>'.$images.'</dd>
</dl>
<div class="clearfix"></div>
';
$tabs->addTab( $words['view']['tabResources'], $contentResources );

$contentConfig	= $config.'<div class="clearfix"></div>';
$tabs->addTab( $words['view']['tabConfiguration'], $contentConfig );

$contentDatabase	= $sql.'<div class="clearfix"></div>';
$tabs->addTab( $words['view']['tabDatabase'], $contentDatabase );


return '
<h2>Module "'.$module->title.'"</em></h2>
'.$tabs->buildTabs( 'tabs-module' ).'
';
?>
