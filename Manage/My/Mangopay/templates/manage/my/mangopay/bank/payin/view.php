<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconList		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconSave		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconPrint		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-print' ) );

$helperMoney	= new View_Helper_Mangopay_Entity_Money( $env );
$helperMoney->setFormat( View_Helper_Mangopay_Entity_Money::FORMAT_AMOUNT_SPACE_CURRENCY );
$helperMoney->setNumberFormat( View_Helper_Mangopay_Entity_Money::NUMBER_FORMAT_COMMA );
$helperMoney->set( $payin->PaymentDetails->DeclaredDebitedFunds );

$helperIBAN	= new View_Helper_Mangopay_Entity_IBAN( $env );
$helperBIC	= new View_Helper_Mangopay_Entity_BIC( $env );

$helperUrl	= new \View_Helper_Mangopay_URL( $env );
$helperUrl->set( ( isset( $from ) && $from ) ? $from :  'manage/my/mangopay/bank/view/'.$bankAccountId );
$helperUrl->setBackwardTo( TRUE );
$helperUrl->setForwardTo( TRUE );
$helperUrl->setFrom( TRUE );
$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' zurück', array(
	'href'	=> $helperUrl->render(),
	'class'	=> 'btn',
) );

$buttonPrint	= HtmlTag::create( 'a', $iconPrint.' drucken', array(
	'type'		=> 'button',
	'class'		=> 'btn btn-info',
	'onclick'	=> 'window.print()',
) );

return '
<div class="content-panel" id="panel-mangopay-bank-payin">
	<h3>Bankeinzahlung: Auftrag</h3>
	<div class="content-panel-inner">
		<dl class="dl-horizontal">
			<dt>Kontoinhaber</dt>
			<dd>'.$payin->PaymentDetails->BankAccount->OwnerName.'</dd>
			<dt>IBAN</dt>
			<dd>'.$helperIBAN->set( $payin->PaymentDetails->BankAccount->Details->IBAN ).'</dd>
			<dt>BIC</dt>
			<dd>'.$helperBIC->set( $payin->PaymentDetails->BankAccount->Details->BIC ).'</dd>
			<dt>Betrag</dt>
			<dd>'.$helperMoney.'</dd>
			<dt>Referenz</dt>
			<dd>'.$payin->PaymentDetails->WireReference.'</dd>
		</dl>
		<div class="buttonbar">
			'.$buttonCancel.'
			'.$buttonPrint.'
		</div>
	</div>
</div>';
