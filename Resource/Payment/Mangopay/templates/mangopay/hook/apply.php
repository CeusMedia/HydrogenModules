<?php

$topics	= array_keys( $eventTypes );

$chunks	= array(
	array_slice( $topics, 0, 4 ),
	array_slice( $topics, 4, 4 ),
	array_slice( $topics, 8, 3 ),
	array_slice( $topics, 11, 6 ),
);

$lists	= array();
foreach( $chunks as $nr => $chunk ){
	$lists[$nr]	= array();
	foreach( $chunk as $topic ){
		$types		= $eventTypes[$topic];
		$sublist	= array();
		foreach( $types as $type ){
			$input		= UI_HTML_Tag::create( 'input', NULL, array(
				'name'		=> 'types[]',
				'type'		=> 'checkbox',
				'value'		=> $type,
 			) );
			if( in_array( $type, $hookedEventTypes ) ){
				$input		= UI_HTML_Tag::create( 'input', NULL, array(
					'type'		=> 'checkbox',
					'checked'	=> 'checked',
					'disabled'	=> 'disabled',
				) ).UI_HTML_Tag::create( 'input', NULL, array(
					'name'		=> 'types[]',
					'type'		=> 'checkbox',
					'value'		=> $type,
					'checked'	=> 'checked',
					'style'		=> 'visibility: hidden'
				) );
			}
			$label		= substr( $type, strlen( $topic ) );
			$label		= ucwords( strtolower( str_replace( '_', ' ', $label ) ) );
			$label		= UI_HTML_Tag::create( 'label', $input.' '.$label, array(
				'class'	=> 'checkbox',
				'style'	=> 'font-size: 0.9em; margin: 0; display: inline-block',
			) );
			$sublist[]	= UI_HTML_Tag::create( 'li', $label, array( 'style' => 'line-height: 15px' ) );
		}
		$sublist	= UI_HTML_Tag::create( 'ul', $sublist, array( 'class' => 'unstyled' ) );
		$topic		= ucwords( strtolower( str_replace( '_', ' ', $topic ) ) );
		$topic		= UI_HTML_Tag::create( 'h4', $topic, array( 'style' => 'font-size: 1.1em; line-height: 15px; font-weight: bold' ) );
		$lists[$nr][]		= UI_HTML_Tag::create( 'li', $topic.$sublist );
	}
	$lists[$nr]	= UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'ul', $lists[$nr], array( 'class' => 'unstyled' ) ),
	), array( 'class' => 'span3' ) );
}

$list	= UI_HTML_Tag::create( 'div', $lists, array( 'class' => 'row-fluid' ) );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Hooks' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'form', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Aktuelle URL', array() ),
					UI_HTML_Tag::create( 'input', NULL, array( 'type' => 'text', 'class' => 'span12', 'disabled' => 'disabled', 'value' => $currentUrl ) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),

			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Base URL', array() ),
					UI_HTML_Tag::create( 'input', NULL, array( 'type' => 'text', 'class' => 'span12', 'disabled' => 'disabled', 'value' => $baseUrl ) ),
				), array( 'class' => 'span6' ) ),
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Hook Path', array( 'for' => 'input_path' ) ),
					UI_HTML_Tag::create( 'input', NULL, array( 'name' => 'path', 'id' => 'input_path', 'type' => 'text', 'class' => 'span12' ) ),
				), array( 'class' => 'span6' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					$list,
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'a', '<i class="fa fa-fw fa-arrow-left"></i> zurück', array( 'href' => './mangopay/hook', 'class' => 'btn' ) ),
				' ',
				UI_HTML_Tag::create( 'button', '<i class="fa fa-fw fa-cogs"></i> anwenden', array( 'type' => 'submit', 'name' => 'save', 'class' => 'btn btn-primary' ) ),
			), array( 'class' => 'buttonbar' ) ),
		), array( 'action' => './mangopay/hook/apply', 'method' => 'POST' )),
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );
