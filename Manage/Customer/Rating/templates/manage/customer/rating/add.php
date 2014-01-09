<?php
return '
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css" />
<script src="http://code.jquery.com/ui/1.10.1/jquery-ui.js"></script>
<link rel="stylesheet" href="/resources/demos/style.css" />
<style>
.pull {
	position: absolute;
	top: -10px;
	left: 290px;
	width: 20px !important;
	}
div.span3 {
	position: relative;
	}
</style>
<script>
$(document).ready(function(){
	$(".slider").each(function(){
		var input = $(this).parent().find("input").addClass("pull");
		$(this).slider({
			value: 0,
			min: 0.9,
			max: 5,
			step: 0.1,
			slide: function( event, ui ){
				var color = "rgb(255,255,255)";
				if(ui.value >= 1){
					input.val(ui.value);
					color = calculateColor((ui.value - 1) / 4);
					if($(this).hasClass("inverse"))
						color = calculateColor(Math.abs(5 - ui.value) / 4);
				}
				else
					input.val("");
				$(this).css("background", color);
			}
		});
		input.val("");
	});
});
</script>




<h3>Kunde bewerten: <span class="muted">'.$customer->title.'</span></h3>
<form action="./manage/customer/rating/add/'.$customerId.'" method="post" class="form-inline">
	<div class="row">
		<div class="span3">
			<label>Umgänglichkeit</label>
		</div>
		<div class="span3">
			<input type="text" class="span1" name="affability" id="input_affability" value=""/>
			<div class="slider"></div>
		</div>
	</div>
	<hr/>
	<div class="row">
		<div class="span3">
			<label>Beratbarkeit</label>
		</div>
		<div class="span3">
			<input type="text" class="span1" name="guidability" id="input_guidability" value=""/>
			<div class="slider"></div>
		</div>
	</div>
	<hr/>
	<div class="row">
		<div class="span3">
			<label>Wachstumschancen</label>
		</div>
		<div class="span3">
			<input type="text" class="span1" name="growthRate" id="input_growthRate" value=""/>
			<div class="slider"></div>
		</div>
	</div>
	<hr/>
	<div class="row">
		<div class="span3">
			<label>Rentabilität</label>
		</div>
		<div class="span3">
			<input type="text" class="span1" name="profitability" id="input_profitability" value=""/>
			<div class="slider"></div>
		</div>
	</div>
	<hr/>
	<div class="row">
		<div class="span3">
			<label>Zahlungsmoral</label>
		</div>
		<div class="span3">
			<input type="text" class="span1" name="paymentMoral" id="input_paymentMoral" value=""/>
			<div class="slider"></div>
		</div>
	</div>
	<hr/>
	<div class="row">
		<div class="span3">
			<label>Termintreue</label>
		</div>
		<div class="span3">
			<input type="text" class="span1" name="adherence" id="input_adherence" value=""/>
			<div class="slider"></div>
		</div>
	</div>
	<hr/>
	<div class="row">
		<div class="span3">
			<label>Nervfaktor, Verspanntheit</label>
		</div>
		<div class="span3">
			<input type="text" class="span1" name="uptightness" id="input_uptightness" value=""/>
			<div class="slider inverse"></div>
		</div>
	</div>
	<hr/>
	<div class="buttonbar">
		<button type="button" class="btn btn-small" onclick="document.location.href=\'./manage/customer/view/'.$customer->customerId.'\';"><i class="icon-arrow-left"></i> zurück</button>
		<button type="submit" class="btn btn-small btn-primary" name="save"><i class="icon-ok icon-white"></i> speichern</button>
	</div>
</form>';
?>
