<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */

$w	= (object) $words['panel-company'];

$form	= '<form action="./admin/payment/mangopay/client/edit" method="post">
	<div class="row-fluid">
		<div class="span4">
			<label for="input_clientId">'.$w->labelClientId.'</label>
			<input type="text" name="clientId" id="input_clientId" class="span12" disabled="disabled" value="'.htmlentities( $client->ClientId, ENT_QUOTES, 'UTF-8' ).'">
		</div>
		<div class="span8">
			<label for="input_name">'.$w->labelName.'</label>
			<input type="text" name="name" id="input_name" class="span12" disabled="disabled" value="'.htmlentities( $client->Name, ENT_QUOTES, 'UTF-8' ).'">
		</div>
	</div>
	<div class="row-fluid">
		<div class="span4">
			<label for="input_platformType">'.$w->labelPlatformType.'</label>
			<input type="text" name="platformType" id="input_platformType" class="span12" disabled="disabled" value="'.htmlentities( $client->PlatformCategorization->BusinessType, ENT_QUOTES, 'UTF-8' ).'">
		</div>
		<div class="span8">
			<label for="input_platformUrl">'.$w->labelPlatformUrl.'</label>
			<input type="text" name="platformUrl" id="input_platformUrl" class="span12" value="'.htmlentities( $client->PlatformURL, ENT_QUOTES, 'UTF-8' ).'">
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label for="input_platformDescription">'.$w->labelPlatformDescription.'</label>
			<input type="text" name="platformDescription" id="input_platformDescription" class="span12" value="'.htmlentities( $client->PlatformDescription, ENT_QUOTES, 'UTF-8' ).'">
		</div>
	</div>
	<div class="row-fluid">
		<div class="span4">
			<label for="input_taxNumber">'.$w->labelTaxNumber.'</label>
			<input type="text" name="taxNumber" id="input_taxNumber" class="span12" value="'.htmlentities( $client->TaxNumber, ENT_QUOTES, 'UTF-8' ).'">
		</div>
	</div>
	<div class="buttonbar">
		<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i>&nbsp;'.$w->buttonSave.'</button>
	</div>
</form>';

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $w->heading ),
	HtmlTag::create( 'div', array(
		$form,
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
