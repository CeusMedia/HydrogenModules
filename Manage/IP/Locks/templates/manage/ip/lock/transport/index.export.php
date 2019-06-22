<?php

$iconDownload	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fw fa-download' ) );

$panelExport	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Export' ),
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
			UI_HTML_Tag::create( 'a', $iconDownload.'&nbsp;exportieren', array(
				'href'	=> './manage/ip/lock/transport/export',
				'class'	=> 'btn btn-primary'
			) )
		), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

return $panelExport;
