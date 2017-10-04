<?php

$data	= print_m( $lock, NULL, NULL, TRUE );

$iconCancel	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconEdit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) );
$iconLock	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconUnlock	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-trash icon-white' ) );

$helperTime = FALSE;
if( $env->getModules()->has( 'UI_Helper_TimePhraser' ) ){
	$helperTime	= new View_Helper_TimePhraser( $env );
}

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' zurück', array(
	'href'		=> './manage/ip/lock/',
	'class'		=> 'btn btn-small',
) );

$buttonStatus	= UI_HTML_Tag::create( 'a', $iconLock.' aktivieren', array(
	'href'		=> './manage/ip/lock/lock/'.$lock->ipLockId,
	'class'		=> 'btn btn-small btn-success',
) );
if( $lock->status > 0 ){
	$buttonStatus	= UI_HTML_Tag::create( 'a', $iconUnlock.' deaktivieren', array(
		'href'		=> './manage/ip/lock/unlock/'.$lock->ipLockId,
		'class'		=> 'btn btn-small btn-inverse',
	) );
}
$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.' entfernen', array(
	'href'		=> './manage/ip/lock/remove/'.$lock->ipLockId,
	'class'		=> 'btn btn-small btn-danger',
) );

$panelEdit	= '
<div class="content-panel">
	<h3><a class="muted" hred="./manage/ip/lock">IP-Sperre:</a> '.$lock->IP.'</h3>
	<div class="content-panel-inner">
		'.$data.'
		<div class="buttonbar">
			'.$buttonCancel.'
			'.$buttonStatus.'
			'.$buttonRemove.'

		</div>
	</div>
</div>';


$tabs   = View_Manage_Ip_Lock::renderTabs( $env );
return $tabs.HTML::DivClass( 'row-fluid', HTML::DivClass( 'span12', $panelEdit ) );

