<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$panelFilter	= $view->loadTemplateFile( 'manage/form/fill/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/form/fill/index.list.php' );

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', $panelFilter, array( 'class' => 'span3' ) ),
	HtmlTag::create( 'div', $panelList, array( 'class' => 'span9' ) ),
), array( 'class' => 'row-fluid' ) );
