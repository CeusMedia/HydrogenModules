<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['index-list'];

$iconLock		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-lock' ) );
$iconUnlock		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-unlock' ) );
$iconUser		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-user' ) );
$iconSave		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconEdit		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-pencil icon-white' ) );
$iconRestore	= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-repeat icon-white' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconLock		= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-fw fa-lock' ) );
	$iconUnlock		= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-fw fa-unlock' ) );
	$iconUser		= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-fw fa-user' ) );
	$iconSave		= new UI_HTML_Tag( 'i', '', array( 'class' => 'fa fa-fw fa-save' ) );
	$iconEdit		= new UI_HTML_Tag( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );
	$iconRestore	= new UI_HTML_Tag( 'i', '', array( 'class' => 'fa fa-fw fa-backward' ) );
}

$list	= [];
foreach( $modules as $module ){
	if( !count( $module->config ) )
		continue;
	$link	= HtmlTag::create( 'a', $module->title.' <small class="muted">('.count( $module->config ).')</small>', array(
		'href'	=> './admin/config/view/'.$module->id
	) );
	$list[]	= HtmlTag::create( 'li', $link );
}

return '
<div class="content-panel content-panel-form content-panel-filter">
	<h3>'.$w->heading. ' <small class="muted">('.count( $list ).')</small></h3>
	<div class="content-panel-inner">
		'.HtmlTag::create( 'ul', $list ).'
	</div>
</div>';
