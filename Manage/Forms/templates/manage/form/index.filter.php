<?php

$iconFilter		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search' ) );
$iconReset		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search-minus' ) );

$statuses	= array(
	-1		=> 'deaktiviert',
	0		=> 'in Arbeit',
	1		=> 'aktiviert',
);
$types		= array(
	0		=> 'direkter Versand',
	1		=> 'mit Double-Opt-In',
);

$optType	= array(
	''							=> '- alle -',
	Model_Form::TYPE_NORMAL		=> $types[Model_Form::TYPE_NORMAL],
	Model_Form::TYPE_CONFIRM	=> $types[Model_Form::TYPE_CONFIRM],
);
$optType	= UI_HTML_Elements::Options( $optType, $filterType );

$optStatus	= array(
	''							=> '- alle -',
	Model_Form::STATUS_DISABLED		=> $statuses[Model_Form::STATUS_DISABLED],
	Model_Form::STATUS_NEW			=> $statuses[Model_Form::STATUS_NEW],
	Model_Form::STATUS_ACTIVATED	=> $statuses[Model_Form::STATUS_ACTIVATED],
);
$optStatus	= UI_HTML_Elements::Options( $optStatus, $filterStatus );

$optCustomerMail	= array( '' => '- alle -', 0 => '- keine Zuweisung -' );
foreach( $mailsCustomer as $mail )
	$optCustomerMail[$mail->mailId]	= $mail->title;
$optCustomerMail	= UI_HTML_Elements::Options( $optCustomerMail, $filterCustomerMailId );

$optManagerMail	= array( '' => '- alle -', 0 => '- keine Zuweisung -' );
foreach( $mailsManager as $mail )
	$optManagerMail[$mail->mailId] = $mail->title;
$optManagerMail	= UI_HTML_Elements::Options( $optManagerMail, $filterManagerMailId );

return '
<div class="content-panel">
	<h3>Filter</h3>
	<div class="content-panel-inner">
		<form action="./manage/form/filter" method="post">
			<div class="row-fluid">
				<div class="span3">
					<label for="input_formId">ID</label>
					<input type="text" name="formId" id="input_formId" class="span12" value="'.$filterFormId.'"/>
				</div>
				<div class="span9">
					<label for="input_title">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.$filterTitle.'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_type">Typ</label>
					<select name="type" id="input_type" class="span12" onchange="this.form.submit()">>'.$optType.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_status">Zustand</label>
					<select name="status" id="input_status" class="span12" onchange="this.form.submit()">>'.$optStatus.'</select>
				</div>
			</div>

			<div class="row-fluid">
				<div class="span12">
					<label for="input_customerMailId">E-Mail an Absender</label>
					<select name="customerMailId" id="input_customerMailId" class="span12" onchange="this.form.submit()">'.$optCustomerMail.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_managerMailId">E-Mail an Empfänger</label>
					<select name="managerMailId" id="input_managerMailId" class="span12" onchange="this.form.submit()">>'.$optManagerMail.'</select>
				</div>
			</div>

			<div class="buttonbar">
				<div class="btn-group">
					<button type="submit" class="btn btn-small btn-info" name="filter">'.$iconFilter.' filtern</button>
					<a href="./manage/form/filter/reset" class="btn btn-small btn-inverse">'.$iconReset.' leeren</a>
				</div>
			</div>
		</form>
	</div>
</div>';
