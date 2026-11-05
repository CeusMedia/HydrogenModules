<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;

/** @var Web $env */
/** @var Entity_IP_Lock $lock */
/** @var bool $canAdd */
/** @var bool $canCancel */
/** @var bool $canLock */
/** @var bool $canOrder */
/** @var bool $canUnlock */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['edit'];

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

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' '.$w->buttonCancel, [
	'href'		=> './manage/ip/lock/',
	'class'		=> 'btn btn-small',
] );

$buttonStatus	= HtmlTag::create( 'a', $iconLock.' '.$w->buttonActivate, [
	'href'		=> './manage/ip/lock/lock/'.$lock->ipLockId,
	'class'		=> 'btn btn-small btn-success',
] );
if( $lock->status > 0 ){
	$buttonStatus	= HtmlTag::create( 'a', $iconUnlock.' '.$w->buttonDeactivate, [
		'href'		=> './manage/ip/lock/unlock/'.$lock->ipLockId,
		'class'		=> 'btn btn-small btn-inverse',
	] );
}
$buttonRemove	= HtmlTag::create( 'a', $iconRemove.' '.$w->buttonRemove, [
	'href'		=> './manage/ip/lock/cancel/'.$lock->ipLockId,
	'class'		=> 'btn btn-small btn-danger',
] );

$panelEdit	= '
<div class="content-panel">
	<h3><a class="muted" href="./manage/ip/lock">'.$w->heading.'</a> '.$lock->IP.'</h3>
	<div class="content-panel-inner">
		'.$data.'
		<div class="buttonbar">
			'.$buttonCancel.'
			'.$buttonStatus.'
			'.$buttonRemove.'

		</div>
	</div>
</div>';


$tabs   = View_Manage_IP_Lock::renderTabs( $env );
return $tabs.HTML::DivClass( 'row-fluid', HTML::DivClass( 'span12', $panelEdit ) );
