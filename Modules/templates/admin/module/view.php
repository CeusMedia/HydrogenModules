<?php

$classes	= '-';
if( $module->links->classes ){
	$classes	= array();
	foreach( $module->links->classes as $item )
		$classes[]	= UI_HTML_Elements::ListItem( $item, 0, array( 'class' => 'class' ) );
	$classes	= UI_HTML_Elements::unorderedList( $classes );
}

$locales	= '-';
if( $module->links->locales ){
	$locales	= array();
	foreach( $module->links->locales as $item )
		$locales[]	= UI_HTML_Elements::ListItem( $item, 0, array( 'class' => 'locales' ) );
	$locales		= UI_HTML_Elements::unorderedList( $locales );
}

$templates	= '-';
if( $module->links->templates ){
	$templates	= array();
	foreach( $module->links->templates as $item )
		$templates[]	= UI_HTML_Elements::ListItem( $item, 0, array( 'class' => 'templates' ) );
	$templates	= UI_HTML_Elements::unorderedList( $templates );
}

$styles	= '-';
if( $module->links->styles ){
	$styles	= array();
	foreach( $module->links->styles as $item )
		$styles[]	= UI_HTML_Elements::ListItem( $item, 0, array( 'class' => 'styles' ) );
	$styles		= UI_HTML_Elements::unorderedList( $styles );
}

$scripts	= '-';
if( $module->links->scripts ){
	$scripts	= array();
	foreach( $module->links->scripts as $item )
		$scripts[]	= UI_HTML_Elements::ListItem( $item, 0, array( 'class' => 'scripts' ) );
	$scripts		= UI_HTML_Elements::unorderedList( $scripts );
}

$images	= '-';
if( $module->links->images ){
	$images	= array();
	foreach( $module->links->images as $item )
		$images[]	= UI_HTML_Elements::ListItem( $item, 0, array( 'class' => 'images' ) );
	$images		= UI_HTML_Elements::unorderedList( $images );
}


return '
<h2>Module "'.$module->title.'"</em></h2>
<h3>Basic Information</h3>
<dl>
	<dt>Title</dt>
	<dd>'.$module->title.'</dd>
	<dt>Description</dt>
	<dd>'.$module->description.'</dd>
	<dt>Type</dt>
	<dd>'.$words['types'][$module->type].'</dd>
</dl>
<div class="clearfix"></div>
<br/>
<h3>Linked Resources</h3>
<dl>
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
';
?>
