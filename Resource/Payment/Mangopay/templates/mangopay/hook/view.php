<?php

$table	= print_m( $hook, NULL, NULL, TRUE );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Hook' ),
	UI_HTML_Tag::create( 'div', array(
		$table,
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'a', '<i class="fa fa-fw fa-arrow-left"></i> zurück', array(
				'href'	=> './mangopay/hook',
				'class'	=> 'btn',
			) ),
		), array( 'class' => 'buttonbar' ) )
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );
?>
