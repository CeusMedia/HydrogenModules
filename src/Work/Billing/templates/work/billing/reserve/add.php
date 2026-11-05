<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var object[] $corporations */

$w		= (object) $env->getLanguage()->getSection( 'work/billing', 'reserve-add' );

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list-alt'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' '.$w->buttonCancel, [
	'href'	=> './work/billing/reserve',
	'class'	=> 'btn btn',
] );

$buttonSave	= HtmlTag::create( 'button', $iconSave.' '.$w->buttonSave, [
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
] );

$optStatus	= [
	0	=> $w->statusInactive,
	1	=> $w->statusActive,
];
$optStatus	= HtmlElements::Options( $optStatus, 1 );

$optCorporation	= [
	'0'	=> $w->optPersonByShare,
];
foreach( $corporations as $corporation )
	$optCorporation[$corporation->corporationId]	= $corporation->title;
$optCorporation	= HtmlElements::Options( $optCorporation );

$optPersonalize	= [
	0	=> $w->no_,
	1	=> $w->yes_,
];
$optPersonalize	= HtmlElements::Options( $optPersonalize, "1" );

return '
<div class="row-fluid">
	<div class="span9">
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./work/billing/reserve/add" method="post" class="form-changes-auto">
					<div class="row-fluid">
						<div class="span8">
							<label for="input_title">'.$w->labelTitle.'</label>
							<input type="text" name="title" id="input_title" class="span12" required="required"/>
						</div>
						<div class="span4">
							<label for="input_status">'.$w->labelStatus.'</label>
							<select name="status" id="input_status" class="span12" required="required">'.$optStatus.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span8">
							<label for="input_corporationId">'.$w->labelTarget.'</label>
							<select name="corporationId" id="input_corporationId" class="span12" data-old-value="0" onchange="WorkBilling.Reserve.updatePersonalize(this)">'.$optCorporation.'</select>
						</div>
						<div class="span4">
							<label for="input_personalize">'.$w->labelPersonalize.'</label>
							<select name="personalize" id="input_personalize" class="span12" data-old-value="0" readonly="readonly">'.$optPersonalize.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span3">
							<label for="input_percent">'.$w->labelPercent.'</label>
							<input type="text" name="percent" id="input_percent" class="span10 input-number" data-min-value="0" data-max-value="50" data-max-precision="2"/><span class="suffix">%</span>
						</div>
						<div class="span3">
							<label for="input_amount">'.$w->labelAmount.'</label>
							<input type="text" name="amount" id="input_amount" class="span10 input-number" data-min-value="0" data-max-precision="2"/><span class="suffix">&euro;</span>
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
