<?php

use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array<array<string,string>> $words */
/** @var Dictionary $filters */
/** @var array<Entity_Mail> $mails */
/** @var string[] $mailClasses */
/** @var int|NULL $page */
/** @var int|NULL $total */
/** @var int|NULL $limit */

$w		= (object) $words['index'];
$wl		= (object) $words['index-list'];

$iconView		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconAttachment	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-paperclip'] );

$statusClasses	= [
	Model_Mail::STATUS_ABORTED	=> 'important',
	Model_Mail::STATUS_FAILED	=> 'important',
	Model_Mail::STATUS_RETRY	=> 'info',
	Model_Mail::STATUS_NEW		=> 'warning',
	Model_Mail::STATUS_SENDING	=> 'warning',
	2	=> 'success',
];

$statusLabelClasses	= [
	Model_Mail::STATUS_ABORTED	=> 'inverse',
	Model_Mail::STATUS_FAILED	=> 'danger',
	Model_Mail::STATUS_RETRY	=> 'warning',
	Model_Mail::STATUS_NEW		=> 'info',
	Model_Mail::STATUS_SENDING	=> 'info',
	Model_Mail::STATUS_SENT		=> 'success',
];

$helper	= new View_Helper_TimePhraser( $env );
$logic	= new Logic_Mail( $env );
//$modelUser	= new Model_User( $env );

