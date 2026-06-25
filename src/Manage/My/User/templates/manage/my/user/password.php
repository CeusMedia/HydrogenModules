<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env  */
/** @var View $view */

$panelPassword	= $view->loadTemplateFile( 'manage/my/user/index.password.php' );
$panelPasswords	= $view->loadTemplateFile( 'manage/my/user/index.passwords.php' );

$tabs	= View_Manage_My_User::renderTabs( $env, 'password' );

extract( $view->populateTexts( ['top', 'bottom'], 'html/manage/my/user/password' ) );

return $textTop.$tabs.HtmlTag::create( 'div', [
		HtmlTag::create( 'div', [
			$panelPassword,
			$panelPasswords,
		], ['class' => 'span12'] ),
	], ['class' => 'row-fluid'] ).$textBottom;
