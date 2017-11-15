<?php


$panelUser			= $view->loadTemplateFile( 'admin/payment/mangopay/seller/panelUser.php' );
//$panelHeadquarter	= $view->loadTemplateFile( 'admin/payment/mangopay/seller/panelHeadquarter.php' );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		$panelUser,
	), array( 'class' => 'span6' ) ),
	UI_HTML_Tag::create( 'div', array(
		print_m( $sellerUser, NULL, NULL, TRUE ),
	), array( 'class' => 'span6' ) ),
), array( 'class' => 'row-fluid' ) );
