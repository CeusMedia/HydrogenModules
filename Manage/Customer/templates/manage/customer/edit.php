<?php
$indicator1	= new UI_HTML_Indicator();
$indicator2	= new UI_HTML_Indicator( array( 'invertColor' => TRUE ) );

foreach( $customer->ratings as $rating ){
	$rows[]	= '<tr>
		<td>'.date( 'Y-m-d', $rating->timestamp ).'</td>
		<td>'.number_format( $rating->index, 1 ).'</td>
		<td>'.( $rating->index ? $indicator1->build( abs( 5 - $rating->index ) + 0.5, 4.5 ) : '' ).'</td>
		<td data-value="'.$rating->affability.'"></td>
		<td data-value="'.$rating->guidability.'"></td>
		<td data-value="'.$rating->growthRate.'"></td>
		<td data-value="'.$rating->profitability.'"></td>
		<td data-value="'.$rating->paymentMoral.'"></td>
		<td data-value="'.$rating->adherence.'"></td>
		<td data-value="'.$rating->uptightness.'" data-inverse="yes"></td>
	</tr>';
}

$heads	= '';

return '
<script>
$(document).ready(function(){
	$("table td").each(function(){
		var color;
		var cell = $(this);
		if(cell.data("value") > 0){
			color = calculateColor((cell.data("value") - 1) / 4, 63);
			if(cell.data("inverse"))
				color = calculateColor(Math.abs(5 - cell.data("value")) / 4, 63);
			cell.css("background-color", color);
			cell.addClass("colored");
		}
	});
	
});

</script>
<style>

table {
	width: 100%;
	min-width: 800px;
	max-width: 1250px;
	box-shadow: 0px 0px 0px 1px gray;
	}

table th {
	background-color: #EEE;
	border-bottom: 1px solid #BBB;
	}
table td.colored {
	text-align: center;
	color: white;
	text-shadow: 1px 1px 2px black;
	border-color: #FFF;
	}

</style>
<h3>Kunde</h3>
<div class="row-fluid">
	<div class="span9">
		<form action="./manage/customer/edit/'.$customerId.'" method="post">
			<div class="row-fluid">
				<div class="span6">
					<label for="input_title">Firmenname</label>
					<input type="text" id="input_title" name="title" class="span12" value="'.htmlentities( $customer->title ).'"/>
				</div>
				<div class="span3">
					<label for="input_type">Type</label>
					<select id="input_type" name="type" class="span12">
						<option key="0">A-Kunde</option>
						<option key="1">B-Kunde</option>
						<option key="2">C-Kunde</option>
					</select>
				</div>
				<div class="span3">
					<label for="input_size">Size</label>
					<select id="input_size" name="size" class="span12">
					</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_description">Beschreibung</label>
					<textarea id="input_description" name="description" class="span12" rows="5">'.htmlentities( $customer->description ).'</textarea>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_country">Land</label>
					<input type="text" id="input_country" name="country" class="span12" value="'.htmlentities( $customer->country ).'"/>
				</div>
				<div class="span3">
					<label for="input_city">Stadt</label>
					<input type="text" id="input_city" name="city" class="span12" value="'.htmlentities( $customer->city ).'"/>
				</div>
				<div class="span1">
					<label for="input_postcode">PLZ</label>
					<input type="text" id="input_postcode" name="postcode" class="span12" value="'.htmlentities( $customer->postcode ).'"/>
				</div>
				<div class="span4">
					<label for="input_street">Street</label>
					<input type="text" id="input_street" name="street" class="span12" value="'.htmlentities( $customer->street ).'"/>
				</div>
				<div class="span1">
					<label for="input_nr">Nr</label>
					<input type="text" id="input_nr" name="nr" class="span12" value="'.htmlentities( $customer->nr ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_url">URL</label>
					<input type="text" id="input_url" name="url" class="span12" value="'.htmlentities( $customer->url ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_contact">Ansprachpartner</label>
					<input type="text" id="input_contact" name="contact" class="span12" value="'.htmlentities( $customer->contact ).'"/>
				</div>
				<div class="span4">
					<label for="input_email">E-Mail-Adresse</label>
					<input type="text" id="input_email" name="email" class="span12" value="'.htmlentities( $customer->email ).'"/>
				</div>
				<div class="span2">
					<label for="input_phone">Telefon</label>
					<input type="text" id="input_phone" name="phone" class="span12" value="'.htmlentities( $customer->phone ).'"/>
				</div>
				<div class="span2">
					<label for="input_fax">Fax</label>
					<input type="text" id="input_fax" name="fax" class="span12" value="'.htmlentities( $customer->fax ).'"/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="button" class="btn btn-small" onclick="document.location.href=\'./manage/customer\';"><i class="icon-arrow-left"></i> zurück</button>
				<button type="submit" class="btn btn-small btn-success" name="save"><i class="icon-ok icon-white"></i> speichern</button>
				<button type="button" class="btn btn-small btn-danger" onclick="document.location.href=\'./manage/customer/remove/'.$customer->customerId.'\';"><i class="icon-plus icon-white"></i> entfernen</button>
			</div>
		</form>
	</div>
	<div class="span3">
		<h4>Bewertung</h4>
		<dl class="dl-horizontal">
			<dt>letzter Index</dt>
			<dd><small class="muted">noch nicht implementiert</small></dd>
			<dt>Index-&empty;</dt>
			<dd>'.$customer->index.'</dd>
			<dt>Varianz</dt>
			<dd>'.number_format( $customer->variance, 1 ).'</dd>
			<dt>Tendenz</dt>
			<dd><small class="muted">noch nicht implementiert</small></dd>
		</dl>
		<button type="button" class="btn btn-small btn-primary" onclick="document.location.href=\'./manage/customer/rating/add/'.$customer->customerId.'\';"><i class="icon-plus icon-white"></i> bewerten</button>
	</div>
</div>


<br/>
<h3>Bewertungen</h3>
<table class="table">
	<colgroup>
		<col width="13%"/>
		<col width="4%"/>
		<col width="13%"/>
		<col width="10%"/>
		<col width="10%"/>
		<col width="10%"/>
		<col width="10%"/>
		<col width="10%"/>
		<col width="10%"/>
		<col width="10%"/>
	</colgroup>
	<thead>
		<tr>
		<th>Datum</th>
		<th>&empty;</th>
		<th>Index</th>
		<th><small class="-muted">Umgänglichkeit</small></th>
		<th><small class="-muted">Beratbarkeit</small></th>
		<th><small class="-muted">Wachstum</small></th>
		<th><small class="-muted">Rentabilität</small></th>
		<th><small class="-muted">Zahlungsmoral</small></th>
		<th><small class="-muted">Termintreue</small></th>
		<th><small class="-muted">Nervfaktor</small></th>
	</tr>
	</thead>
	<tbody>
		'.join( $rows ).'
	</tbody>
</table>

<br/>
';

?>
