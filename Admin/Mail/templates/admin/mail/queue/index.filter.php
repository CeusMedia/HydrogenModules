<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words['index'];
$wl		= (object) $words['index-list'];
$wf		= (object) $words['index-filter'];

$statusClasses	= array(
	-3	=> 'important',
	-2	=> 'important',
	-1	=> 'info',
	0	=> 'warning',
	1	=> 'warning',
	2	=> 'success',
);

$optStatus		= array( '' => '- alle -' );
foreach( $words['states'] as $key => $value )
	$optStatus[$key]	= $key.': '.$value;
$optStatus		= HtmlElements::Options( $optStatus, $filters->get( 'status' ) );

$optOrder		= array(
	''				=> '- egal -',
	'subject'		=> 'Betreff',
	'enqueuedAt'	=> 'Eingangsdatum',
	'sentAt'		=> 'Ausgangsdatum',
);
$optOrder		= HtmlElements::Options( $optOrder, $filters->get( 'order' ) );

$optDirection	= array(
	'ASC'		=> 'aufsteigend',
	'DESC'		=> 'absteigend',
);
$optDirection	= HtmlElements::Options( $optDirection, $filters->get( 'direction' ) );

$optMailClass	= array( '' => '- alle -' );
foreach( $mailClasses as $mailClass )
	$optMailClass[$mailClass]	= preg_replace( '/_/', ':', preg_replace( '/^Mail_/', '', $mailClass ) );
$optMailClass	= HtmlElements::Options( $optMailClass, $filters->get( 'mailClass' ) );


$iconFilter		= HtmlTag::create( 'i', '', array( 'class' => 'icon-search icon-white' ) );
$iconReset		= HtmlTag::create( 'i', '', array( 'class' => 'icon-remove-circle' ) );

$buttonFilter	= HtmlTag::create( 'button', $iconFilter.' '.$wf->buttonFilter, array( 'type' => 'submit', 'class' => 'btn btn-primary' ) );
$buttonReset	= HtmlTag::create( 'a', $iconReset.' '.$wf->buttonReset, array( 'class' => 'btn btn-small', 'href' => './admin/mail/queue/filter/true' ) );


return '
	<div class="content-panel">
		<h3>'.$wf->heading.'</h3>
		<div class="content-panel-inner">
			<form action="./admin/mail/queue/filter" method="post">
				<div class="row-fluid">
					<div class="span12">
						<label for="input_subject">'.$wf->labelSubject.'</label>
						<input type="text" name="subject" id="input_subject" class="span12" value="'.htmlentities( $filters->get( 'subject' ), ENT_QUOTES, 'UTF-8' ).'"/>
					</div>
				</div>
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
						<label for="input_mailClass">'.$wf->labelMailClass.'</label>
						<select name="mailClass" id="input_mailClass" class="span12">'.$optMailClass.'</select>
					</div>
				</div>
				<div class="row-fluid">
					<div class="span12">
						<label for="input_dateStart">'.$wf->labelDateStart.'</label>
						<input type="date" name="dateStart" id="input_dateStart" class="span12" value="'.htmlentities( $filters->get( 'dateStart' ), ENT_QUOTES, 'UTF-8' ).'"/>
					</div>
				</div>
				<div class="row-fluid">
					<div class="span12">
						<label for="input_dateEnd">'.$wf->labelDateEnd.'</label>
						<input type="date" name="dateEnd" id="input_dateEnd" class="span12" value="'.htmlentities( $filters->get( 'dateEnd' ), ENT_QUOTES, 'UTF-8' ).'"/>
					</div>
				</div>
<!--				<div class="row-fluid">
					<div class="span6">
						<label for="input_timeStart">'.$wf->labelTimeStart.'</label>
						<input type="time" name="timeStart" id="input_timeStart" class="span12" value="'.htmlentities( $filters->get( 'timeStart' ), ENT_QUOTES, 'UTF-8' ).'"/>
					</div>
					<div class="span6">
						<label for="input_timeEnd">'.$wf->labelTimeEnd.'</label>
						<input type="time" name="timeEnd" id="input_timeEnd" class="span12" value="'.htmlentities( $filters->get( 'timeEnd' ), ENT_QUOTES, 'UTF-8' ).'"/>
					</div>
				</div>-->
				<div class="row-fluid">
					<div class="span12">
						<label for="input_order">'.$wf->labelOrder.'</label>
						<select name="order" id="input_order" class="span12" oninput="this.form.submit();">'.$optOrder.'</select>
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
';
?>
