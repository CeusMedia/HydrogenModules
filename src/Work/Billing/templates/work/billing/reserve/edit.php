<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var object $reserve */
/** @var object[] $corporations */

$w		= (object) $env->getLanguage()->getSection( 'work/billing', 'reserve-edit' );

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list-alt'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-trash-o'] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' '.$w->buttonCancel, [
	'href'	=> './work/billing/reserve',
	'class'	=> 'btn btn',
] );

$buttonSave	= HtmlTag::create( 'button', $iconSave.' '.$w->buttonSave, [
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
] );

$buttonRemove	= HtmlTag::create( 'a', $iconRemove.' '.$w->buttonRemove, [
	'href'	=> './work/billing/reserve/remove/'.$reserve->reserveId,
	'class'	=> 'btn btn-danger',
] );

$optStatus	= [
	0	=> $w->statusInactive,
	1	=> $w->statusActive,
];
$optStatus	= HtmlElements::Options( $optStatus, $reserve->status );


$optCorporation	= [
	'0'	=> $w->optPersonByShare,
];
foreach( $corporations as $corporation )
	$optCorporation[$corporation->corporationId]	= $corporation->title;
$optCorporation	= HtmlElements::Options( $optCorporation, $reserve->corporationId );

$optPersonalize	= [
	0	=> $w->no_,
	1	=> $w->yes_,
];
$optPersonalize	= HtmlElements::Options( $optPersonalize, $reserve->personalize );

return '
<div class="row-fluid">
	<div class="span9">
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./work/billing/reserve/edit/'.$reserve->reserveId.'" method="post" class="form-changes-auto">
					<div class="row-fluid">
						<div class="span8">
							<label for="input_title">'.$w->labelTitle.'</label>
							<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $reserve->title, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span4">
							<label for="input_status">'.$w->labelStatus.'</label>
							<select name="status" id="input_status" class="span12" required="required">'.$optStatus.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span8">
							<label for="input_corporationId">'.$w->labelTarget.'</label>
							<select name="corporationId" id="input_corporationId" class="span12" onchange="WorkBilling.Reserve.updatePersonalize(this)">'.$optCorporation.'</select>
						</div>
						<div class="span4">
							<label for="input_personalize">'.$w->labelPersonalize.'</label>
							<select name="personalize" id="input_personalize" class="span12" data-old-value="'.$reserve->personalize.'" '.( $reserve->corporationId ? '' : 'readonly="readonly"' ).'>'.$optPersonalize.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span3">
							<label for="input_percent">'.$w->labelPercent.'</label>
							<input type="number" step="0.01" min="0" name="percent" id="input_percent" class="span10 input-number" value="'.number_format( $reserve->percent, 2, '.', '' ).'"/><span class="suffix">%</span>
						</div>
						<div class="span3">
							<label for="input_amount">'.$w->labelAmount.'</label>
							<input type="number" step="0.01" min="0" name="amount" id="input_amount" class="span10 input-number" value="'.number_format( $reserve->amount, 2, '.', '' ).'"/><span class="suffix">&euro;</span>
						</div>
					</div>
					<div class="buttonbar">
						'.$buttonCancel.'
						'.$buttonSave.'
						'.$buttonRemove.'
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script>
</script>';
