<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$optFolder	= UI_HTML_Elements::Options( $folders, $file->downloadFolderId );


$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' zurück', array(
	'href'		=> './info/file/index/'.$file->downloadFolderId,
	'class'		=> 'btn',
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.' speichern', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
) );
$buttonRemove	= '';
if( in_array( 'remove', $rights ) )
	$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.' entfernen', array(
		'href'		=> './info/file/remove/'.$file->downloadFileId,
		'class'		=> 'btn btn-danger',
	) );

$panelEdit	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Datei verändern <small class="muted">(umbenennen oder verschieben)</small>' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'form', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Dateiname', array( 'for' => 'input_title' ) ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'		=> 'text',
						'name'		=> 'title',
						'id'		=> 'input_title',
						'class'		=> 'span12',
						'value'		=> htmlentities( $file->title, ENT_QUOTES, 'UTF-8' ),
						'required'	=> 'required',
					) ),
				), array( 'class' => 'span6' ) ),
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'In Ordner', array( 'for' => 'input_folderId' ) ),
					UI_HTML_Tag::create( 'select', $optFolder, array(
						'type'		=> 'text',
						'name'		=> 'folderId',
						'id'		=> 'input_folderId',
						'class'		=> 'span12',
						'required'	=> 'required',
					) ),
				), array( 'class' => 'span6' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', join( ' ', array(
				$buttonCancel,
				$buttonSave,
				$buttonRemove
			) ), array( 'class' => 'buttonbar' ) ),
		), array( 'action' => './info/file/editFile/'.$file->downloadFileId, 'method' => 'post' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/info/file/' ) );

return $textIndexTop.'
<!--<h3>Dateien</h3>-->
<div>'.View_Info_File::renderPosition( $env, $file->downloadFolderId, NULL ).'</div><br/>'
.UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		$panelEdit,
	), array( 'class' => 'span9' ) ),
), array( 'class' => 'row-fluid' ) );
