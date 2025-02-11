<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var object $folder */
/** @var array $folders */
/** @var array $files */
/** @var array $rights */

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

unset( $folders[$folder->downloadFolderId] );
$folders	= array_merge( [0 => ''], $folders );
$optFolder	= HtmlElements::Options( $folders, $folder->parentId );

$inputTitle		= HtmlTag::create( 'input', NULL, [
	'type'		=> 'text',
	'name'		=> 'title',
	'id'		=> 'input_title',
	'class'		=> 'span12',
	'value'		=> htmlentities( $folder->title, ENT_QUOTES, 'UTF-8' ),
	'required'	=> 'required',
] );
$selectFolder	= HtmlTag::create( 'select', $optFolder, [
	'type'		=> 'text',
	'name'		=> 'parentId',
	'id'		=> 'input_parentId',
	'class'		=> 'span12',
	//		'required'	=> 'required',
] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' zurück', [
	'href'		=> './info/file/index/'.$folder->parentId,
	'class'		=> 'btn',
] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.' speichern', [
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
] );
$buttonRemove	= '';
if( in_array( 'remove', $rights ) && count( $files ) === 0 )
	$buttonRemove	= HtmlTag::create( 'a', $iconRemove.' entfernen', [
		'href'		=> './info/file/removeFolder/'.$folder->downloadFolderId,
		'class'		=> 'btn btn-danger',
	] );

$panelEdit	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', 'Ordner verändern <small class="muted">(umbenennen oder verschieben)</small>' ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'form', [
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', 'Ordnername', ['for' => 'input_title'] ),
					$inputTitle,
				], ['class' => 'span6'] ),
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', 'In Ordner', ['for' => 'input_parentId'] ),
					$selectFolder,
				], ['class' => 'span6'] ),
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', join( ' ', [
				$buttonCancel,
				$buttonSave,
				$buttonRemove
			] ), ['class' => 'buttonbar'] ),
		], ['action' => './info/file/editFolder/'.$folder->downloadFolderId, 'method' => 'post'] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );

extract( $view->populateTexts( ['index.top', 'index.bottom'], 'html/info/file/' ) );

return join( [
	$textIndexTop,
	HtmlTag::create( 'div', View_Info_File::renderPosition( $env, $folder->downloadFolderId, NULL ) ),
	HtmlTag::create( 'br' ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'div', $panelEdit, ['class' => 'span9'] ),
	], ['class' => 'row-fluid'] )
] );
