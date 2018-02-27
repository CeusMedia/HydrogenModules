<?php
$tabs	= $view->renderTabs( $env, 'message' );

return $tabs.UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Mail: Raw' ),
	UI_HTML_Tag::create( 'div', array(
		xmp( $message->raw, TRUE ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
