<?php
$w		= (object) $words['index'];
$wl		= (object) $words['index-list'];

$iconView		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$statusClasses	= array(
	-3	=> 'important',
	-2	=> 'important',
	-1	=> 'info',
	0	=> 'warning',
	1	=> 'warning',
	2	=> 'success',
);

$helper	= new View_Helper_TimePhraser( $env );

//$modelUser	= new Model_User( $env );

$table		= UI_HTML_Tag::create( 'em', $wl->noEntries, array( 'class' => 'muted' ) );
if( $mails ){
	$rows	= array();
	foreach( $mails as $mail ){
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
		$datetime   = UI_HTML_Tag::create( 'small', $datetime, array( 'class' => 'muted' ) );
		$receiverName	= UI_HTML_Tag::create( 'span', $mail->receiverName, array( 'class' => 'mail-user-name' ) );
		$receiverMail	= UI_HTML_Tag::create( 'small', $mail->receiverAddress, array( 'class' => 'mail-user-address muted' ) );
		$senderMail		= UI_HTML_Tag::create( 'small', $mail->senderAddress, array( 'class' => 'mail-user-address muted' ) );
		$from			= '?from=admin/mail/queue'.($page ? '/'.$page : '' );
		$paramPage		= $page ? '?page='.$page : '';
		$link			= UI_HTML_Tag::create( 'a', $mail->subject, array( 'href' => './admin/mail/queue/view/'.$mail->mailId.$paramPage ) );

		$buttons		= array();
		$buttons[]		= UI_HTML_Tag::create( 'a', $iconView, array(
			'href'		=> './admin/mail/queue/view/'.$mail->mailId.$paramPage,
			'class'		=> 'btn btn-info btn-mini',
			'title'		=> 'anzeigen',
		) );
		$buttons[]		= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'		=> './admin/mail/queue/remove/'.$mail->mailId.$paramPage,
			'class'		=> 'btn btn-danger btn-mini',
			'title'		=> 'entfernen',
		) );
		$buttons		= UI_HTML_Tag::create( 'div', $buttons, array( 'class' => 'btn-group' ) );

		$statusClass	= 'success';
		if( in_array( $mail->status, array( 1, 0 ) ) )
			$statusClass	= 'info';
		if( in_array( $mail->status, array( -1 ) ) )
			$statusClass	= 'warning';
		if( in_array( $mail->status, array( -2 ) ) )
			$statusClass	= 'danger';
		if( in_array( $mail->status, array( -3 ) ) )
			$statusClass	= 'inverse';

		$status		= UI_HTML_Tag::create( 'span', $words['states'][$mail->status], array( 'class' => 'label label-'.$statusClass ) );
		$checkbox	= UI_HTML_Tag::create ('input', NULL, array(
			'type'		=> 'checkbox',
			'class'		=> 'checkbox-mail',
			'id'		=> 'admin-mail-queue-list-all-item-'.$mail->mailId,
		), array( 'id' => $mail->mailId, ) );

		$cells		= array();
		$cells[]	= UI_HTML_Tag::create( 'td', $checkbox, array( 'class' => '' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $senderMail.'<br/>'.$link, array( 'class' => 'autocut cell-mail-subject' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $receiverName.'<br/>'.$receiverMail, array( 'class' => 'autocut cell-mail-receiver' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $status.'<br/>'.$datetime, array( 'class' => 'cell-mail-status' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $buttons, array( 'class' => 'cell-mail-actions' ) );

		$class		= 'list-item-mail';
		if( count( $filters->get( 'status' ) ) > 1 )
			$class	.= ' '.$statusClasses[$mail->status];
		$rows[]		= UI_HTML_Tag::create( 'tr', $cells, array( 'class' => $class ) );
	}

	$checkboxAll	= UI_HTML_Tag::create( 'input', NULL, array(
		'type'		=> 'checkbox',
		'id'		=> 'admin-mail-queue-list-all-items-toggle',
	) );

	$heads	= UI_HTML_Elements::TableHeads( array(
		$checkboxAll,
		'Sender und Betreff',
		'Empfänger',
		'Status',
		'',
	) );

	$colgroup		= UI_HTML_Elements::ColumnGroup( array( '40px', '', '30%', '120px', '80px' ) );
	$thead			= UI_HTML_Tag::create( 'thead', $heads );
	$tbody			= UI_HTML_Tag::create( 'tbody', $rows );
	$table			= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped table-fixed' ) );
}

$pagination		= new \CeusMedia\Bootstrap\PageControl( './admin/mail/queue', $page, ceil( $total / $limit ) );

$dropdownMenu	= UI_HTML_Tag::create( 'ul', array(
	UI_HTML_Tag::create( 'li',
		UI_HTML_Tag::create( 'a', '<i class="fa fa-remove"></i> <strike>abbrechen</strike>', array( 'class' => '#', 'id' => 'action-button-abort' ) )
	),
	UI_HTML_Tag::create( 'li',
		UI_HTML_Tag::create( 'a', '<i class="fa fa-refresh"></i> <strike>erneut versuchen</strike>', array( 'class' => '#', 'id' => 'action-button-retry' ) )
	),
	UI_HTML_Tag::create( 'li',
		UI_HTML_Tag::create( 'a', '<i class="fa fa-trash"></i> entfernen', array( 'class' => '#', 'id' => 'action-button-remove' ) )
	),
), array( 'class' => 'dropdown-menu not-pull-right' ) );

$dropdownToggle	= UI_HTML_Tag::create( 'button', 'Aktion <span class="caret"></span>', array(
	'type'		=> 'button',
	'class'		=> 'btn dropdown-toggle',
), array( 'toggle' => 'dropdown' ) );
$dropdown		= UI_HTML_Tag::create( 'div', array( $dropdownToggle, $dropdownMenu ), array( 'class' => 'btn-group dropup' ) );


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
