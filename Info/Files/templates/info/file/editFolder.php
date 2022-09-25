<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconRemove		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

unset( $folders[$folder->downloadFolderId] );
$folders	= array_merge( array( 0	=> '' ), $folders );
$optFolder	= HtmlElements::Options( $folders, $folder->parentId );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' zurück', array(
	'href'		=> './info/file/index/'.$folder->parentId,
	'class'		=> 'btn',
) );
$buttonSave		= HtmlTag::create( 'button', $iconSave.' speichern', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
) );
$buttonRemove	= '';
if( in_array( 'remove', $rights ) && count( $files ) === 0 )
	$buttonRemove	= HtmlTag::create( 'a', $iconRemove.' entfernen', array(
		'href'		=> './info/file/removeFolder/'.$folder->downloadFolderId,
		'class'		=> 'btn btn-danger',
	) );

$panelEdit	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Ordner verändern <small class="muted">(umbenennen oder verschieben)</small>' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'form', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Ordnername', array( 'for' => 'input_title' ) ),
					HtmlTag::create( 'input', NULL, array(
						'type'		=> 'text',
						'name'		=> 'title',
						'id'		=> 'input_title',
						'class'		=> 'span12',
						'value'		=> htmlentities( $folder->title, ENT_QUOTES, 'UTF-8' ),
						'required'	=> 'required',
					) ),
				), array( 'class' => 'span6' ) ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'In Ordner', array( 'for' => 'input_parentId' ) ),
					HtmlTag::create( 'select', $optFolder, array(
						'type'		=> 'text',
						'name'		=> 'parentId',
						'id'		=> 'input_parentId',
						'class'		=> 'span12',
				 //		'required'	=> 'required',
					) ),
				), array( 'class' => 'span6' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', join( ' ', array(
				$buttonCancel,
				$buttonSave,
				$buttonRemove
			) ), array( 'class' => 'buttonbar' ) ),
		), array( 'action' => './info/file/editFolder/'.$folder->downloadFolderId, 'method' => 'post' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/info/file/' ) );

return $textIndexTop.'
<div>'.View_Info_File::renderPosition( $env, $folder->downloadFolderId, NULL ).'</div><br/>'
.HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', array(
		$panelEdit,
	), array( 'class' => 'span9' ) ),
), array( 'class' => 'row-fluid' ) );
