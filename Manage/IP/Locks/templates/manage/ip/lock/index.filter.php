<?php

$iconFilter		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-search icon-white' ) );
$iconReset		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-zoom-out icon-white' ) );
$buttonFilter	= UI_HTML_Tag::create( 'button', $iconFilter.' filtern', array(
	'type'	=> 'submit',
	'name'	=> 'filter',
	'class'	=> 'btn btn-info',
) );
$buttonReset	= UI_HTML_Tag::create( 'a', $iconReset.' zurücksetzen', array(
	'href'	=> './manage/ip/lock/order/reset',
	'class'	=> 'btn btn-small btn-inverse',
) );

$optStatus = array(
	-10	=> 'deaktiviert',
	-2	=> 'unlocked',
	-1	=> '...',
	''	=> '- alle -',
	0	=> 'lock requested',
	1	=> 'locked',
	2	=> 'unlock requested',
);
$optStatus	= UI_HTML_Elements::Options( $optStatus, $filterStatus );

$optSort	= array(
	'lockedAt'	=> 'Sperrung',
	'IPv4'		=> 'IP',
);
$optSort	= UI_HTML_Elements::Options( $optSort, $filterSort );

$optOrder	= array(
	'asc'	=> 'aufsteigend',
	'desc'	=> 'absteigend',
);
$optOrder	= UI_HTML_Elements::Options( $optOrder, $filterOrder );

$panelFilter	= HTML::DivClass( 'content-panel',
	UI_HTML_Tag::create( 'h3', 'Filter' ).
	HTML::DivClass( 'content-panel-inner',
		UI_HTML_Tag::create( 'form', array(
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', array(
					UI_HTML_Tag::create( 'label', 'IP-Adresse', array( 'for' => 'input_ip' ) ),
					UI_HTML_Tag::create( 'input', NULL, array( 'type' => 'text', 'name' => 'ip', 'id' => 'input_id', 'class' => 'span12', 'value' => htmlentities( $filterIp, ENT_QUOTES, 'UTF-8' ) ) ),
				) )
			),
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', array(
					UI_HTML_Tag::create( 'label', 'Status', array( 'for' => 'input_status' ) ),
					UI_HTML_Tag::create( 'select', $optStatus, array( 'name' => 'status', 'id' => 'input_status', 'class' => 'span12' ) ),
				) )
			),
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', array(
					UI_HTML_Tag::create( 'label', 'Sortierung', array( 'for' => 'input_sort' ) ),
					UI_HTML_Tag::create( 'select', $optSort, array( 'name' => 'sort', 'id' => 'input_sort', 'class' => 'span12' ) ),
				) )
			),
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', array(
					UI_HTML_Tag::create( 'label', 'Richtung', array( 'for' => 'input_order' ) ),
					UI_HTML_Tag::create( 'select', $optOrder, array( 'name' => 'order', 'id' => 'input_order', 'class' => 'span12' ) ),
				) )
			),
			HTML::DivClass( 'buttonbar',
				HTML::DivClass( 'btn-toolbar', array(
					$buttonFilter,
					$buttonReset
				) )
			)
		), array( 'action' => './manage/ip/lock/order', 'method' => 'post' ) )
	)
);
return $panelFilter;
