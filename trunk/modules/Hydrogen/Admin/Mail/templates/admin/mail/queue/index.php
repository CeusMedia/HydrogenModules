<?php
$w		= (object) $words['index'];
$wl		= (object) $words['index-list'];
$wf		= (object) $words['index-filter'];

$statusClasses	= array(
	0	=> 'warning',
	1	=> 'important',
	2	=> 'success',
);

$helper	= new View_Helper_TimePhraser( $env );

//$modelUser	= new Model_User( $env );

$table		= UI_HTML_Tag::create( 'em', 'Keine Mails gefunden.', array( 'class' => 'muted' ) );
if( $mails ){
	$rows	= array();
	foreach( $mails as $mail ){
		$timestamp	= $mail->status == 2 ? ( $mail->status == 1 ? $mail->attemptedAt : $mail->enqueuedAt ) : $mail->sentAt;
		$datetime	= date( $wl->formatDate, $timestamp );
		if( $env->getModules()->has( 'UI_Helper_TimePhraser' ) ){
			$datetime	= $helper->convert( $timestamp, TRUE, 'vor' );
		}
		$datetime   = UI_HTML_Tag::create( 'small', $datetime, array( 'class' => 'muted' ) );
		$receiverName	= UI_HTML_Tag::create( 'span', $mail->receiverName, array( 'class' => 'mail-user-name' ) );
		$receiverMail	= UI_HTML_Tag::create( 'small', $mail->receiverAddress, array( 'class' => 'mail-user-address muted' ) );
		$senderMail		= UI_HTML_Tag::create( 'small', $mail->senderAddress, array( 'class' => 'mail-user-address muted' ) );
		$link			= UI_HTML_Tag::create( 'a', $mail->subject, array( 'href' => './admin/mail/queue/view/'.$mail->mailId ) );

		$cells		= array();
		$cells[]	= UI_HTML_Tag::create( 'td', $senderMail.'<br/>'.$link, array( 'class' => 'cell-mail-subject' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $receiverName.'<br/>'.$receiverMail, array( 'class' => 'cell-mail-receiver' ) );
		$cells[]	= UI_HTML_Tag::create( 'td', $words['states'][$mail->status].'<br/>'.$datetime, array( 'class' => 'cell-mail-status' ) );
		$rows[]		= UI_HTML_Tag::create( 'tr', $cells, array( 'class' => 'list-item-mail '.$statusClasses[$mail->status] ) );
	}
	$heads	= UI_HTML_Elements::TableHeads( array(
		'Sender und Betreff',
		'Empfänger',
		'Status',
	) );

	$colgroup		= UI_HTML_Elements::ColumnGroup( array( "55%", "25%", "15%" ) );
	$thead			= UI_HTML_Tag::create( 'thead', $heads );
	$tbody			= UI_HTML_Tag::create( 'tbody', $rows );
	$table			= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}

$optStatus		= UI_HTML_Elements::Options( $words['states'], $filters->get( 'status' ) );

$iconFilter		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-search icon-white' ) );
$iconReset		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove-circle' ) );

$buttonFilter	= UI_HTML_Tag::create( 'button', $iconFilter.' '.$wf->buttonFilter, array( 'type' => 'submit', 'class' => 'btn btn-primary' ) );
$buttonReset	= UI_HTML_Tag::create( 'a', $iconReset.' '.$wf->buttonReset, array( 'class' => 'btn btn-small', 'href' => './admin/mail/queue/filter/true' ) );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/mail/' ) );

return $textTop.'
<div class="row-fluid">
	<div class="span3">
		<h3>'.$wf->heading.'</h3>
		<form action="./admin/mail/queue/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_status">'.$wf->labelStatus.'</label>
					<select name="status[]" id="input_status" class="span12" multiple="multiple" size="3">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_limit">'.$wf->labelLimit.'</label>
					<input type="text" name="limit" id="input_limit" class="span4" value="'.$filters->get( 'limit' ).'"/>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonFilter.'
				'.$buttonReset.'
			</div>
		</form>
	</div>
	<div class="span9">
		<h3>'.$wl->heading.'</h3>
		'.$table.'
	</div>
</div>
<style>
.list-item-mail {}
</style>'.$textBottom;
?>
