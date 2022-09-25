<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$topics	= array_keys( $eventTypes );

$chunks	= array(
	array_slice( $topics, 0, 4 ),
	array_slice( $topics, 4, 4 ),
	array_slice( $topics, 8, 3 ),
	array_slice( $topics, 11, 6 ),
);

$lists	= [];
foreach( $chunks as $nr => $chunk ){
	$lists[$nr]	= [];
	foreach( $chunk as $topic ){
		$types		= $eventTypes[$topic];
		$sublist	= [];
		foreach( $types as $type ){
			$input		= HtmlTag::create( 'input', NULL, array(
				'name'		=> 'types[]',
				'type'		=> 'checkbox',
				'value'		=> $type,
 			) );
			if( in_array( $type, $hookedEventTypes ) ){
				$input		= HtmlTag::create( 'input', NULL, array(
					'type'		=> 'checkbox',
					'checked'	=> 'checked',
					'disabled'	=> 'disabled',
				) ).HtmlTag::create( 'input', NULL, array(
					'name'		=> 'types[]',
					'type'		=> 'checkbox',
					'value'		=> $type,
					'checked'	=> 'checked',
					'style'		=> 'visibility: hidden'
				) );
			}
			$label		= substr( $type, strlen( $topic ) );
			$label		= ucwords( strtolower( str_replace( '_', ' ', $label ) ) );
			$label		= HtmlTag::create( 'label', $input.' '.$label, array(
				'class'	=> 'checkbox',
				'style'	=> 'font-size: 0.9em; margin: 0; display: inline-block',
			) );
			$sublist[]	= HtmlTag::create( 'li', $label, array( 'style' => 'line-height: 15px' ) );
		}
		$sublist	= HtmlTag::create( 'ul', $sublist, array( 'class' => 'unstyled' ) );
		$topic		= ucwords( strtolower( str_replace( '_', ' ', $topic ) ) );
		$topic		= HtmlTag::create( 'h4', $topic, array( 'style' => 'font-size: 1.1em; line-height: 15px; font-weight: bold' ) );
		$lists[$nr][]		= HtmlTag::create( 'li', $topic.$sublist );
	}
	$lists[$nr]	= HtmlTag::create( 'div', array(
		HtmlTag::create( 'ul', $lists[$nr], array( 'class' => 'unstyled' ) ),
	), array( 'class' => 'span3' ) );
}

$list	= HtmlTag::create( 'div', $lists, array( 'class' => 'row-fluid' ) );

$tabs	= View_Admin_Payment_Mangopay::renderTabs( $env, 'hook' );

return $tabs.HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Hooks' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'form', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Aktuelle URL', array() ),
					HtmlTag::create( 'input', NULL, array( 'type' => 'text', 'class' => 'span12', 'disabled' => 'disabled', 'value' => $currentUrl ) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),

			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Base URL', array() ),
					HtmlTag::create( 'input', NULL, array( 'type' => 'text', 'class' => 'span12', 'disabled' => 'disabled', 'value' => $baseUrl ) ),
				), array( 'class' => 'span6' ) ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Hook Path', array( 'for' => 'input_path' ) ),
					HtmlTag::create( 'input', NULL, array( 'name' => 'path', 'id' => 'input_path', 'type' => 'text', 'class' => 'span12' ) ),
				), array( 'class' => 'span6' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					$list,
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'a', '<i class="fa fa-fw fa-arrow-left"></i> zurück', array( 'href' => './admin/payment/mangopay/hook', 'class' => 'btn' ) ),
				' ',
				HtmlTag::create( 'button', '<i class="fa fa-fw fa-cogs"></i> anwenden', array( 'type' => 'submit', 'name' => 'save', 'class' => 'btn btn-primary' ) ),
			), array( 'class' => 'buttonbar' ) ),
		), array( 'action' => './admin/payment/mangopay/hook/apply', 'method' => 'POST' )),
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) );
