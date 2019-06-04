<?php

$panelFilter	= $view->loadTemplateFile( 'manage/form/block/index.filter.php' );
$panelList		= $view->loadTemplateFile( 'manage/form/block/index.list.php' );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', $panelFilter, array( 'class' => 'span3' ) ),
	UI_HTML_Tag::create( 'div', $panelList, array( 'class' => 'span9' ) ),
), array( 'class' => 'row-fluid' ) );
