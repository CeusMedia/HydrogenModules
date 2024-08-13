<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var array $folders */
/** @var object $file */
/** @var array $rights */

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

$optFolder	= HtmlElements::Options( $folders, $file ? $file->downloadFolderId : '' );


$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' zurück', [
	'href'		=> './info/file/index/'.$file->downloadFolderId,
	'class'		=> 'btn',
] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.' speichern', [
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
] );
$buttonRemove	= '';
if( in_array( 'remove', $rights ) )
	$buttonRemove	= HtmlTag::create( 'a', $iconRemove.' entfernen', [
		'href'		=> './info/file/remove/'.$file->downloadFileId,
		'class'		=> 'btn btn-danger',
	] );

$panelEdit	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', 'Datei verändern <small class="muted">(umbenennen oder verschieben)</small>' ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'form', [
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', 'Dateiname', ['for' => 'input_title'] ),
					HtmlTag::create( 'input', NULL, [
						'type'		=> 'text',
						'name'		=> 'title',
						'id'		=> 'input_title',
						'class'		=> 'span12',
						'value'		=> htmlentities( $file->title, ENT_QUOTES, 'UTF-8' ),
						'required'	=> 'required',
					] ),
				], ['class' => 'span6'] ),
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', 'In Ordner', ['for' => 'input_folderId'] ),
					HtmlTag::create( 'select', $optFolder, [
						'type'		=> 'text',
						'name'		=> 'folderId',
						'id'		=> 'input_folderId',
						'class'		=> 'span12',
						'required'	=> 'required',
					] ),
				], ['class' => 'span6'] ),
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', join( ' ', [
				$buttonCancel,
				$buttonSave,
				$buttonRemove
			] ), ['class' => 'buttonbar'] ),
		], ['action' => './info/file/editFile/'.$file->downloadFileId, 'method' => 'post'] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );

extract( $view->populateTexts( ['index.top', 'index.bottom'], 'html/info/file/' ) );

return join( [
	$textIndexTop,
//	'<!--<h3>Dateien</h3>-->',
	HtmlTag::create( 'div', View_Info_File::renderPosition( $env, $file->downloadFolderId, NULL ) ),
	HtmlTag::create( 'br' ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'div', $panelEdit, ['class' => 'span9'] ),
	], ['class' => 'row-fluid'] )
] );
