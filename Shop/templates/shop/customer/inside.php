<?php
$w			= (object) $words['customer'];
$wDelivery	= (object) $words['customer-delivery'];
$wBilling	= (object) $words['customer-billing'];

if( !$addressDelivery ){
	$address				= $user;
	$address->street		= $user->street;
	$address->institution	= '';
	$address->region		= '';//$user->region;

	$helper		= new View_Helper_Shop_AddressForm( $env );
	$helper->setAddress( $address );
	$helper->setHeading( $wDelivery->heading );
	$helper->setType( 4 );
	$helper->setTextTop( '<div class="alert alert-info">'.$wDelivery->textTop.'<br/></div>' );
	return $helper->render();
}

if( !$addressBilling ){
	$address	= $addressDelivery;
	unset( $address->addressId );

	$helper		= new View_Helper_Shop_AddressForm( $env );
	$helper->setAddress( $address );
	$helper->setHeading( $wBilling->heading );
	$helper->setType( 2 );
	$helper->setTextTop( '<div class="alert alert-info">'.$wBilling->textTop.'<br/></div>' );
	return $helper->render();
}

$helperAddress	= new View_Helper_Shop_AddressView( $env );
$panelDelivery	= '<div class="content-panel">
	<h3>'.$wDelivery->heading.'</h3>
	<div class="content-panel-inner">
		'.$helperAddress->setAddress( $addressDelivery ).'<br/>
		<a href="./shop/customer/address/'.$addressDelivery->addressId.'/4" class="btn btn-small"><i class="fa fa-fw fa-pencil"></i> '.$wDelivery->buttonEdit.'</a>
		<a href="./shop/customer/address/'.$addressDelivery->addressId.'/4/1" class="btn btn-small btn-inverse"><i class="fa fa-fw fa-remove"></i> '.$wDelivery->buttonRemove.'</a>
	</div>
</div>';

$panelBilling	= '<div class="content-panel">
	<h3>'.$wBilling->heading.'</h3>
	<div class="content-panel-inner">
		'.$helperAddress->setAddress( $addressBilling ).'<br/>
		<a href="./shop/customer/address/'.$addressBilling->addressId.'/2" class="btn btn-small"><i class="fa fa-fw fa-pencil"></i> '.$wBilling->buttonEdit.'</a>
		<a href="./shop/customer/address/'.$addressBilling->addressId.'/2/1" class="btn btn-small btn-inverse"><i class="fa fa-fw fa-remove"></i> '.$wBilling->buttonRemove.'</a>
	</div>
</div>';

return '<div class="row-fluid">
	<div class="span6">'.$panelDelivery.'<br/></div>
	<div class="span6">'.$panelBilling.'<br/></div>
</div>
<br/>
<form action="shop/customer" method="post">
	<div class="buttonbar well well-small">
		'.new \CeusMedia\Bootstrap\LinkButton( './shop/cart', $w->buttonToCart, 'not-pull-right', 'fa fa-fw fa-arrow-left' ).'
		'.new \CeusMedia\Bootstrap\SubmitButton( "save", $w->buttonToConditions, 'btn-success not-pull-right', 'fa fa-fw fa-arrow-right' ).'
	</div>
</form>';