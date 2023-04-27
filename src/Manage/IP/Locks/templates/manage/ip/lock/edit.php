<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$data	= print_m( $lock, NULL, NULL, TRUE );

$iconCancel	= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] );
$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'icon-pencil'] );
$iconLock	= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
$iconUnlock	= HtmlTag::create( 'i', '', ['class' => 'icon-remove icon-white'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'icon-trash icon-white'] );

$helperTime = FALSE;
if( $env->getModules()->has( 'UI_Helper_TimePhraser' ) ){
	$helperTime	= new View_Helper_TimePhraser( $env );
}

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' zurück', [
	'href'		=> './manage/ip/lock/',
	'class'		=> 'btn btn-small',
] );

$buttonStatus	= HtmlTag::create( 'a', $iconLock.' aktivieren', [
	'href'		=> './manage/ip/lock/lock/'.$lock->ipLockId,
	'class'		=> 'btn btn-small btn-success',
] );
if( $lock->status > 0 ){
	$buttonStatus	= HtmlTag::create( 'a', $iconUnlock.' deaktivieren', [
		'href'		=> './manage/ip/lock/unlock/'.$lock->ipLockId,
		'class'		=> 'btn btn-small btn-inverse',
	] );
}
$buttonRemove	= HtmlTag::create( 'a', $iconRemove.' entfernen', [
	'href'		=> './manage/ip/lock/cancel/'.$lock->ipLockId,
	'class'		=> 'btn btn-small btn-danger',
] );

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
