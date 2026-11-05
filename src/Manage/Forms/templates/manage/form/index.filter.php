<?php

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var ?string $filterType */
/** @var ?string $filterStatus */
/** @var ?string $filterCustomerMailId */
/** @var ?string $filterManagerMailId */
/** @var ?string $filterFormId */
/** @var ?string $filterTitle */
/** @var array $mailsCustomer */
/** @var array $mailsManager */
/** @var array<string,array<string,string>> $words */

$w			= (object) $words['index.filter'];

$iconFilter		= new Icon( 'search' );
$iconReset		= new Icon( 'search-minus' );

$statuses	= $words['statuses'];
$types		= $words['types'];

$optType	= [
	''							=> $w->optionAll,
	Model_Form::TYPE_NORMAL		=> $types[Model_Form::TYPE_NORMAL],
	Model_Form::TYPE_CONFIRM	=> $types[Model_Form::TYPE_CONFIRM],
];
$optType	= HtmlElements::Options( $optType, $filterType );

$optStatus	= [
	''							=> $w->optionAll,
	Model_Form::STATUS_DISABLED		=> $statuses[Model_Form::STATUS_DISABLED],
	Model_Form::STATUS_NEW			=> $statuses[Model_Form::STATUS_NEW],
	Model_Form::STATUS_ACTIVATED	=> $statuses[Model_Form::STATUS_ACTIVATED],
];
$optStatus	= HtmlElements::Options( $optStatus, $filterStatus );

$optCustomerMail	= ['' => $w->optionAll, 0 => $w->optionNoAssignment];
foreach( $mailsCustomer as $mail )
	$optCustomerMail[$mail->mailId]	= $mail->title;
$optCustomerMail	= HtmlElements::Options( $optCustomerMail, $filterCustomerMailId );

$optManagerMail	= ['' => $w->optionAll, 0 => $w->optionNoAssignment];
foreach( $mailsManager as $mail )
	$optManagerMail[$mail->mailId] = $mail->title;
$optManagerMail	= HtmlElements::Options( $optManagerMail, $filterManagerMailId );

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/form/filter" method="post">
			<div class="row-fluid">
				<div class="span3">
					<label for="input_formId">'.$w->labelId.'</label>
					<input type="text" name="formId" id="input_formId" class="span12" value="'.$filterFormId.'"/>
				</div>
				<div class="span9">
					<label for="input_title">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.$filterTitle.'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_type">'.$w->labelType.'</label>
					<select name="type" id="input_type" class="span12" onchange="this.form.submit()">>'.$optType.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12" onchange="this.form.submit()">>'.$optStatus.'</select>
				</div>
			</div>

			<div class="row-fluid">
				<div class="span12">
					<label for="input_customerMailId">'.$w->labelCustomerMail.'</label>
					<select name="customerMailId" id="input_customerMailId" class="span12" onchange="this.form.submit()">'.$optCustomerMail.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_managerMailId">'.$w->labelManagerMail.'</label>
					<select name="managerMailId" id="input_managerMailId" class="span12" onchange="this.form.submit()">>'.$optManagerMail.'</select>
				</div>
			</div>

			<div class="buttonbar">
				<div class="btn-group">
					<button type="submit" class="btn btn-small btn-info" name="filter">'.$iconFilter.' '.$w->buttonFilter.'</button>
					<a href="./manage/form/filter/reset" class="btn btn-small btn-inverse">'.$iconReset.' '.$w->buttonReset.'</a>
				</div>
			</div>
		</form>
	</div>
</div>';
