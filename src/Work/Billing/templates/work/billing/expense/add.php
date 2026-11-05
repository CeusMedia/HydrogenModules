<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var object[] $corporations */
/** @var object[] $persons */

$w		= (object) $env->getLanguage()->getSection( 'work/billing', 'expense-add' );

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list-alt'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' '.$w->buttonCancel, [
	'href'	=> './work/billing/expense',
	'class'	=> 'btn btn',
] );

$buttonSave	= HtmlTag::create( 'button', $iconSave.' '.$w->buttonSave, [
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
] );

$optStatus	= [
	0		=> $w->statusInactive,
	1		=> $w->statusActive,
];
$optStatus	= HtmlElements::Options( $optStatus, 1 );

$optCorporation	= [
//	'0'	=> '- kein Unternehmen -',
];
foreach( $corporations as $corporation )
	$optCorporation[$corporation->corporationId]	= $corporation->title;
$optCorporation	= HtmlElements::Options( $optCorporation );

$optPerson	= [
//	'0'	=> '- keine Person -',
];
foreach( $persons as $person )
	$optPerson[$person->personId]	= $person->firstname.' '.$person->surname;
$optPerson	= HtmlElements::Options( $optPerson );

$optFrequency	= [
	1		=> $w->frequency1,
	2		=> $w->frequency2,
	3		=> $w->frequency3,
	4		=> $w->frequency4,
	5		=> $w->frequency5,
];
$optFrequency	= HtmlElements::Options( $optFrequency );

$optType	= [
	1		=> $w->typePerson,
	2		=> $w->typeCorporation,
];
$optFromType	= HtmlElements::Options( $optType, 1 );
$optType	= [
	0		=> $w->optNone,
	1		=> $w->typePerson,
	2		=> $w->typeCorporation,
];
$optToType	= HtmlElements::Options( $optType, 0 );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./work/billing/expense/add" method="post" class="form-changes-auto">
					<div class="row-fluid">
						<div class="span5">
							<label for="input_title">'.$w->labelTitle.'</label>
							<input type="text" name="title" id="input_title" class="span12" required="required"/>
						</div>
						<div class="span3">
							<label for="input_frequency">'.$w->labelFrequency.'</label>
							<select name="frequency" id="input_frequency" class="span12">'.$optFrequency.'</select>
						</div>
						<div class="span2">
							<label for="input_amount">'.$w->labelAmount.'</label>
							<input type="number" min="1" step="0.01" name="amount" id="input_amount" class="span10 input-number" required="required"/><span class="suffix">&euro;</span>
						</div>
						<div class="span2">
							<label for="input_status">'.$w->labelStatus.'</label>
							<select name="status" id="input_status" class="span12"/>'.$optStatus.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span5">
							<div class="row-fluid">
								<div class="span5">
									<label for="input_fromType">'.$w->labelFrom.'</label>
									<select name="fromType" id="input_fromType" class="span12 has-optionals">'.$optFromType.'</select>
								</div>
								<div class="span7 optional fromType fromType-2">
									<label for="input_fromCorporationId">'.$w->typeCorporation.'</label>
									<select name="fromCorporationId" id="input_fromCorporationId" class="span12">'.$optCorporation.'</select>
								</div>
								<div class="span7 optional fromType fromType-1">
									<label for="input_fromPersonId">'.$w->typePerson.'</label>
									<select name="fromPersonId" id="input_fromPersonId" class="span12">'.$optPerson.'</select>
								</div>
							</div>
						</div>
						<div class="span7">
							<div class="row-fluid">
								<div class="span5">
									<label for="input_toType">'.$w->labelTo.'</label>
									<select name="toType" id="input_toType" class="span12 has-optionals">'.$optToType.'</select>
								</div>
								<div class="span7 optional toType toType-2">
									<label for="input_toCorporationId">'.$w->typeCorporation.'</label>
									<select name="toCorporationId" id="input_toCorporationId" class="span12">'.$optCorporation.'</select>
								</div>
								<div class="span7 optional toType toType-1">
									<label for="input_toPersonId">'.$w->typePerson.'</label>
									<select name="toPersonId" id="input_toPersonId" class="span12">'.$optPerson.'</select>
								</div>
							</div>
						</div>
					</div>
					<div class="buttonbar">
						'.$buttonCancel.'
						'.$buttonSave.'
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';
