<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object[] $modules */

$w	= (object) $words['index-list'];

$iconLock		= HtmlTag::create( 'i', '', ['class' => 'icon-lock'] );
$iconUnlock		= HtmlTag::create( 'i', '', ['class' => 'icon-unlock'] );
$iconUser		= HtmlTag::create( 'i', '', ['class' => 'icon-user'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
$iconEdit		= HtmlTag::create( 'i', '', ['class' => 'icon-pencil icon-white'] );
$iconRestore	= HtmlTag::create( 'i', '', ['class' => 'icon-repeat icon-white'] );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconLock		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-lock'] );
	$iconUnlock		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-unlock'] );
	$iconUser		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-user'] );
	$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-save'] );
	$iconEdit		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
	$iconRestore	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-backward'] );
}

$list	= [];
foreach( $modules as $module ){
	if( !count( $module->config ) )
		continue;
	$link	= HtmlTag::create( 'a', $module->title.' <small class="muted">('.count( $module->config ).')</small>', [
		'href'	=> './admin/config/view/'.$module->id
	] );
	$list[]	= HtmlTag::create( 'li', $link );
}

return '
<div class="content-panel content-panel-form content-panel-filter">
	<h3>'.$w->heading. ' <small class="muted">('.count( $list ).')</small></h3>
	<div class="content-panel-inner">
		'.HtmlTag::create( 'ul', $list ).'
	</div>
</div>';
