<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<object> $reasons */
/** @var object $filters */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['index.export'];

$iconDownload	= HtmlTag::create( 'i', '', ['class' => 'fa fw fa-download'] );

$listReasons	= [];
foreach( $reasons as $reason ){
	$listReasons[]	= HtmlTag::create( 'li', [
		HtmlTag::create( 'label', [
			HtmlTag::create( 'input', NULL, [
				'type'				=> 'checkbox',
				'name'				=> 'reasonIds[]',
				'id'				=> 'input_reasonIds-'.$reason->ipLockReasonId,
				'value'				=> $reason->ipLockReasonId,
				'checked'			=> 'checked',
				'class'				=> 'has-optionals',
				'data-animation'	=> 'slide',
			] ),
			HtmlTag::create( 'div', [
				$reason->title,
				' ',
				HtmlTag::create( 'small', '('.count( $reason->filters ).')', ['class' => 'muted'] ),
			], ['title' => $reason->description] ),
		], ['class' => 'checkbox'] ),
	] );
}
$listReasons	= HtmlTag::create( 'ul', $listReasons, [
	'class'		=> 'unstyled',
	'style'		=> 'padding-left: 1.5em;',
] );

$listFilters	= [];
foreach( $filters as $filter ){
	$listFilters[]	= HtmlTag::create( 'li', [
		HtmlTag::create( 'label', [
			HtmlTag::create( 'input', NULL, [
				'type'		=> 'checkbox',
				'name'		=> 'filterIds[]',
				'id'		=> 'input_filterIds-'.$filter->ipLockFilterId,
				'value'		=> $filter->ipLockFilterId,
				'checked'	=> 'checked',
			] ),
			HtmlTag::create( 'div', $filter->title ),
		], ['class' => 'checkbox'] ),
	], ['class' => 'optional reasonIds-'.$filter->reasonId.' reasonIds-'.$filter->reasonId.'-true'] );
}
$listFilters	= HtmlTag::create( 'ul', $listFilters, [
	'class'		=> 'unstyled',
	'style'		=> 'padding-left: 1.5em;',
] );

$panelExport	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', $w->heading ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'form', [
			HtmlTag::create( 'input', NULL, [
				'type'	=> 'hidden',
				'name'	=> 'not-filters',
				'value'	=> 'all',
			] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', $w->description ),
			], ['class' => 'alert alert-success'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'h4', $w->headingReasons ),
					HtmlTag::create( 'label', [
						HtmlTag::create( 'input', NULL, [
							'type'				=> 'checkbox',
							'name'				=> 'reasons',
							'id'				=> 'input_reasons',
							'value'				=> 'all',
							'checked'			=> 'checked',
							'class'				=> 'has-optionals',
							'data-animation'	=> 'slide',
						] ),
						HtmlTag::create( 'div', $w->labelAllReasons ),
					], ['class' => 'checkbox'] ),
					HtmlTag::create( 'div', [
						$listReasons,
						HtmlTag::create( 'h4', $w->headingFilters ),
						HtmlTag::create( 'label', [
							HtmlTag::create( 'input', NULL, [
								'type'				=> 'checkbox',
								'name'				=> 'filters',
								'id'				=> 'input_filters',
								'value'				=> 'all',
								'checked'			=> 'checked',
								'class'				=> 'has-optionals',
								'data-animation'	=> 'slide',
							] ),
							HtmlTag::create( 'div', $w->labelAllFilters ),
						], ['class' => 'checkbox'] ),
						HtmlTag::create( 'div', [
							$listFilters,
						], ['class' => 'optional filters filters-false', 'style' => 'display: none'] ),

					], ['class' => 'optional reasons reasons-false', 'style' => 'display: none'] ),
				], ['class' => 'span12'] ),
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'br' ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', $w->labelFilename, ['for' => 'input_filename'] ),
					HtmlTag::create( 'input', NULL, [
						'type'		=> 'text',
						'name'		=> 'filename',
						'id'		=> 'input_filename',
						'value'		=> 'Lock_Filters_'.date( 'Y-m-d' ),
						'class'		=> 'span12',
					] ),
				], ['class' => 'span12'] ),
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'button', $iconDownload.'&nbsp;'.$w->buttonExport, [
					'type'	=> 'submit',
					'name'	=> 'save',
					'class'	=> 'btn btn-primary'
				] )
			], ['class' => 'buttonbar'] ),
		], [
			'action'	=> './manage/ip/lock/transport/export',
			'method'	=> 'post',
		] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );

return $panelExport;
