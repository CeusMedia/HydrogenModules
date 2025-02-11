<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

/** @var View $view */

$panelFilter	= $view->loadTemplateFile( 'manage/form/mail/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/form/mail/index.list.php' );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'div', $panelFilter, ['class' => 'span3'] ),
	HtmlTag::create( 'div', $panelList, ['class' => 'span9'] ),
], ['class' => 'row-fluid'] );
