<?php
$w		= (object) $words['cart'];

$tablePositions	= '<p><em class="muted">'.$w->empty.'</em></p>';
if( count( $positions ) ){
	$helperCart	= new View_Helper_Shop_CartPositions( $env );
	$helperCart->setPositions( $positions );
	$helperCart->setChangeable( TRUE );
	$tablePositions	= $helperCart->render();
}

$tabContent	= '
	<h3>'.$w->heading.'</h3>
	'.$tablePositions.'
	<div class="buttonbar well well-small">
		'.new \CeusMedia\Bootstrap\LinkButton( './shop/customer', $w->buttonToCustomer, 'btn-success not-pull-right', 'arrow-right', !$positions ).'
	</div>
';

$helperTabs		= new View_Helper_Shop_Tabs( $env );
$helperTabs->setCurrent( 'shop-cart' );
$helperTabs->setContent( $tabContent );
$helperTabs->setPaymentBackends( $this->getData( 'paymentBackends' ) );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/shop/' ) );

return $textTop.$helperTabs->render().$textBottom;
?>
