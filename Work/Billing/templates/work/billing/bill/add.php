<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list-alt' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' zur Liste', array(
	'href'	=> './work/billing/bill',
	'class'	=> 'btn btn',
) );
$buttonSave	= UI_HTML_Tag::create( 'button', $iconSave.' speichern', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
) );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>Neue Rechnung</h3>
			<div class="content-panel-inner">
				<form action="./work/billing/bill/add" method="post" class="form-changes-auto">
					<div class="row-fluid">
						<div class="span2">
							<label for="input_number">Nummer</label>
							<input type="text" name="number" id="input_number" class="span12" required="required"/>
						</div>
						<div class="span7">
							<label for="input_title">Titel</label>
							<input type="text" name="title" id="input_title" class="span12" value=""/>
						</div>
						<div class="span3">
							<label for="input_dateBooked">gebucht am</label>
							<input type="date" name="dateBooked" id="input_dateBooked" class="span12" required="required" value="'.date( 'Y-m-d' ).'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span3">
							<label for="input_amountNetto">Nettobetrag</label>
							<input type="number" step="0.01" min="0" name="amountNetto" id="input_amountNetto" class="span10 input-number" required="required" oninput="WorkBilling.Bill.updateAmounts(this)"/><span class="suffix">&euro;</span>
						</div>
						<div class="span3">
							<label for="input_amountTaxed">Bruttobetrag</label>
							<input type="number" step="0.01" min="0" name="amountTaxed" id="input_amountTaxed" class="span10 input-number" required="required" oninput="WorkBilling.Bill.updateAmounts(this)"/><span class="suffix">&euro;</span>
						</div>
						<div class="span3">
							<label for="input_taxRate">Steuersatz</label>
							<input type="number" step="0.01" min="0" max="100" name="taxRate" id="input_taxRate" class="span10 input-number" value="19.00" required="required" required="required" autocomplete="off" oninput="WorkBilling.Bill.updateAmounts(this)"/><span class="suffix">%</span>
						</div>
						<div class="span3">
							<label>Mehrwertsteuer</label>
							<input type="number" step="0.01" min="0" max="100" name="tax" id="output_tax" class="span10 input-number" disabled="disabled"/><span class="suffix">&euro;</span>
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
