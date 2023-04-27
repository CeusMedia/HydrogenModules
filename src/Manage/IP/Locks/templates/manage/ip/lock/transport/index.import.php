<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconUpload		= HtmlTag::create( 'i', '', ['class' => 'fa fw fa-upload'] );

$upload	= new View_Helper_Input_File( $env );
$upload->setLabel( 'Datei' );
$upload->setName( 'upload' );

$panelImport	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Import' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'form', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Datei aus vorherigem Export', ['for' => 'input_upload'] ),
					$upload->render()
				) ),
			) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'h4', 'Typ der Installation' ),
					HtmlTag::create( 'label', array(
						HtmlTag::create( 'input', NULL, [
							'type'		=> 'radio',
							'name'		=> 'type',
							'value'		=> 'merge',
							'id'		=> 'input_type-merge',
							'checked'	=> 'checked',
						] ),
						HtmlTag::create( 'div', array(
							HtmlTag::create( 'strong', 'Aktuellen Bestand erweitern' ),
							HtmlTag::create( 'br' ),
							HtmlTag::create( 'small', 'Bestehende Regeln und Sperren bleiben erhalten und neue Regeln werden hinzugefügt.', ['class' => 'muted'] ),
						) ),
					), ['class' => 'radio'] ),
					HtmlTag::create( 'label', array(
						HtmlTag::create( 'input', NULL, [
							'type'		=> 'radio',
							'name'		=> 'type',
							'value'		=> 'fresh',
							'id'		=> 'input_type-fresh',
						] ),
						HtmlTag::create( 'div', array(
							HtmlTag::create( 'strong', 'Frische Installation' ),
							HtmlTag::create( 'br' ),
							HtmlTag::create( 'small', 'Bestehende Regeln und Sperren werden vor der Installation entfernt.', ['class' => 'muted'] ),
						) ),
					), ['class' => 'radio'] ),


				) ),
			) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'button', $iconUpload.'&nbsp;importieren', [
					'name'	=> 'save',
					'type'	=> 'submit',
					'class'	=> 'btn btn-primary'
				] ),
			), ['class' => 'buttonbar'] ),
		), [
			'action'	=> './manage/ip/lock/transport/import',
			'method'	=> 'post',
			'enctype'	=> 'multipart/form-data'
		] ),
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

return $panelImport;
