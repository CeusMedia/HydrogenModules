<?php

$buttonCancel	= UI_HTML_Tag::create( 'a', 'zur Liste', array(
	'href'	=> './work/billing/bill',
	'class'	=> 'btn btn',
) );
$buttonSave	= UI_HTML_Tag::create( 'button', 'speichern', array(
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
							<input type="text" name="amountNetto" id="input_amountNetto" class="span10 input-number" data-max-precision="2" required="required" onkeyup="WorkBilling.Bill.updateAmounts(this)"/><span class="suffix">&euro;</span>
						</div>
						<div class="span3">
							<label for="input_amountTaxed">Bruttobetrag</label>
							<input type="text" name="amountTaxed" id="input_amountTaxed" class="span10 input-number" data-max-precision="2" required="required" onkeyup="WorkBilling.Bill.updateAmounts(this)"/><span class="suffix">&euro;</span>
						</div>
						<div class="span3">
							<label for="input_taxRate">Steuersatz</label>
							<input type="text" name="taxRate" id="input_taxRate" class="span10 input-number" required="required" value="19" data-min-value="0" data-max-value="100" data-max-precision="2" required="required" onkeyup="WorkBilling.Bill.updateAmounts(this)"/><span class="suffix">%</span>
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
