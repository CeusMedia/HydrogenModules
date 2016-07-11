<?php

$iconLock		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-lock', 'title' => 'protected' ) );
$iconUnlock		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-unlock', 'title' => 'unprotected' ) );
$iconUser		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-user', 'title' => 'configurable by user' ) );

if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconLock		= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-fw fa-lock', 'title' => 'protected' ) );
	$iconUnlock		= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-fw fa-unlock', 'title' => 'unprotected' ) );
	$iconUser		= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-fw fa-user', 'title' => 'configurable by user' ) );
}

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/admin/config/' ) );

$panelList		= $view->loadTemplateFile( 'admin/config/list.php' );
$panelHome		= '...';

return $textTop.'
<div class="row-fluid">
	<div class="span4">
		'.$panelList.'
	</div>
	<div class="span8">
		'.$panelHome.'
	</div>
</div>
'.$textBottom;
