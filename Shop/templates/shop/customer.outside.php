<?php
$wLogin		= (object) $words['customer-login'];
$wRegister	= (object) $words['customer-register'];

$fieldOauth2	= '';
if( isset( $useOauth2 ) && $useOauth2 ){
	$helper				= new View_Helper_Oauth_ProviderButtons( $this->env );
	if( $helper->count() ){
		$iconUnbind		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
		$helper->setDropdownLabel( 'weitere' );
		$helper->setLinkPath( './auth/oauth2/login/' );
		$helper->setFrom( 'shop/customer' );
		$fieldOauth2	= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'label', 'Anmelden mit' ),
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'div', $helper->render(), array( 'class' => 'span12' ) ),
				), array( 'class' => 'row-fluid' ) ),
				UI_HTML_Tag::create( 'hr', NULL ),
			), array( 'class' => 'span12' ) ),
		), array( 'class' => 'row-fluid' ) );
	}
}

return '<div class="row-fluid">
	<div class="span4 offset1">
		<div class="content-panel">
			<h3>'.$wLogin->heading.'</h3>
			<div class="content-panel-inner">
				<p>'.$wLogin->textTop.'</p>
				<form action="./shop/login" method="post">
					'.$fieldOauth2.'
					<label for="input_login">'.$wLogin->labelLogin.'</label>
					<input type="text" name="login" id="input_login" class="span10" value="'.htmlentities( $login, ENT_QUOTES, 'UTF-8' ).'"/>
					<label for="input_password">'.$wLogin->labelPassword.'</label>
					<input type="password" name="password" id="input_password" class="span10"/>
					<div class="buttonbar">
						<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-sign-in"></i> '.$wLogin->buttonLogin.'</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="span4 offset1">
		<div class="content-panel">
			<h3>'.$wRegister->heading.'</h3>
			<div class="content-panel-inner">
				<p>'.$wRegister->textTop.'</p>
						<a href="./auth/register?from=shop/customer" class="btn btn-primary"><i class="fa fa-fw fa-pencil"></i> '.$wRegister->buttonRegister.'</a>
				</form>
			</div>
		</div>
	</div>
</div>';

?>
