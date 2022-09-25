<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconFilter		= HtmlTag::create( 'i', '', array( 'class' => 'icon-search icon-white' ) );
$iconReset		= HtmlTag::create( 'i', '', array( 'class' => 'icon-zoom-out icon-white' ) );
$buttonFilter	= HtmlTag::create( 'button', $iconFilter.' filtern', array(
	'type'	=> 'submit',
	'name'	=> 'filter',
	'class'	=> 'btn btn-info',
) );
$buttonReset	= HtmlTag::create( 'a', $iconReset.' zurücksetzen', array(
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
$optStatus	= HtmlElements::Options( $optStatus, $filterStatus );

$optSort	= array(
	'lockedAt'	=> 'Sperrung',
	'IP'		=> 'IP',
);
$optSort	= HtmlElements::Options( $optSort, $filterSort );

$optOrder	= array(
	'asc'	=> 'aufsteigend',
	'desc'	=> 'absteigend',
);
$optOrder	= HtmlElements::Options( $optOrder, $filterOrder );

$panelFilter	= HTML::DivClass( 'content-panel',
	HtmlTag::create( 'h3', 'Filter' ).
	HTML::DivClass( 'content-panel-inner',
		HtmlTag::create( 'form', array(
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', array(
					HtmlTag::create( 'label', 'IP-Adresse', array( 'for' => 'input_ip' ) ),
					HtmlTag::create( 'input', NULL, array( 'type' => 'text', 'name' => 'ip', 'id' => 'input_id', 'class' => 'span12', 'value' => htmlentities( $filterIp, ENT_QUOTES, 'UTF-8' ) ) ),
				) )
			),
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', array(
					HtmlTag::create( 'label', 'Status', array( 'for' => 'input_status' ) ),
					HtmlTag::create( 'select', $optStatus, array( 'name' => 'status', 'id' => 'input_status', 'class' => 'span12' ) ),
				) )
			),
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', array(
					HtmlTag::create( 'label', 'Sortierung', array( 'for' => 'input_sort' ) ),
					HtmlTag::create( 'select', $optSort, array( 'name' => 'sort', 'id' => 'input_sort', 'class' => 'span12' ) ),
				) )
			),
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span12', array(
					HtmlTag::create( 'label', 'Richtung', array( 'for' => 'input_order' ) ),
					HtmlTag::create( 'select', $optOrder, array( 'name' => 'order', 'id' => 'input_order', 'class' => 'span12' ) ),
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
