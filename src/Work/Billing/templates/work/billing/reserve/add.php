<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var object[] $corporations */

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list-alt'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' zur Liste', [
	'href'	=> './work/billing/reserve',
	'class'	=> 'btn btn',
] );

$buttonSave	= HtmlTag::create( 'button', $iconSave.' speichern', [
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
] );

$optStatus	= [
	0	=> 'inaktiv',
	1	=> 'aktiv',
];
$optStatus	= HtmlElements::Options( $optStatus, 1 );

$optCorporation	= [
	'0'	=> '- Person per Anteil -',
];
foreach( $corporations as $corporation )
	$optCorporation[$corporation->corporationId]	= $corporation->title;
$optCorporation	= HtmlElements::Options( $optCorporation );

$optPersonalize	= [
	0	=> 'nein',
	1	=> 'ja',
];
$optPersonalize	= HtmlElements::Options( $optPersonalize, "1" );

return '
<div class="row-fluid">
	<div class="span9">
		<div class="content-panel">
			<h3>Neue Rücklage</h3>
			<div class="content-panel-inner">
				<form action="./work/billing/reserve/add" method="post" class="form-changes-auto">
					<div class="row-fluid">
						<div class="span8">
							<label for="input_title">Bezeichnung</label>
							<input type="text" name="title" id="input_title" class="span12" required="required"/>
						</div>
						<div class="span4">
							<label for="input_status">Zustand</label>
							<select name="status" id="input_status" class="span12" required="required">'.$optStatus.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span8">
							<label for="input_corporationId">Zielkonto</label>
							<select name="corporationId" id="input_corporationId" class="span12" data-old-value="0" onchange="WorkBilling.Reserve.updatePersonalize(this)">'.$optCorporation.'</select>
						</div>
						<div class="span4">
							<label for="input_personalize">personalisieren</label>
							<select name="personalize" id="input_personalize" class="span12" data-old-value="0" readonly="readonly">'.$optPersonalize.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span3">
							<label for="input_percent"><small class="muted">entweder</small> Prozent</label>
							<input type="text" name="percent" id="input_percent" class="span10 input-number" data-min-value="0" data-max-value="50" data-max-precision="2"/><span class="suffix">%</span>
						</div>
						<div class="span3">
							<label for="input_amount"><small class="muted">oder</small> Betrag</label>
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
