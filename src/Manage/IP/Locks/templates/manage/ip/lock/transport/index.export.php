<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconDownload	= HtmlTag::create( 'i', '', ['class' => 'fa fw fa-download'] );

$listReasons	= [];
foreach( $reasons as $reason ){
	$listReasons[]	= HtmlTag::create( 'li', array(
		HtmlTag::create( 'label', array(
			HtmlTag::create( 'input', NULL, array(
				'type'				=> 'checkbox',
				'name'				=> 'reasonIds[]',
				'id'				=> 'input_reasonIds-'.$reason->ipLockReasonId,
				'value'				=> $reason->ipLockReasonId,
				'checked'			=> 'checked',
				'class'				=> 'has-optionals',
				'data-animation'	=> 'slide',
			) ),
			HtmlTag::create( 'div', array(
				$reason->title,
				' ',
				HtmlTag::create( 'small', '('.count( $reason->filters ).')', ['class' => 'muted'] ),
			), ['title' => $reason->description] ),
		), ['class' => 'checkbox'] ),
	) );
}
$listReasons	= HtmlTag::create( 'ul', $listReasons, array(
	'class'		=> 'unstyled',
	'style'		=> 'padding-left: 1.5em;',
) );

$listFilters	= [];
foreach( $filters as $filter ){
	$listFilters[]	= HtmlTag::create( 'li', array(
		HtmlTag::create( 'label', array(
			HtmlTag::create( 'input', NULL, array(
				'type'		=> 'checkbox',
				'name'		=> 'filterIds[]',
				'id'		=> 'input_filterIds-'.$filter->ipLockFilterId,
				'value'		=> $filter->ipLockFilterId,
				'checked'	=> 'checked',
			) ),
			HtmlTag::create( 'div', $filter->title ),
		), ['class' => 'checkbox'] ),
	), ['class' => 'optional reasonIds-'.$filter->reasonId.' reasonIds-'.$filter->reasonId.'-true'] );
}
$listFilters	= HtmlTag::create( 'ul', $listFilters, array(
	'class'		=> 'unstyled',
	'style'		=> 'padding-left: 1.5em;',
) );

$panelExport	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Export' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'form', array(
			HtmlTag::create( 'input', NULL, array(
				'type'	=> 'hidden',
				'name'	=> 'not-filters',
				'value'	=> 'all',
			) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', join( '<br/>', array(
					'Bestehende Gründe und Regeln werden in eine JSON-Datei gespeichert.',
					'Aktuelle Sperren werden dabei nicht exportiert.',
					'Die JSON-Datei kann zur Archivierung abgelegt oder in einer anderen Applikation importiert werden.'
				 ) ) ),
			), ['class' => 'alert alert-success'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'h4', 'Gründe' ),
					HtmlTag::create( 'label', array(
						HtmlTag::create( 'input', NULL, array(
							'type'				=> 'checkbox',
							'name'				=> 'reasons',
							'id'				=> 'input_reasons',
							'value'				=> 'all',
							'checked'			=> 'checked',
							'class'				=> 'has-optionals',
							'data-animation'	=> 'slide',
						) ),
						HtmlTag::create( 'div', 'alle Gründe' ),
					), ['class' => 'checkbox'] ),
					HtmlTag::create( 'div', array(
						$listReasons,
						HtmlTag::create( 'h4', 'Filter' ),
						HtmlTag::create( 'label', array(
							HtmlTag::create( 'input', NULL, array(
								'type'				=> 'checkbox',
								'name'				=> 'filters',
								'id'				=> 'input_filters',
								'value'				=> 'all',
								'checked'			=> 'checked',
								'class'				=> 'has-optionals',
								'data-animation'	=> 'slide',
							) ),
							HtmlTag::create( 'div', 'alle Filter' ),
						), ['class' => 'checkbox'] ),
						HtmlTag::create( 'div', array(
							$listFilters,
						), ['class' => 'optional filters filters-false', 'style' => 'display: none'] ),

					), ['class' => 'optional reasons reasons-false', 'style' => 'display: none'] ),
				), ['class' => 'span12'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'br' ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Dateiname', ['for' => 'input_filename'] ),
					HtmlTag::create( 'input', NULL, array(
						'type'		=> 'text',
						'name'		=> 'filename',
						'id'		=> 'input_filename',
						'value'		=> 'Lock_Filters_'.date( 'Y-m-d' ),
						'class'		=> 'span12',
					) ),
				), ['class' => 'span12'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'button', $iconDownload.'&nbsp;exportieren', array(
					'type'	=> 'submit',
					'name'	=> 'save',
					'class'	=> 'btn btn-primary'
				) )
			), ['class' => 'buttonbar'] ),
		), array(
			'action'	=> './manage/ip/lock/transport/export',
			'method'	=> 'post',
		) ),
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

return $panelExport;
