<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$panelFilter	= $view->loadTemplateFile( 'manage/form/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/form/index.list.php' );

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', $panelFilter, ['class' => 'span3'] ),
	HtmlTag::create( 'div', $panelList, ['class' => 'span9'] ),
), ['class' => 'row-fluid'] );
