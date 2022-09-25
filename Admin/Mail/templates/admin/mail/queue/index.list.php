<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words['index'];
$wl		= (object) $words['index-list'];

$iconView		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconAttachment	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-paperclip' ) );

$statusClasses	= array(
	-3	=> 'important',
	-2	=> 'important',
	-1	=> 'info',
	0	=> 'warning',
	1	=> 'warning',
	2	=> 'success',
);

$helper	= new View_Helper_TimePhraser( $env );
$logic	= new Logic_Mail( $env );
//$modelUser	= new Model_User( $env );

$dropdown	= '';
$table		= HtmlTag::create( 'em', $wl->noEntries, array( 'class' => 'muted' ) );
if( $mails ){
	$rows	= [];
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
		$datetime   = HtmlTag::create( 'small', $datetime, array( 'class' => 'muted' ) );
		$receiverName	= HtmlTag::create( 'span', $mail->receiverName, array( 'class' => 'mail-user-name' ) );
		$receiverMail	= HtmlTag::create( 'small', $mail->receiverAddress, array( 'class' => 'mail-user-address muted' ) );
		$senderMail		= HtmlTag::create( 'small', $mail->senderAddress, array( 'class' => 'mail-user-address muted' ) );
		$from			= '?from=admin/mail/queue'.($page ? '/'.$page : '' );
		$paramPage		= $page ? '?page='.$page : '';
		$link			= HtmlTag::create( 'a', $mail->subject, array( 'href' => './admin/mail/queue/view/'.$mail->mailId.$paramPage ) );

		$buttons		= [];
		$buttons[]		= HtmlTag::create( 'a', $iconView, array(
			'href'		=> './admin/mail/queue/view/'.$mail->mailId.$paramPage,
			'class'		=> 'btn btn-info btn-mini',
			'title'		=> 'anzeigen',
		) );
		$buttons[]		= HtmlTag::create( 'a', $iconRemove, array(
			'href'		=> './admin/mail/queue/remove/'.$mail->mailId.$paramPage,
			'class'		=> 'btn btn-danger btn-mini',
			'title'		=> 'entfernen',
		) );
		$buttons		= HtmlTag::create( 'div', $buttons, array( 'class' => 'btn-group' ) );

		$statusClass	= 'success';
		if( in_array( $mail->status, array( 1, 0 ) ) )
			$statusClass	= 'info';
		if( in_array( $mail->status, array( -1 ) ) )
			$statusClass	= 'warning';
		if( in_array( $mail->status, array( -2 ) ) )
			$statusClass	= 'danger';
		if( in_array( $mail->status, array( -3 ) ) )
			$statusClass	= 'inverse';

		$status		= HtmlTag::create( 'span', $words['states'][$mail->status], array( 'class' => 'label label-'.$statusClass ) );
		$checkbox	= HtmlTag::create ('input', NULL, array(
			'type'		=> 'checkbox',
			'class'		=> 'checkbox-mail',
			'id'		=> 'admin-mail-queue-list-all-item-'.$mail->mailId,
		), array( 'id' => $mail->mailId, ) );


		$features	= [];
		if( $mail->object->instance->mail->hasAttachments() )
			$features[]	= $iconAttachment;
		$features	= join( '', $features );

		$cells		= [];
		$cells[]	= HtmlTag::create( 'td', $checkbox, array( 'class' => '' ) );
		$cells[]	= HtmlTag::create( 'td', $features );
		$cells[]	= HtmlTag::create( 'td', $senderMail.'<br/>'.$link, array( 'class' => 'autocut cell-mail-subject' ) );
		$cells[]	= HtmlTag::create( 'td', $receiverName.'<br/>'.$receiverMail, array( 'class' => 'autocut cell-mail-receiver' ) );
		$cells[]	= HtmlTag::create( 'td', $status.'<br/>'.$datetime, array( 'class' => 'cell-mail-status' ) );
		$cells[]	= HtmlTag::create( 'td', $buttons, array( 'class' => 'cell-mail-actions' ) );

		$class		= 'list-item-mail';
		if( count( $filters->get( 'status' ) ) > 1 )
			$class	.= ' '.$statusClasses[$mail->status];
		$rows[]		= HtmlTag::create( 'tr', $cells, array( 'class' => $class ) );
	}

	$checkboxAll	= HtmlTag::create( 'input', NULL, array(
		'type'		=> 'checkbox',
		'id'		=> 'admin-mail-queue-list-all-items-toggle',
	) );

	$heads	= HtmlElements::TableHeads( array(
		$checkboxAll,
		'',
		'Sender und Betreff',
		'Empfänger',
		'Status',
		'',
	) );

	$colgroup		= HtmlElements::ColumnGroup( array( '30px', '25px', '', '30%', '120px', '80px' ) );
	$thead			= HtmlTag::create( 'thead', $heads );
	$tbody			= HtmlTag::create( 'tbody', $rows );
	$table			= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped table-fixed' ) );

	$dropdownMenu	= HtmlTag::create( 'ul', array(
		HtmlTag::create( 'li',
			HtmlTag::create( 'a', '<i class="fa fa-remove"></i> <strike>abbrechen</strike>', array( 'class' => '#', 'id' => 'action-button-abort' ) )
		),
		HtmlTag::create( 'li',
			HtmlTag::create( 'a', '<i class="fa fa-refresh"></i> <strike>erneut versuchen</strike>', array( 'class' => '#', 'id' => 'action-button-retry' ) )
		),
		HtmlTag::create( 'li',
			HtmlTag::create( 'a', '<i class="fa fa-trash"></i> entfernen', array( 'class' => '#', 'id' => 'action-button-remove' ) )
		),
	), array( 'class' => 'dropdown-menu not-pull-right' ) );

	$dropdownToggle	= HtmlTag::create( 'button', 'Aktion <span class="caret"></span>', array(
		'type'		=> 'button',
		'class'		=> 'btn dropdown-toggle',
	), array( 'toggle' => 'dropdown' ) );
	$dropdown		= HtmlTag::create( 'div', array( $dropdownToggle, $dropdownMenu ), array( 'class' => 'btn-group dropup' ) );
}

$pagination		= new \CeusMedia\Bootstrap\Nav\PageControl( './admin/mail/queue', $page, ceil( $total / $limit ) );

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
?>
