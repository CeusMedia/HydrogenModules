<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconList		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconPayin		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-sign-in' ) );
$iconPayout		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-sign-out' ) );

$buttonPayIn	= '<a href="./manage/my/mangopay/bank/payin/'.$bankAccountId.'" class="btn">'.$iconPayin.' Einzahlung</a>';
$buttonPayOut	= '<a href="./manage/my/mangopay/bank/payOut/'.$bankAccountId.'" class="btn">'.$iconPayout.' Auszahlung</a>';
$linkBack		= './'.( $backwardTo ? $backwardTo : 'manage/my/mangopay/bank' );

$inputAddressLine2	= '';
if( strlen( trim( $bankAccount->OwnerAddress->AddressLine2 ) ) ){
	$inputAddressLine2	= '
		<div class="row-fluid">
			<div class="span12">
				<label>Address Line 2</label>
				<div class="span12 value">'.htmlentities( $bankAccount->OwnerAddress->AddressLine2, ENT_QUOTES, 'UTF-8' ).'</div>
			</div>
		</div>';
}

return '
<div class="content-panel panel-mangopay-view">
	<h3><i class="fa fa-fw fa-bank"></i> Bankkonto</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span7">
				<label>Kontoinhaber</label>
				<div class="value autocut">'.htmlentities( $bankAccount->OwnerName, ENT_QUOTES, 'UTF-8' ).'</div>
			</div>
			<div class="span5">
				<label>registriert vor</label>
				<div class="value autocut">'.View_Helper_TimePhraser::convertStatic( $env, $bankAccount->CreationDate, TRUE ).'</div>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span7">
				<label>IBAN</label>
				<div class="value autocut">'.htmlentities( $bankAccount->Details->IBAN, ENT_QUOTES, 'UTF-8' ).'</div>
			</div>
			<div class="span5">
				<label>BIC</label>
				<div class="value autocut">'.htmlentities( $bankAccount->Details->BIC, ENT_QUOTES, 'UTF-8' ).'</div>
			</div>
		</div>
<!--		<div class="row-fluid">
			<div class="span3">
				<label>Typ</label>
				<div class="value autocut">'.htmlentities( $bankAccount->Type, ENT_QUOTES, 'UTF-8' ).'</div>
			</div>
			<div class="span4">
				<label>ID</label>
				<div class="value autocut">'.htmlentities( $bankAccount->Id, ENT_QUOTES, 'UTF-8' ).'</div>
			</div>
			<div class="span5">
				<label>Tag</label>
				<div class="value autocut">'.htmlentities( $bankAccount->Tag, ENT_QUOTES, 'UTF-8' ).'</div>
			</div>
		</div>-->
<!--		<h4>Address</h4>
		<div class="row-fluid">
			<div class="span7">
				<label>Country</label>
				<div class="value autocut">'.htmlentities( $bankAccount->OwnerAddress->Country, ENT_QUOTES, 'UTF-8' ).'</div>
			</div>
			<div class="span5">
				<label>Region</label>
				<div class="value autocut">'.htmlentities( $bankAccount->OwnerAddress->Region, ENT_QUOTES, 'UTF-8' ).'</div>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span4">
				<label>Postal Code</label>
				<div class="value autocut">'.htmlentities( $bankAccount->OwnerAddress->PostalCode, ENT_QUOTES, 'UTF-8' ).'</div>
			</div>
			<div class="span8">
				<label>City</label>
				<div class="value autocut">'.htmlentities( $bankAccount->OwnerAddress->City, ENT_QUOTES, 'UTF-8' ).'</div>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<label>Address Line 1</label>
				<div class="value autocut">'.htmlentities( $bankAccount->OwnerAddress->AddressLine1, ENT_QUOTES, 'UTF-8' ).'</div>
			</div>
		</div>
		'.$inputAddressLine2.'-->
<!--		'.print_m( $bankAccount, NULL, NULL, TRUE ).'-->
		<div class="buttonbar">
			<a href="'.$linkBack.'" class="btn">'.$iconList.' zurück zur Liste</a>
			'.$buttonPayIn.'
			'.$buttonPayOut.'
		</div>
	</div>
</div>';
