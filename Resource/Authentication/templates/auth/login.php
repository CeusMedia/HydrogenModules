<?php

$list	= array();
foreach( $backends as $backend ){
	$list[]	= UI_HTML_Tag::create( 'a', $backend->label, array(
		'href'	=> './auth/'.$backend->path.'/login',
		'class'	=> 'btn btn-primary'
	) );
}
$list	= UI_HTML_Tag::create( 'div', $list, array( 'class' => 'btn-bar' ) );

return $list;
/*
$panelLogin	= $view->loadTemplateFile( 'auth/login.form.php' );

extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth/login/', array( 'from' => $from ) ) );

return HTML::DivClass( "auth-login-text-top", $textTop ).
HTML::DivClass( "row-fluid", array(
	HTML::DivClass( "span4",
		$panelLogin
	),
	HTML::DivClass( "span8",
		HTML::DivClass( "auth-login-text-info", $textInfo )
	)
) ).
HTML::DivClass( "auth-login-text-bottom", $textBottom );
*/
?>
