<?php

use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array $words */
/** @var array $rights */
/** @var int|string $folderId */

if( !in_array( 'upload', $rights ) )
	return '';

$w			= (object) $words['upload'];
$iconFile	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-folder'] );
$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-upload'] );

$helper		= new View_Helper_Input_File( $env );
$helper->setName( 'upload' );
//$helper->setLabel( $w->labelFile );
$helper->setLabel( $iconFile );
$helper->setRequired( TRUE );

$maxSize	= UnitFormater::formatBytes( Logic_Upload::getMaxUploadSize() );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h4', $w->heading ),
	HtmlTag::create( 'div',  [
		HtmlTag::create( 'form', [
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'small', [
						HtmlTag::create( 'em', sprintf( $w->hintMaxSize, $maxSize ), ['class' => 'muted'] ),
					] ),
				], ['class' => 'span12'] ),
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', $helper->render(), ['class' => 'span12'] ),
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'button', $iconSave.' '.$w->buttonSave, [
					'type'		=> 'submit',
					'name'		=> 'save',
					'class'		=> 'btn btn-small btn-success'
				] )
			], ['class' => 'buttonbar'] )
		], [
			'action'	=> './info/file/upload/'.$folderId,
			'method'	=> 'post',
			'enctype'	=> 'multipart/form-data'
		] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );
