<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object $server */

$panelList		= $view->loadTemplateFile( 'admin/log/exception/index.list.php' );
$panelFilter	= $view->loadTemplateFile( 'admin/log/exception/index.filter.php' );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'div', $panelFilter, ['class' => 'span3'] ),
	HtmlTag::create( 'div', $panelList, ['class' => 'span9'] )
], ['class' => 'row-fluid'] );
