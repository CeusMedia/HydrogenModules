<?php
if( !in_array( 'upload', $rights ) )
	return '';

$iconFile	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-folder' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-upload' ) );

$helper		= new View_Helper_Input_File( $env );
$helper->setName( 'upload' );
//$helper->setLabel( $words['upload']['labelFile'] );
$helper->setLabel( $iconFile );
$helper->setRequired( TRUE );

return '
<div class="content-panel">
	<h4>'.$words['upload']['heading'].'</h4>
	<div class="content-panel-inner">
		<form action="./info/file/upload/'.$folderId.'" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					'.$helper->render().'
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-small btn-success">'.$iconSave.' '.$words['upload']['buttonSave'].'</button>
			</div>
		</form>
	</div>
</div>';
