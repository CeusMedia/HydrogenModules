<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env  */
/** @var View $view */
/** @var array $words */

$w			= (object) $words['index'];

/* TO BE USED LATER FOR STATUS INFO
$indicator	= new \CeusMedia\Common\UI\HTML\Indicator();
$indicator->setIndicatorClass( 'indicator-small' );
$ind1		= $indicator->build( 75, 100 );
*/

$panelInfo		= $view->loadTemplateFile( 'manage/my/user/index.info.php' );
$panelPassword	= $view->loadTemplateFile( 'manage/my/user/index.password.php' );
$panelPasswords	= $view->loadTemplateFile( 'manage/my/user/index.passwords.php' );
$panelEmail		= $view->loadTemplateFile( 'manage/my/user/index.email.php' );
$panelUsername	= $view->loadTemplateFile( 'manage/my/user/index.username.php' );
$panelEdit		= $view->loadTemplateFile( 'manage/my/user/index.edit.php' );
$panelAccount	= $view->loadTemplateFile( 'manage/my/user/index.account.php' );
$panelRelations	= $view->loadTemplateFile( 'manage/my/user/index.relations.php' );
$panelRemove	= $view->loadTemplateFile( 'manage/my/user/index.remove.php' );

$tabs	= View_Manage_My_User::renderTabs( $env );

extract( $view->populateTexts( ['top', 'bottom'], 'html/manage/my/user/' ) );

return $textTop.$tabs.HtmlTag::create( 'div', [
	HtmlTag::create( 'div', [
//		$panelAccount,
		$panelEdit,
		$panelPassword,
		$panelEmail,
		$panelUsername,
		$panelRemove,
	], ['class' => 'span7'] ),
	HtmlTag::create( 'div', [
		$panelInfo,
		$panelRelations,
		$panelPasswords,
	], ['class' => 'span5'] ),
], ['class' => 'row-fluid'] ).$textBottom;
