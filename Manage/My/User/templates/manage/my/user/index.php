<?php
$w			= (object) $words['index'];

/* TO BE USED LATER FOR STATUS INFO
$indicator	= new UI_HTML_Indicator();
$indicator->setIndicatorClass( 'indicator-small' );
$ind1		= $indicator->build( 75, 100 );
*/

$panelInfo		= $view->loadTemplateFile( 'manage/my/user/index.info.php' );
$panelPassword	= $view->loadTemplateFile( 'manage/my/user/index.password.php' );
$panelEmail		= $view->loadTemplateFile( 'manage/my/user/index.email.php' );
$panelUsername	= $view->loadTemplateFile( 'manage/my/user/index.username.php' );
$panelEdit		= $view->loadTemplateFile( 'manage/my/user/index.edit.php' );
$panelAccount	= $view->loadTemplateFile( 'manage/my/user/index.account.php' );

$tabs	= View_Manage_My_User::renderTabs( $env );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/manage/my/user/' ) );

return $textTop.$tabs.
HTML::DivClass( 'row-fluid', array(
	HTML::DivClass( 'span8', array(
		$panelAccount,
	) ),
	HTML::DivClass( 'span4', array(
		$panelInfo
	) )
) ).
HTML::DivClass( 'row-fluid', array(
	HTML::DivClass( 'span8', array(
		$panelEdit,
	) )
) ).
HTML::DivClass( 'row-fluid', array(
	HTML::DivClass( 'span3',
		$panelPassword
	),
	HTML::DivClass( 'span5',
		$panelEmail
	),
	HTML::DivClass( 'span4',
		$panelUsername
	)
) ).$textBottom;
?>
