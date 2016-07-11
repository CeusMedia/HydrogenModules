<?php

$iconLock		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-lock', 'title' => 'protected' ) );
$iconUnlock		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-unlock', 'title' => 'unprotected' ) );
$iconUser		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-user', 'title' => 'configurable by user' ) );

if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconLock		= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-fw fa-lock', 'title' => 'protected' ) );
	$iconUnlock		= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-fw fa-unlock', 'title' => 'unprotected' ) );
	$iconUser		= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-fw fa-user', 'title' => 'configurable by user' ) );
}

$list	= array();
foreach( $modules as $module ){
	if( !count( $module->config ) )
		continue;
	$url		= './admin/config/edit/'.$module->id;
	$version	= $versions[$module->id];
//	$label		= $module->title;
	$parts		= explode( ": ", $module->title );
	$label		= UI_HTML_Tag::create( 'span', array_pop( $parts ), array( 'class' => 'main' ) );
	while( $part = array_pop( $parts ) )
		$label	= UI_HTML_Tag::create( 'span', $part.': ', array( 'class' => 'prefix' ) ).$label;
	$label		= $label.'&nbsp;<small class="muted">'.$version.'</small>';
	$badge		= UI_HTML_Tag::create( 'span', count( $module->config ), array( 'class' => 'badge' ) );
	$link		= UI_HTML_Tag::create( 'a', $label.$badge, array( 'href' => $url, 'class' => 'autocut' ) );
	$linkClass	= isset( $moduleId ) && $moduleId == $module->id ? 'active' : NULL;
	$list[]		= UI_HTML_Tag::create( 'li', $link, array( 'class' => $linkClass ) );
}
$list		= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked' ) );

return '
<div class="content-panel">
	<h3>Module</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>
<style>
ul.nav.nav-pills li {
	position: relative;
	}
ul.nav.nav-pills li a.autocut {
	box-sizing: border-box;
	padding-right: 40px;
	font-size: 0.9em;
	}
ul.nav.nav-pills li .badge {
	position: absolute;
	right: 0.5em;
	top: 0.5em;
	}
ul.nav.nav-pills li a span.prefix {
	font-size: 0.9em;
	}
ul.nav.nav-pills li a span.main {
	font-weight: bold;
/*	display: block;*/
	}
</style>
';
