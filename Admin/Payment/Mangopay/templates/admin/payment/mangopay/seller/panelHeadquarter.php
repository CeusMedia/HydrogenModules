<?php

if( !$sellerUser->Id || empty( $sellerUser->HeadquartersAddress ) )
	return;

$w	= (object) $words['panel-headquarter'];

$form	= '<form action="./admin/payment/mangopay/client/edit" method="post">
	<div class="row-fluid">
		<div class="span12">
			<label for="input_headquarter_address">'.$w->labelHeadquarterAddress.'</label>
			<input type="text" name="headquarter[address]" id="input_headquarter_address" class="span12" value="'.htmlentities( $sellerUser->HeadquartersAddress->AddressLine1, ENT_QUOTES, 'UTF-8' ).'">
		</div>
	</div>
	<div class="row-fluid">
		<div class="span4">
			<label for="input_headquarter_postcode">'.$w->labelHeadquarterPostcode.'</label>
			<input type="text" name="headquarter[postcode]" id="input_headquarter_postcode" class="span12" value="'.htmlentities( $sellerUser->HeadquartersAddress->PostalCode, ENT_QUOTES, 'UTF-8' ).'">
		</div>
		<div class="span8">
			<label for="input_headquarter_city">'.$w->labelHeadquarterCity.'</label>
			<input type="text" name="headquarter[city]" id="input_headquarter_city" class="span12" value="'.htmlentities( $sellerUser->HeadquartersAddress->City, ENT_QUOTES, 'UTF-8' ).'">
		</div>
	</div>
	<div class="row-fluid">
		<div class="span6">
			<label for="input_headquarter_country">'.$w->labelHeadquarterCountry.'</label>
			<input type="text" name="headquarter[country]" id="input_headquarter_country" class="span12" value="'.htmlentities( $sellerUser->HeadquartersAddress->Country, ENT_QUOTES, 'UTF-8' ).'">
		</div>
		<div class="span6">
			<label for="input_headquarter_region">'.$w->labelHeadquarterRegion.'</label>
			<input type="text" name="headquarter[region]" id="input_headquarter_region" class="span12" value="'.htmlentities( $sellerUser->HeadquartersAddress->Region, ENT_QUOTES, 'UTF-8' ).'">
		</div>
	</div>
	<div class="buttonbar">
		<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i>&nbsp;'.$w->buttonSave.'</button>
	</div>
</form>';

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', $w->heading ),
	UI_HTML_Tag::create( 'div', array(
		$form,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );