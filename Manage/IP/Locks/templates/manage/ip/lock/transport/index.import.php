<?php

$iconUpload		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fw fa-upload' ) );

$upload	= new View_Helper_Input_File( $env );
$upload->setLabel( 'Datei' );
$upload->setName( 'upload' );

$panelImport	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Import' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'form', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Datei aus vorherigem Export', array( 'for' => 'input_upload' ) ),
					$upload->render()
				) ),
			) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'h4', 'Typ der Installation' ),
					UI_HTML_Tag::create( 'label', array(
						UI_HTML_Tag::create( 'input', NULL, array(
							'type'		=> 'radio',
							'name'		=> 'type',
							'value'		=> 'merge',
							'id'		=> 'input_type-merge',
							'checked'	=> 'checked',
						) ),
						UI_HTML_Tag::create( 'div', array(
							UI_HTML_Tag::create( 'strong', 'Aktuellen Bestand erweitern' ),
							UI_HTML_Tag::create( 'br' ),
							UI_HTML_Tag::create( 'small', 'Bestehende Regeln und Sperren bleiben erhalten und neue Regeln werden hinzugefügt.', array( 'class' => 'muted' ) ),
						) ),
					), array( 'class' => 'radio' ) ),
					UI_HTML_Tag::create( 'label', array(
						UI_HTML_Tag::create( 'input', NULL, array(
							'type'		=> 'radio',
							'name'		=> 'type',
							'value'		=> 'fresh',
							'id'		=> 'input_type-fresh',
						) ),
						UI_HTML_Tag::create( 'div', array(
							UI_HTML_Tag::create( 'strong', 'Frische Installation' ),
							UI_HTML_Tag::create( 'br' ),
							UI_HTML_Tag::create( 'small', 'Bestehende Regeln und Sperren werden vor der Installation entfernt.', array( 'class' => 'muted' ) ),
						) ),
					), array( 'class' => 'radio' ) ),


				) ),
			) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'button', $iconUpload.'&nbsp;importieren', array(
					'name'	=> 'save',
					'type'	=> 'submit',
					'class'	=> 'btn btn-primary'
				) ),
			), array( 'class' => 'buttonbar' ) ),
		), array(
			'action'	=> './manage/ip/lock/transport/import',
			'method'	=> 'post',
			'enctype'	=> 'multipart/form-data'
		) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

return $panelImport;
