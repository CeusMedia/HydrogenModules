<?php

$iconDownload	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fw fa-download' ) );

$listReasons	= array();
foreach( $reasons as $reason ){
	$listReasons[]	= UI_HTML_Tag::create( 'li', array(
		UI_HTML_Tag::create( 'label', array(
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'				=> 'checkbox',
				'name'				=> 'reasonIds[]',
				'id'				=> 'input_reasonIds-'.$reason->ipLockReasonId,
				'value'				=> $reason->ipLockReasonId,
				'checked'			=> 'checked',
				'class'				=> 'has-optionals',
				'data-animation'	=> 'slide',
			) ),
			UI_HTML_Tag::create( 'div', array(
				$reason->title,
				' ',
				UI_HTML_Tag::create( 'small', '('.count( $reason->filters ).')', array( 'class' => 'muted' ) ),
			), array( 'title' => $reason->description ) ),
		), array( 'class' => 'checkbox' ) ),
	) );
}
$listReasons	= UI_HTML_Tag::create( 'ul', $listReasons, array(
	'class'		=> 'unstyled',
	'style'		=> 'padding-left: 1.5em;',
) );

$listFilters	= array();
foreach( $filters as $filter ){
	$listFilters[]	= UI_HTML_Tag::create( 'li', array(
		UI_HTML_Tag::create( 'label', array(
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'checkbox',
				'name'		=> 'filterIds[]',
				'id'		=> 'input_filterIds-'.$filter->ipLockFilterId,
				'value'		=> $filter->ipLockFilterId,
				'checked'	=> 'checked',
			) ),
			UI_HTML_Tag::create( 'div', $filter->title ),
		), array( 'class' => 'checkbox' ) ),
	), array( 'class' => 'optional reasonIds-'.$filter->reasonId.' reasonIds-'.$filter->reasonId.'-true' ) );
}
$listFilters	= UI_HTML_Tag::create( 'ul', $listFilters, array(
	'class'		=> 'unstyled',
	'style'		=> 'padding-left: 1.5em;',
) );

$panelExport	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Export' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'form', array(
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'	=> 'hidden',
				'name'	=> 'not-filters',
				'value'	=> 'all',
			) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', join( '<br/>', array(
					'Bestehende Gründe und Regeln werden in eine JSON-Datei gespeichert.',
					'Aktuelle Sperren werden dabei nicht exportiert.',
					'Die JSON-Datei kann zur Archivierung abgelegt oder in einer anderen Applikation importiert werden.'
				 ) ) ),
			), array( 'class' => 'alert alert-success' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'h4', 'Gründe' ),
					UI_HTML_Tag::create( 'label', array(
						UI_HTML_Tag::create( 'input', NULL, array(
							'type'				=> 'checkbox',
							'name'				=> 'reasons',
							'id'				=> 'input_reasons',
							'value'				=> 'all',
							'checked'			=> 'checked',
							'class'				=> 'has-optionals',
							'data-animation'	=> 'slide',
						) ),
						UI_HTML_Tag::create( 'div', 'alle Gründe' ),
					), array( 'class' => 'checkbox' ) ),
					UI_HTML_Tag::create( 'div', array(
						$listReasons,
						UI_HTML_Tag::create( 'h4', 'Filter' ),
						UI_HTML_Tag::create( 'label', array(
							UI_HTML_Tag::create( 'input', NULL, array(
								'type'				=> 'checkbox',
								'name'				=> 'filters',
								'id'				=> 'input_filters',
								'value'				=> 'all',
								'checked'			=> 'checked',
								'class'				=> 'has-optionals',
								'data-animation'	=> 'slide',
							) ),
							UI_HTML_Tag::create( 'div', 'alle Filter' ),
						), array( 'class' => 'checkbox' ) ),
						UI_HTML_Tag::create( 'div', array(
							$listFilters,
						), array( 'class' => 'optional filters filters-false', 'style' => 'display: none' ) ),

					), array( 'class' => 'optional reasons reasons-false', 'style' => 'display: none' ) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'br' ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Dateiname', array( 'for' => 'input_filename' ) ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'		=> 'text',
						'name'		=> 'filename',
						'id'		=> 'input_filename',
						'value'		=> 'Lock_Filters_'.date( 'Y-m-d' ),
						'class'		=> 'span12',
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'button', $iconDownload.'&nbsp;exportieren', array(
					'type'	=> 'submit',
					'name'	=> 'save',
					'class'	=> 'btn btn-primary'
				) )
			), array( 'class' => 'buttonbar' ) ),
		), array(
			'action'	=> './manage/ip/lock/transport/export',
			'method'	=> 'post',
		) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

return $panelExport;
