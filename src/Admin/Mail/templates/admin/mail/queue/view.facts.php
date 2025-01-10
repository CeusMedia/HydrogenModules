<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var View_Admin_Mail_Queue $view */
/** @var array<array<string,string>> $words */
/** @var object $mail */
/** @var int $page */

$iconBack		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-stop'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconAgain		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-refresh'] );

$buttons		= [];
$buttons[]	= HtmlTag::create( 'a', $iconBack.'&nbsp;zurück', [
	'href'	=> './admin/mail/queue/',
	'class'	=> 'btn btn-small'
] );

if( in_array( $mail->status, [Model_Mail::STATUS_NEW, Model_Mail::STATUS_RETRY] ) ){
	$buttons[]	= HtmlTag::create( 'a', $iconCancel.'&nbsp;abbrechen', [
		'href'	=> './admin/mail/queue/cancel/'.$mail->mailId,
		'class'	=> 'btn btn-inverse btn-small'
	] );
}
else{
	$buttons[]	= HtmlTag::create( 'button', $iconCancel.'&nbsp;abbrechen', [
		'type'		=> 'button',
		'disabled'	=> 'disabled',
		'class'		=> 'btn btn-inverse btn-small'
	] );
}
if( $mail->status == 2 || $mail->status == -2 ){
	$buttons[]	= HtmlTag::create( 'a', $iconAgain.'&nbsp;noch einmal versenden', [
		'href'	=> './admin/mail/queue/resend/'.$mail->mailId,
		'class'	=> 'btn btn-primary btn-small'
	] );
}
$buttons[]	= HtmlTag::create( 'a', $iconRemove.'&nbsp;entfernen', [
	'href'	=> './admin/mail/queue/remove/'.$mail->mailId.( $page ? '?page='.$page : '' ),
	'class'	=> 'btn btn-danger btn-small'
] );
$buttons	= join( ' ', $buttons );

$listKeys	= [
	'mailId',
	'subject',
	'senderAddress',
	'senderId',
	'receiverName',
	'receiverAddress',
	'receiverId',
	'mailClass',
	'language',
];
$list	= [];
foreach( $listKeys as $key )
	if( $fact = $view->renderFact( $key, $mail->{$key} ) )
		$list[]	= $fact;

$listLeft	= HtmlTag::create( 'dl', $list, ['class' => 'dl-horizontal'] );

$listKeys	= [
	'status',
	'attempts',
	'enqueuedAt',
	'attemptedAt',
	'sentAt',
];
$list	= [];
foreach( $listKeys as $key )
	if( $fact = $view->renderFact( $key, $mail->{$key} ) )
		$list[]	= $fact;
$listRight	= HtmlTag::create( 'dl', $list, ['class' => 'dl-horizontal'] );

return '
<div class="content-panel">
	<h4>'.$words['view-facts']['heading'].'</h4>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span6">
				'.$listLeft.'
			</div>
			<div class="span6">
				'.$listRight.'
			</div>
		</div>
		<div class="buttonbar">
			'.$buttons.'
		</div>
	</div>
</div>';
