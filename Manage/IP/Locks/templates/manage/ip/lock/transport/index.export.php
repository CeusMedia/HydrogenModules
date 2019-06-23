<?php

$iconDownload	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fw fa-download' ) );

$listReasons	= array();
foreach( $reasons as $reason ){
	$listReasons[]	= UI_HTML_Tag::create( 'li', array(
		UI_HTML_Tag::create( 'label', array(
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'checkbox',
				'name'		=> 'reasonIds[]',
				'id'		=> 'input_reasonIds-'.$reason->ipLockReasonId,
				'value'		=> $reason->ipLockReasonId,
				'checked'	=> 'checked',
			) ),
			UI_HTML_Tag::create( 'div', $reason->title, array( 'title' => $reason->description ) ),
		), array( 'class' => 'checkbox' ) ),
	) );
}
$listReasons	= UI_HTML_Tag::create( 'ul', $listReasons, array(
	'class'		=> 'unstyled optional reasons-false',
	'style'		=> 'display: none'
) );

$panelExport	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Export' ),
	UI_HTML_Tag::create( 'form', array(
		UI_HTML_Tag::create( 'input', NULL, array(
			'type'	=> 'hidden',
			'name'	=> 'filters',
			'value'	=> 'all',
		) ),
		UI_HTML_Tag::create( 'div', array(
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
							'type'		=> 'checkbox',
							'name'		=> 'reasons',
							'id'		=> 'input_reasons',
							'value'		=> 'all',
							'checked'	=> 'checked',
							'class'		=> 'has-optionals',
						) ),
						UI_HTML_Tag::create( 'div', 'alle Gründe' ),
					), array( 'class' => 'checkbox' ) ),
					$listReasons,
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
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
