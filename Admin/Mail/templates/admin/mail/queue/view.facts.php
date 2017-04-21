<?php

$iconBack		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );

$buttons		= array();
$buttons[]	= UI_HTML_Tag::create( 'a', $iconBack.'&nbsp;zurück', array(
	'href'	=> './admin/mail/queue/',
	'class'	=> 'btn btn-small'
) );
if( in_array( $mail->status, array( Model_Mail::STATUS_NEW, Model_Mail::STATUS_RETRY ) ) ){
	$buttons[]	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;abbrechen', array(
		'href'	=> './admin/mail/queue/cancel/'.$mail->mailId,
		'class'	=> 'btn btn-danger btn-small'
	) );
}
if( $mail->status == 2 ){
	$buttons[]	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;noch einmal versenden', array(
		'href'	=> './admin/mail/queue/resend/'.$mail->mailId,
		'class'	=> 'btn btn-primary btn-small'
	) );
}
$buttons	= join( ' ', $buttons );

$listKeys	= array(
	'mailId',
	'subject',
	'senderAddress',
	'senderId',
	'receiverName',
	'receiverAddress',
	'receiverId',
	'language',
);
$list	= array();
foreach( $listKeys as $key )
	if( $fact = $view->renderFact( $key, $mail->{$key} ) )
		$list[]	= $fact;

$listLeft	= UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'dl-horizontal' ) );

$listKeys	= array(
	'status',
	'attempts',
	'enqueuedAt',
	'attemptedAt',
	'sentAt',
);
$list	= array();
foreach( $listKeys as $key )
	if( $fact = $view->renderFact( $key, $mail->{$key} ) )
		$list[]	= $fact;
$listRight	= UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'dl-horizontal' ) );

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
?>
