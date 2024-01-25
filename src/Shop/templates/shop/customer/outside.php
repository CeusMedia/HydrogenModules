<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var \CeusMedia\HydrogenFramework\Environment $env */
/** @var array $words */

$wLogin		= (object) $words['customer-login'];
$wRegister	= (object) $words['customer-register'];
$wGuest		= (object) $words['customer-guest'];

$fieldOauth2	= '';
if( isset( $useOauth2 ) && $useOauth2 ){
	$helper				= new View_Helper_Oauth_ProviderButtons( $env );
	if( $helper->count() ){
		$iconUnbind		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
		$helper->setDropdownLabel( 'weitere' );
		$helper->setLinkPath( './auth/oauth2/login/' );
		$helper->setFrom( 'shop/customer' );
		$fieldOauth2	= HtmlTag::create( 'div', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'label', 'Anmelden mit' ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'div', $helper->render(), ['class' => 'span12'] ),
				), ['class' => 'row-fluid'] ),
				HtmlTag::create( 'hr', NULL ),
			), ['class' => 'span12'] ),
		), ['class' => 'row-fluid'] );
	}
}

return '<div class="row-fluid">
	<div class="span5 offset0">
		<div class="content-panel">
			<h3>'.$wLogin->heading.'</h3>
			<div class="content-panel-inner">
				<p>'.$wLogin->textTop.'</p>
				<form action="./auth/local/login?from=shop/customer" method="post">
					'.$fieldOauth2.'
					<label for="input_login_username">'.$wLogin->labelUsername.'</label>
					<input type="text" name="login_username" id="input_login_username" class="span10" value="'.htmlentities( $username ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
					<label for="input_login_password">'.$wLogin->labelPassword.'</label>
					<input type="password" name="login_password" id="input_login_password" class="span10"/>
					<div class="buttonbar">
						<button type="submit" name="doLogin" class="btn btn"><i class="fa fa-fw fa-sign-in"></i> '.$wLogin->buttonLogin.'</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="span6 offset1">
		<div class="content-panel">
			<h3>'.$wRegister->heading.'</h3>
			<div class="content-panel-inner">
				<p>'.$wRegister->textTop.'</p>
				<a href="./auth/register?from=shop/customer" class="btn btn-success"><i class="fa fa-fw fa-pencil"></i> '.$wRegister->buttonRegister.'</a>
			</div>
		</div>
		<div class="content-panel">
			<h3>'.$wGuest->heading.'</h3>
			<div class="content-panel-inner">
				<p>
					'.$wGuest->textTop.'
				</p>
				<a href="shop/customer/guest" class="btn btn"><i class="fa fa-fw fa-arrow-right"></i> '.$wGuest->button.'</a>
			</div>
		</div>
	</div>
</div>';
