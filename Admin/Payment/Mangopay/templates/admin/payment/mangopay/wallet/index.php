<?php

$panelClientWallets	= $view->loadTemplateFile( 'admin/payment/mangopay/wallet/panelClientWallets.php' );

//$list	= UI_HTML_Tag::create( 'div', 'Plattform-Konto noch nicht oder unvollständig eingerichtet.' );

$panelPlatformWallets	= '';
if( $projectUserId && $projectUser ){
	if( $projectWalletId ){
		if( $projectWallets ){
			$panelPlatformWallets	= $view->loadTemplateFile( 'admin/payment/mangopay/wallet/panelProjectWallets.php' );
		}
	}
}

$tabs	= View_Admin_Payment_Mangopay::renderTabs( $env, 'wallet' );

return $tabs.'<div class="row-fluid">
	<div class="span6">
		'.$panelClientWallets.'
	</div>
	<div class="span6">
		'.$panelPlatformWallets.'
	</div>
</div>';
