<?php
$w			= (object) $words['conditions'];
extract( $view->populateTexts( array( 'conditions' ), 'html/shop/' ) );

$tabContent	= '
<form action="./shop/conditions" method="post">
	'.$textConditions.'
	<br/>
	<h4>Geschäftsbedingungen</h4>
	<div class="alert alert-success">
		<div style="float: left; width: 30px;">
			<div class="pull-right" style="padding-top: 7px">
				<input type="checkbox" name="accept_rules" value="1" id="input_accept_rules" '.( $cart->get( 'acceptRules' ) ? ' checked="checked"' : '' ).'>
			</div>
		</div>
		<div style="margin-left: 50px;">
			<label for="input_accept_rules">
				'.$w->labelAcceptRules.'
			</label>
		</div>
		<div class="clearfloat"></div>
	</div>
	<div class="buttonbar well well-small">
		'.new \CeusMedia\Bootstrap\LinkButton( './shop/customer', $w->buttonToCustomer, 'not-pull-right', 'fa fa-fw fa-arrow-left' ).'
		'.new \CeusMedia\Bootstrap\SubmitButton( "saveConditions", $w->buttonNext, 'btn-success not-pull-right', 'fa fa-fw fa-arrow-right' ).'
	</div>
</form>';

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/shop/' ) );

$helperTabs		= new View_Helper_Shop_Tabs( $env );
$helperTabs->setCurrent( 'shop-conditions' );
$helperTabs->setContent( $tabContent );
$helperTabs->setCartTotal( $cartTotal );
$helperTabs->setPaymentBackends( $this->getData( 'paymentBackends' ) );

return $textTop.$helperTabs->render().$textBottom;
