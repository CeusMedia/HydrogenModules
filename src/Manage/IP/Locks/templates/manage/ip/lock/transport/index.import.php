<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var \CeusMedia\HydrogenFramework\Environment $env */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['index.import'];

$iconUpload		= HtmlTag::create( 'i', '', ['class' => 'fa fw fa-upload'] );

$upload	= new View_Helper_Input_File( $env );
$upload->setLabel( $w->labelUpload );
$upload->setName( 'upload' );

$panelImport	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', $w->heading ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'form', [
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', $w->labelFile, ['for' => 'input_upload'] ),
					$upload->render()
				] ),
			] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'h4', $w->headingType ),
					HtmlTag::create( 'label', [
						HtmlTag::create( 'input', NULL, [
							'type'		=> 'radio',
							'name'		=> 'type',
							'value'		=> 'merge',
							'id'		=> 'input_type-merge',
							'checked'	=> 'checked',
						] ),
						HtmlTag::create( 'div', [
							HtmlTag::create( 'strong', $w->labelTypeMerge ),
							HtmlTag::create( 'br' ),
							HtmlTag::create( 'small', $w->labelTypeMerge_hint, ['class' => 'muted'] ),
						] ),
					], ['class' => 'radio'] ),
					HtmlTag::create( 'label', [
						HtmlTag::create( 'input', NULL, [
							'type'		=> 'radio',
							'name'		=> 'type',
							'value'		=> 'fresh',
							'id'		=> 'input_type-fresh',
						] ),
						HtmlTag::create( 'div', [
							HtmlTag::create( 'strong', $w->labelTypeFresh ),
							HtmlTag::create( 'br' ),
							HtmlTag::create( 'small', $w->labelTypeFresh_hint, ['class' => 'muted'] ),
						] ),
					], ['class' => 'radio'] ),


				] ),
			] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'button', $iconUpload.'&nbsp;'.$w->buttonImport, [
					'name'	=> 'save',
					'type'	=> 'submit',
					'class'	=> 'btn btn-primary'
				] ),
			], ['class' => 'buttonbar'] ),
		], [
			'action'	=> './manage/ip/lock/transport/import',
			'method'	=> 'post',
			'enctype'	=> 'multipart/form-data'
		] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );

return $panelImport;
