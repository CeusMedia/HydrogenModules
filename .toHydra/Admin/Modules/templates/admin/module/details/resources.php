<?php

$w		= (object) $words['view'];
$count	= 0;
$list	= array();

if( $module->files->classes ){
	$classes	= array();
	foreach( $module->files->classes as $item ){
		$count++;
		$class	= NULL;
		$url		= './admin/module/viewer/viewCode/'.$moduleId.'/class/'.base64_encode( $item->file );
		$label		= UI_HTML_Tag::create( 'span', $item->file, array( 'class' => 'icon class' ) );
		$link		= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url, 'class' => 'layer-html', 'title' => 'TEST' ) );
		$classes[]	= UI_HTML_Elements::ListItem( $link, 1, array( 'class' => $class ) );
	}
	$classes	= UI_HTML_Elements::unorderedList( $classes, 1, array( 'class' => 'classes' ) );
	$list[]	= '<dt>'.$w->resourceClasses.'</dt>';
	$list[]	= '<dd>'.$classes.'</dd>';
}

//$classes	= xmp( CMF_Hydrogen_View_Helper_Diff::htmlDiff( file_get_contents( 'config.ini.inc' ), file_get_contents( 'config.ini.inc.dist' ) ) );
//die( $classes );


if( $module->files->locales ){
	$locales	= array();
	foreach( $module->files->locales as $item ){
		$count++;
		$class	= NULL;
		$url		= './admin/module/viewer/viewCode/'.$moduleId.'/locale/'.base64_encode( $item->file );
		$label		= UI_HTML_Tag::create( 'span', $item->file, array( 'class' => 'icon locale' ) );
		$link		= UI_HTML_Elements::Link( $url, $label, 'layer-html' );
		$locales[]	= UI_HTML_Elements::ListItem( $link, 1, array( 'class' => $class ) );
	}
	$locales		= UI_HTML_Elements::unorderedList( $locales, 1, array( 'class' => 'locales' ) );
	$list[]	= '<dt>'.$w->resourceLocales.'</dt>';
	$list[]	= '<dd>'.$locales.'</dd>';
}


if( $module->files->templates ){
	$templates	= array();
	foreach( $module->files->templates as $item ){
		$count++;
		$class	= NULL;
		$url		= './admin/module/viewer/viewCode/'.$moduleId.'/template/'.base64_encode( $item->file );
		$label		= UI_HTML_Tag::create( 'span', $item->file, array( 'class' => 'icon template' ) );
		$link		= UI_HTML_Elements::Link( $url, $label, 'layer-html' );
		$templates[]	= UI_HTML_Elements::ListItem( $link, 1, array( 'class' => $class ) );
	}
	$templates	= UI_HTML_Elements::unorderedList( $templates, 1, array( 'class' => 'templates' ) );
	$list[]	= '<dt>'.$w->resourceTemplates.'</dt>';
	$list[]	= '<dd>'.$templates.'</dd>';
}


if( $module->files->styles ){
	$styles	= array();
	foreach( $module->files->styles as $item ){
		$count++;
		$class	= NULL;
		$url		= './admin/module/viewer/viewCode/'.$moduleId.'/style/'.base64_encode( $item->file );
		$label		= UI_HTML_Tag::create( 'span', $item->file, array( 'class' => 'icon style' ) );
		$link		= UI_HTML_Elements::Link( $url, $label, 'layer-html' );
		$styles[]	= UI_HTML_Elements::ListItem( $link, 1, array( 'class' => $class ) );
	}
	$styles		= UI_HTML_Elements::unorderedList( $styles, 1, array( 'class' => 'styles' ) );
	$list[]	= '<dt>'.$w->resourceStyles.'</dt>';
	$list[]	= '<dd>'.$styles.'</dd>';
}


if( $module->files->scripts ){
	$scripts	= array();
	foreach( $module->files->scripts as $item ){
		$count++;
		$class	= NULL;
		$url		= './admin/module/viewer/viewCode/'.$moduleId.'/script/'.base64_encode( $item->file );
		$label		= UI_HTML_Tag::create( 'span', $item->file, array( 'class' => 'icon script' ) );
		$link		= UI_HTML_Elements::Link( $url, $label, 'layer-html', array( 'title' => 'TEST' ) );
		$scripts[]	= UI_HTML_Elements::ListItem( $link, 1, array( 'class' => $class )  );
	}
	$scripts		= UI_HTML_Elements::unorderedList( $scripts, 1, array( 'class' => 'scripts' ) );
	$list[]	= '<dt>'.$w->resourceScripts.'</dt>';
	$list[]	= '<dd>'.$scripts.'</dd>';
}


if( $module->files->images ){
	$images	= array();
	foreach( $module->files->images as $item ){
		$count++;
		$class	= NULL;
		$label		= UI_HTML_Tag::create( 'span', $item->file, array( 'class' => 'icon image' ) );
		$images[]	= UI_HTML_Elements::ListItem( $label, 1, array( 'class' => $class ) );
	}
	$images		= UI_HTML_Elements::unorderedList( $images, 1, array( 'class' => 'images' ) );
	$list[]	= '<dt>'.$w->resourceImages.'</dt>';
	$list[]	= '<dd>'.$images.'</dd>';
}

if( $module->files->files ){
	$files	= array();
	foreach( $module->files->files as $item ){
		$count++;
		$class	= NULL;
		$label		= UI_HTML_Tag::create( 'span', $item->file, array( 'class' => 'icon file' ) );
		$files[]	= UI_HTML_Elements::ListItem( $label, 1, array( 'class' => $class ) );
	}
	$files		= UI_HTML_Elements::unorderedList( $files, 1, array( 'class' => 'files' ) );
	$list[]	= '<dt>'.$w->resourceFiles.'</dt>';
	$list[]	= '<dd>'.$files.'</dd>';
}

return '
<dl class="resources">
	'.join( $list ).'
</dl>
<div class="clearfix"></div>
';

?>