$dropdown	= '';
$table		= HtmlTag::create( 'em', $wl->noEntries, ['class' => 'muted'] );
if( $mails ){
	$rows	= [];
	/** @var Entity_Mail $mail */
	foreach( $mails as $mail ){
		$logic->decompressMailObject( $mail );
/*		$timestamp	= $mail->enqueuedAt;
		if( (int) $mail->status === Model_Mail::STATUS_SENDING )
			$timestamp	= $mail->attemptedAt;
		if( (int) $mail->status === Model_Mail::STATUS_SENT )
			$timestamp	= $mail->sentAt;*/
		$timestamp	= $mail->status == Model_Mail::STATUS_SENT ? $mail->sentAt : ( $mail->status == 1 ? $mail->attemptedAt : $mail->enqueuedAt );
		$datetime	= date( $wl->formatDate, $timestamp );
		if( $env->getModules()->has( 'UI_Helper_TimePhraser' ) ){
			$datetime	= $helper->convert( $timestamp, TRUE, 'vor' );
		}
		$datetime   = HtmlTag::create( 'small', $datetime, ['class' => 'muted'] );
		$receiverName	= HtmlTag::create( 'span', $mail->receiverName, ['class' => 'mail-user-name'] );
		$receiverMail	= HtmlTag::create( 'small', $mail->receiverAddress, ['class' => 'mail-user-address muted'] );
		$senderMail		= HtmlTag::create( 'small', $mail->senderAddress, ['class' => 'mail-user-address muted'] );
		$from			= '?from=admin/mail/queue'.($page ? '/'.$page : '' );
		$paramPage		= $page ? '?page='.$page : '';
		$link			= HtmlTag::create( 'a', $mail->subject, ['href' => './admin/mail/queue/view/'.$mail->mailId.$paramPage] );

		$buttons		= [];
		$buttons[]		= HtmlTag::create( 'a', $iconView, [
			'href'		=> './admin/mail/queue/view/'.$mail->mailId.$paramPage,
			'class'		=> 'btn btn-info btn-mini',
			'title'		=> 'anzeigen',
		] );
		$buttons[]		= HtmlTag::create( 'a', $iconRemove, [
			'href'		=> './admin/mail/queue/remove/'.$mail->mailId.$paramPage,
			'class'		=> 'btn btn-danger btn-mini',
			'title'		=> 'entfernen',
		] );
		$buttons		= HtmlTag::create( 'div', $buttons, ['class' => 'btn-group'] );

		$statusClass	= $statusLabelClasses[$mail->status];

		$status		= HtmlTag::create( 'span', $words['states'][$mail->status], ['class' => 'label label-'.$statusClass] );
		$checkbox	= HtmlTag::create( 'input', NULL, [
			'type'		=> 'checkbox',
			'class'		=> 'checkbox-mail',
			'id'		=> 'admin-mail-queue-list-all-item-'.$mail->mailId,
		], ['id' => $mail->mailId,] );


		$features	= [];
		if( $mail->objectInstance->mail->hasAttachments() )
			$features[]	= $iconAttachment;
		$features	= join( '', $features );

		$cells		= [];
		$cells[]	= HtmlTag::create( 'td', $checkbox, ['class' => ''] );
		$cells[]	= HtmlTag::create( 'td', $features );
		$cells[]	= HtmlTag::create( 'td', $senderMail.'<br/>'.$link, ['class' => 'autocut cell-mail-subject'] );
		$cells[]	= HtmlTag::create( 'td', $receiverName.'<br/>'.$receiverMail, ['class' => 'autocut cell-mail-receiver'] );
		$cells[]	= HtmlTag::create( 'td', $status.'<br/>'.$datetime, ['class' => 'cell-mail-status'] );
		$cells[]	= HtmlTag::create( 'td', $buttons, ['class' => 'cell-mail-actions'] );

		$class		= 'list-item-mail';
		if( count( $filters->get( 'status' ) ) > 1 )
			$class	.= ' '.$statusClasses[$mail->status];
		$rows[]		= HtmlTag::create( 'tr', $cells, ['class' => $class] );
	}

	$checkboxAll	= HtmlTag::create( 'input', NULL, [
		'type'		=> 'checkbox',
		'id'		=> 'admin-mail-queue-list-all-items-toggle',
	] );

	$heads	= HtmlElements::TableHeads( [
		$checkboxAll,
		'',
		'Sender und Betreff',
		'Empfänger',
		'Status',
		'',
	] );

	$colgroup		= HtmlElements::ColumnGroup( ['30px', '25px', '', '30%', '120px', '80px'] );
	$thead			= HtmlTag::create( 'thead', $heads );
	$tbody			= HtmlTag::create( 'tbody', $rows );
	$table			= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped table-fixed'] );

	$dropdownMenu	= HtmlTag::create( 'ul', [
		HtmlTag::create( 'li',
			HtmlTag::create( 'a', '<i class="fa fa-remove"></i> <del>abbrechen</del>', ['class' => '#', 'id' => 'action-button-abort'] )
		),
		HtmlTag::create( 'li',
			HtmlTag::create( 'a', '<i class="fa fa-refresh"></i> <del>erneut versuchen</del>', ['class' => '#', 'id' => 'action-button-retry'] )
		),
		HtmlTag::create( 'li',
			HtmlTag::create( 'a', '<i class="fa fa-trash"></i> entfernen', ['class' => '#', 'id' => 'action-button-remove'] )
		),
	], ['class' => 'dropdown-menu not-pull-right'] );

	$dropdownToggle	= HtmlTag::create( 'button', 'Aktion <span class="caret"></span>', [
		'type'		=> 'button',
		'class'		=> 'btn dropdown-toggle',
	], ['toggle' => 'dropdown'] );
	$dropdown		= HtmlTag::create( 'div', [$dropdownToggle, $dropdownMenu], ['class' => 'btn-group dropup'] );
}

$pagination		= new PageControl( './admin/mail/queue', $page, ceil( $total / $limit ) );

return '
	<div class="content-panel">
		<h3>'.$wl->heading.'</h3>
		<div class="content-panel-inner">
			<form action="admin/mail/queue/bulk" method="post" id="form-admin-mail-queue">
				<input type="hidden" name="type" id="input_type"/>
				<input type="hidden" name="ids" id="input_ids"/>
				'.$table.'
				'.$pagination.'
				'.$dropdown.'
			</form>
		</div>
	</div>';
