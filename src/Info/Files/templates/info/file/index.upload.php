<?php

use CeusMedia\Common\Alg\UnitFormater;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

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

return '
<div class="content-panel">
	<h4>'.$w->heading.'</h4>
	<div class="content-panel-inner">
		<form action="./info/file/upload/'.$folderId.'" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					<small><em class="muted">'.sprintf( $w->hintMaxSize, $maxSize ).'</em></small>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					'.$helper->render().'
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-small btn-success">'.$iconSave.' '.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>';
