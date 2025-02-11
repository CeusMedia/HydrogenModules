<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
declare(strict_types=1);

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

/** @var View $view */

$panelFilter	= $view->loadTemplateFile( 'manage/form/block/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/form/block/index.list.php' );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'div', $panelFilter, ['class' => 'span3'] ),
	HtmlTag::create( 'div', $panelList, ['class' => 'span9'] ),
], ['class' => 'row-fluid'] );
