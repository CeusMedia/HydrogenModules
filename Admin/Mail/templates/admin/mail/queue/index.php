<?php
$w		= (object) $words['index'];
$wl		= (object) $words['index-list'];
$wf		= (object) $words['index-filter'];

$statusClasses	= array(
	-1	=> 'info',
	0	=> 'warning',
	1	=> 'important',
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
		$link			= UI_HTML_Tag::create( 'a', $mail->subject, array( 'href' => './admin/mail/queue/view/'.$mail->mailId ) );

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

		$cells		= array();
		$cells[]	= UI_HTML_Tag::create( 'td', $senderMail.'<br/>'.$link, array( 'class' => 'autocut cell-mail-subject' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $receiverName.'<br/>'.$receiverMail, array( 'class' => 'autocut cell-mail-receiver' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $status.'<br/>'.$datetime, array( 'class' => 'cell-mail-status' ) );

		$class		= 'list-item-mail';
		if( count( $filters->get( 'status' ) ) > 1 )
			$class	.= ' '.$statusClasses[$mail->status];
		$rows[]		= UI_HTML_Tag::create( 'tr', $cells, array( 'class' => $class ) );
	}

	$heads	= UI_HTML_Elements::TableHeads( array(
		'Sender und Betreff',
		'Empfänger',
		'Status',
	) );

	$colgroup		= UI_HTML_Elements::ColumnGroup( array( "", "30%", "120px" ) );
	$thead			= UI_HTML_Tag::create( 'thead', $heads );
	$tbody			= UI_HTML_Tag::create( 'tbody', $rows );
	$table			= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped table-fixed' ) );
}

$optStatus		= array( '' => '- alle -' );
foreach( $words['states'] as $key => $value )
	$optStatus[$key]	= $key.': '.$value;
$optStatus		= UI_HTML_Elements::Options( $optStatus, $filters->get( 'status' ) );

$optOrder		= array(
	''				=> '- egal -',
	'subject'		=> 'Betreff',
	'enqueuedAt'	=> 'Eingangsdatum',
);
$optOrder		= UI_HTML_Elements::Options( $optOrder, $filters->get( 'order' ) );

$optDirection	= array(
	'ASC'		=> 'aufsteigend',
	'DESC'		=> 'absteigend',
);
$optDirection	= UI_HTML_Elements::Options( $optDirection, $filters->get( 'direction' ) );

$iconFilter		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-search icon-white' ) );
$iconReset		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove-circle' ) );

$buttonFilter	= UI_HTML_Tag::create( 'button', $iconFilter.' '.$wf->buttonFilter, array( 'type' => 'submit', 'class' => 'btn btn-primary' ) );
$buttonReset	= UI_HTML_Tag::create( 'a', $iconReset.' '.$wf->buttonReset, array( 'class' => 'btn btn-small', 'href' => './admin/mail/queue/filter/true' ) );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/admin/mail/queue/' ) );

$pagination		= new \CeusMedia\Bootstrap\PageControl( './admin/mail/queue', $page, ceil( $total / $limit ) );

return $textTop.'
<div class="row-fluid">
	<div class="span3">
		<div class="content-panel">
			<h3>'.$wf->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./admin/mail/queue/filter" method="post">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_receiverAddress">'.$wf->labelReceiverAddress.'</label>
							<input type="text" name="receiverAddress" id="input_receiverAddress" class="span12" value="'.htmlentities( $filters->get( 'receiverAddress' ), ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_status">'.$wf->labelStatus.'</label>
							<select name="status[]" id="input_status" class="span12" multiple="multiple" size="11">'.$optStatus.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_order">'.$wf->labelOrder.'</label>
							<select name="order" id="input_order" class="span12" onclick="this.form.submit();">'.$optOrder.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span7">
							<label for="input_direction">'.$wf->labelDirection.'</label>
							<select type="text" name="direction" id="input_direction" class="span12" onclick="this.form.submit();">'.$optDirection.'</select>
						</div>
						<div class="span5">
							<label for="input_limit">'.$wf->labelLimit.'</label>
							<input type="text" name="limit" id="input_limit" class="span12" value="'.$filters->get( 'limit' ).'"/>
						</div>
					</div>
					<div class="buttonbar">
						'.$buttonFilter.'
						'.$buttonReset.'
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="span9">
		<div class="content-panel">
			<h3>'.$wl->heading.'</h3>
			<div class="content-panel-inner">
				'.$table.'
				'.$pagination.'
			</div>
		</div>
	</div>
</div>
<style>
.list-item-mail {}
</style>'.$textBottom;
?>
