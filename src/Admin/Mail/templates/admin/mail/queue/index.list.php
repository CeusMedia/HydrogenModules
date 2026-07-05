<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

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
/** @var int|NULL $canRemove */

$w		= (object) $words['index'];
$wl		= (object) $words['index-list'];

$iconView		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconAttachment	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-paperclip'] );
$iconBroken		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-unlink'] );

$iconSender		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
$iconReceiver	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-chevron-right'] );
$iconUser		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-user'] );
$iconUser		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-right'] );
$iconClass		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-php'] );

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
$helper->setCase( $wl->timePhraseCase );

$logic	= new Logic_Mail( $env );
//$modelUser	= new Model_User( $env );

$dropdown	= '';
$table		= HtmlTag::create( 'div', $wl->noEntries, ['class' => 'alert alert-warn'] );
if( $mails ){
	$rows	= [];
	/** @var Entity_Mail $mail */
	foreach( $mails as $mail ){
/*		$timestamp	= $mail->enqueuedAt;
		if( (int) $mail->status === Model_Mail::STATUS_SENDING )
			$timestamp	= $mail->attemptedAt;
		if( (int) $mail->status === Model_Mail::STATUS_SENT )
			$timestamp	= $mail->sentAt;*/
		$timestamp	= $mail->status == Model_Mail::STATUS_SENT ? $mail->sentAt : ( $mail->status == 1 ? $mail->attemptedAt : $mail->enqueuedAt );
		$datetime	= date( $wl->formatDate, $timestamp );
		if( $env->getModules()->has( 'UI_Helper_TimePhraser' ) ){
			$datetime	= $helper->convert( $timestamp, TRUE, $wl->timePhrasePrefix );
		}
		$datetime   = HtmlTag::create( 'small', $datetime, ['class' => 'muted'] );
		$receiverName	= '';
		if( '' !== ( $mail->receiverName ?? '' ) )
			$receiverName	= HtmlTag::create( 'span', $iconUser.'&nbsp;'.$mail->receiverName, ['class' => 'mail-user-name'] );
		$receiverMail	= HtmlTag::create( 'small', $iconReceiver.'&nbsp;'.$mail->receiverAddress, ['class' => 'mail-user-address muted'] );
		$senderMail		= HtmlTag::create( 'small', $iconSender.'&nbsp;'.$mail->senderAddress, ['class' => 'mail-user-address muted'] );
		$from			= '?from=admin/mail/queue'.($page ? '/'.$page : '' );
		$paramPage		= $page ? '?page='.$page : '';

		$buttons		= [];
		$buttons[]		= HtmlTag::create( 'a', $iconView, [
			'href'		=> './admin/mail/queue/view/'.$mail->mailId.$paramPage,
			'class'		=> 'btn btn-info btn-mini',
			'title'		=> 'anzeigen',
		] );
		if( $canRemove )
			$buttons[]		= HtmlTag::create( 'a', $iconRemove, [
				'href'		=> './admin/mail/queue/remove/'.$mail->mailId.$paramPage,
				'class'		=> 'btn btn-danger btn-mini',
				'title'		=> 'entfernen',
			] );
		$buttons		= HtmlTag::create( 'div', $buttons, ['class' => 'btn-group'] );

		$statusClass	= $statusLabelClasses[$mail->status];

		$statusLabel	= $words['states'][$mail->status];
		if( Model_Mail::STATUS_NEW === $mail->status && 0 !== $mail->toBeSentAt ){
			$sendData		= date( 'j.n.y H:i', $mail->toBeSentAt );
			$sendData		= HtmlTag::create( 'small', $sendData, ['style' => 'font-weight: normal'] );
			$statusLabel	= $statusLabel.': '.$sendData;
		}

		$status		= HtmlTag::create( 'span', $statusLabel, ['class' => 'label label-'.$statusClass] );
		$checkbox	= HtmlTag::create( 'input', NULL, [
			'type'		=> 'checkbox',
			'class'		=> 'checkbox-mail',
			'id'		=> 'admin-mail-queue-list-all-item-'.$mail->mailId,
		], ['id' => $mail->mailId,] );

		$accessible	= FALSE;
		$features	= [];
		try{
			$logic->decompressMailObject( $mail );
			$accessible	= TRUE;
			$link		= HtmlTag::create( 'a', $mail->subject, ['href' => './admin/mail/queue/view/'.$mail->mailId.$paramPage] );
			if( $mail->objectInstance->mail->hasAttachments() )
				$features[]	= $iconAttachment;
		}
		catch( Throwable $e ){
			$features[]	= $iconBroken;
			$link		= HtmlTag::create( 'span', $mail->subject, ['class' => 'muted'] );
		}

		$features	= join( '', $features );

		$mailClass	= preg_replace( '/_/', ':', preg_replace( '/^Mail_/', '', $mail->mailClass ) );
		$mailClass	= HtmlTag::create( 'small', $mailClass, ['class' => 'muted'] );

		$cells		= [];
		if( $canRemove )
			$cells[]	= HtmlTag::create( 'td', $checkbox, ['class' => ''] );
		$cells[]	= HtmlTag::create( 'td', $features );
		$cells[]	= HtmlTag::create( 'td', $link.'<br/>'.$mailClass.'&nbsp;'.$receiverName, ['class' => 'autocut cell-mail-subject'] );
		$cells[]	= HtmlTag::create( 'td', $senderMail.'<br/>'.$receiverMail, ['class' => 'autocut cell-mail-receiver'] );
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

	$heads	= [
		$checkboxAll,
		'',
		$wl->headMain,
		$wl->headTraffic,
		$wl->headStatus,
		$wl->headActions,
	];

	$columns		= ['30px', '25px', '', '30%', '120px', '80px'];
	if( !$canRemove ){
		array_shift( $columns );
		array_shift( $heads );
	}
	$colgroup		= HtmlElements::ColumnGroup( $columns );
	$thead			= HtmlTag::create( 'thead', HtmlElements::TableHeads( $heads ) );
	$tbody			= HtmlTag::create( 'tbody', $rows );
	$table			= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped table-fixed'] );

	$itemList		= [];
	if( $canRemove )		//  @todo replace by real right, canRemove is used as hack here
		$itemList[]		= HtmlTag::create( 'li',
			HtmlTag::create( 'a', '<i class="fa fa-fw fa-remove"></i> abbrechen', ['class' => '#', 'id' => 'action-button-abort'] )
		);
	if( $canRemove )		//  @todo replace by real right, canRemove is used as hack here
		$itemList[]		= HtmlTag::create( 'li',
			HtmlTag::create( 'a', '<i class="fa fa-fw fa-refresh"></i> erneut versuchen', ['class' => '#', 'id' => 'action-button-retry'] )
		);
	if( $canRemove )
		$itemList[]	= HtmlTag::create( 'li',
			HtmlTag::create( 'a', '<i class="fa fa-fw fa-trash"></i> entfernen', ['class' => '#', 'id' => 'action-button-remove'] )
		);

	$dropdown	= '';
	if( [] !== $itemList ){
		$dropdownMenu	= HtmlTag::create( 'ul', $itemList, ['class' => 'dropdown-menu not-pull-right'] );
		$dropdownToggle	= HtmlTag::create( 'button', 'Aktion <span class="caret"></span>', [
			'type'		=> 'button',
			'class'		=> 'btn dropdown-toggle',
		], ['toggle' => 'dropdown'] );
		$dropdown		= HtmlTag::create( 'div', [$dropdownToggle, $dropdownMenu], ['class' => 'btn-group dropup'] );
	}
}

$pagination		= new PageControl( './admin/mail/queue', $page, ceil( $total / $limit ) );

return '
	<div class="content-panel">
		<h3>'.$wl->heading.'</h3>
		<div class="content-panel-inner">
			<form action="admin/mail/queue/bulk" method="post" id="form-admin-mail-queue">
				<input type="hidden" name="type" id="input_type"/>
				<input type="hidden" name="ids" id="input_ids"/>
				<input type="hidden" name="page" id="input_page" value="'.$page.'"/>
				'.$table.'
				'.$pagination.'
				'.$dropdown.'
			</form>
		</div>
	</div>';
