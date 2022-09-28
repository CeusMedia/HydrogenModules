<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words['view'];
$count	= 0;
$list	= [];

if( $module->files->classes ){
	$classes	= [];
	foreach( $module->files->classes as $item ){
		$count++;
		$class	= NULL;
		$url		= './admin/module/viewer/viewCode/'.$moduleId.'/class/'.base64_encode( $item->file );
		$label		= HtmlTag::create( 'span', $item->file, array( 'class' => 'icon class' ) );
		$link		= HtmlTag::create( 'a', $label, array( 'href' => $url, 'class' => 'layer-html', 'title' => 'TEST' ) );
		$classes[]	= HtmlElements::ListItem( $link, 1, array( 'class' => $class ) );
	}
	$classes	= HtmlElements::unorderedList( $classes, 1, array( 'class' => 'classes' ) );
	$list[]	= '<dt>'.$w->resourceClasses.'</dt>';
	$list[]	= '<dd>'.$classes.'</dd>';
}

//$classes	= xmp( \CeusMedia\HydrogenFramework\View\Helper\Diff::htmlDiff( file_get_contents( 'config.ini.inc' ), file_get_contents( 'config.ini.inc.dist' ) ) );
//die( $classes );


if( $module->files->locales ){
	$locales	= [];
	foreach( $module->files->locales as $item ){
		$count++;
		$class	= NULL;
		$url		= './admin/module/viewer/viewCode/'.$moduleId.'/locale/'.base64_encode( $item->file );
		$label		= HtmlTag::create( 'span', $item->file, array( 'class' => 'icon locale' ) );
		$link		= HtmlElements::Link( $url, $label, 'layer-html' );
		$locales[]	= HtmlElements::ListItem( $link, 1, array( 'class' => $class ) );
	}
	$locales		= HtmlElements::unorderedList( $locales, 1, array( 'class' => 'locales' ) );
	$list[]	= '<dt>'.$w->resourceLocales.'</dt>';
	$list[]	= '<dd>'.$locales.'</dd>';
}


if( $module->files->templates ){
	$templates	= [];
	foreach( $module->files->templates as $item ){
		$count++;
		$class	= NULL;
		$url		= './admin/module/viewer/viewCode/'.$moduleId.'/template/'.base64_encode( $item->file );
		$label		= HtmlTag::create( 'span', $item->file, array( 'class' => 'icon template' ) );
		$link		= HtmlElements::Link( $url, $label, 'layer-html' );
		$templates[]	= HtmlElements::ListItem( $link, 1, array( 'class' => $class ) );
	}
	$templates	= HtmlElements::unorderedList( $templates, 1, array( 'class' => 'templates' ) );
	$list[]	= '<dt>'.$w->resourceTemplates.'</dt>';
	$list[]	= '<dd>'.$templates.'</dd>';
}


if( $module->files->styles ){
	$styles	= [];
	foreach( $module->files->styles as $item ){
		$count++;
		$class	= NULL;
		$url		= './admin/module/viewer/viewCode/'.$moduleId.'/style/'.base64_encode( $item->file );
		$label		= HtmlTag::create( 'span', $item->file, array( 'class' => 'icon style' ) );
		$link		= HtmlElements::Link( $url, $label, 'layer-html' );
		$styles[]	= HtmlElements::ListItem( $link, 1, array( 'class' => $class ) );
	}
	$styles		= HtmlElements::unorderedList( $styles, 1, array( 'class' => 'styles' ) );
	$list[]	= '<dt>'.$w->resourceStyles.'</dt>';
	$list[]	= '<dd>'.$styles.'</dd>';
}


if( $module->files->scripts ){
	$scripts	= [];
	foreach( $module->files->scripts as $item ){
		$count++;
		$class	= NULL;
		$url		= './admin/module/viewer/viewCode/'.$moduleId.'/script/'.base64_encode( $item->file );
		$label		= HtmlTag::create( 'span', $item->file, array( 'class' => 'icon script' ) );
		$link		= HtmlElements::Link( $url, $label, 'layer-html', array( 'title' => 'TEST' ) );
		$scripts[]	= HtmlElements::ListItem( $link, 1, array( 'class' => $class )  );
	}
	$scripts		= HtmlElements::unorderedList( $scripts, 1, array( 'class' => 'scripts' ) );
	$list[]	= '<dt>'.$w->resourceScripts.'</dt>';
	$list[]	= '<dd>'.$scripts.'</dd>';
}


if( $module->files->images ){
	$images	= [];
	foreach( $module->files->images as $item ){
		$count++;
		$class	= NULL;
		$label		= HtmlTag::create( 'span', $item->file, array( 'class' => 'icon image' ) );
		$images[]	= HtmlElements::ListItem( $label, 1, array( 'class' => $class ) );
	}
	$images		= HtmlElements::unorderedList( $images, 1, array( 'class' => 'images' ) );
	$list[]	= '<dt>'.$w->resourceImages.'</dt>';
	$list[]	= '<dd>'.$images.'</dd>';
}

if( $module->files->files ){
	$files	= [];
	foreach( $module->files->files as $item ){
		$count++;
		$class	= NULL;
		$label		= HtmlTag::create( 'span', $item->file, array( 'class' => 'icon file' ) );
		$files[]	= HtmlElements::ListItem( $label, 1, array( 'class' => $class ) );
	}
	$files		= HtmlElements::unorderedList( $files, 1, array( 'class' => 'files' ) );
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
