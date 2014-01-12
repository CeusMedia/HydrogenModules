<?php
return '
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css" />
<script src="http://code.jquery.com/ui/1.10.1/jquery-ui.js"></script>
<link rel="stylesheet" href="/resources/demos/style.css" />
<style>
input.rate {
	float: left;
	margin-top: -10px;
	margin-left: 10px !important;
	}
</style>
<script>
$(document).ready(function(){
	ManageCustomerRating.initSliders(".slider");
});
</script>


<h3>Kunde bewerten: <span class="muted">'.$customer->title.'</span></h3>
<form action="./manage/customer/rating/add/'.$customerId.'" method="post" class="form-inline">
	<div class="row-fluid">
		<div class="span3">
			<label>Umgänglichkeit</label>
		</div>
		<div class="span1">
			<input type="text" class="span9 rate" name="affability" id="input_affability" value="" tabindex="1" required="required"/>
		</div>
		<div class="span3">
			<div class="slider"></div>
		</div>
	</div>
	<hr/>
	<div class="row-fluid">
		<div class="span3">
			<label>Beratbarkeit</label>
		</div>
		<div class="span1">
			<input type="text" class="span9 rate" name="guidability" id="input_guidability" value="" tabindex="2" required="required"/>
		</div>
		<div class="span3">
			<div class="slider"></div>
		</div>
	</div>
	<hr/>
	<div class="row-fluid">
		<div class="span3">
			<label>Wachstumschancen</label>
		</div>
		<div class="span1">
			<input type="text" class="span9 rate" name="growthRate" id="input_growthRate" value="" tabindex="3" required="required"/>
		</div>
		<div class="span3">
			<div class="slider"></div>
		</div>
	</div>
	<hr/>
	<div class="row-fluid">
		<div class="span3">
			<label>Rentabilität</label>
		</div>
		<div class="span1">
			<input type="text" class="span9 rate" name="profitability" id="input_profitability" value="" tabindex="4" required="required"/>
		</div>
		<div class="span3">
			<div class="slider"></div>
		</div>
	</div>
	<hr/>
	<div class="row-fluid">
		<div class="span3">
			<label>Zahlungsmoral</label>
		</div>
		<div class="span1">
			<input type="text" class="span9 rate" name="paymentMoral" id="input_paymentMoral" value="" tabindex="5" required="required"/>
		</div>
		<div class="span3">
			<div class="slider"></div>
		</div>
	</div>
	<hr/>
	<div class="row-fluid">
		<div class="span3">
			<label>Termintreue</label>
		</div>
		<div class="span1">
			<input type="text" class="span9 rate" name="adherence" id="input_adherence" value="" tabindex="6" required="required"/>
		</div>
		<div class="span3">
			<div class="slider"></div>
		</div>
	</div>
	<hr/>
	<div class="row-fluid">
		<div class="span3">
			<label>Nervfaktor, Verspanntheit</label>
		</div>
		<div class="span1">
			<input type="text" class="span9 rate" name="uptightness" id="input_uptightness" value="" tabindex="6" required="required"/>
		</div>
		<div class="span3">
			<div class="slider inverse"></div>
		</div>
	</div>
	<hr/>
	<div class="buttonbar">
		<button type="button" class="btn btn-small" onclick="document.location.href=\'./manage/customer/edit/'.$customer->customerId.'\';"><i class="icon-arrow-left"></i> zurück</button>
		<button type="submit" class="btn btn-small btn-primary" name="save"><i class="icon-ok icon-white"></i> speichern</button>
	</div>
</form>';
?>
