<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$optStatus	= $words['states'];
$optStatus	= HtmlElements::Options( $optStatus, $gallery->status );

$iconOpen	= HtmlTag::create( 'i', '', ['class' => 'icon-folder-open icon-white'] );

$helperUpload	= new View_Helper_Input_File( $env );
$helperUpload->setName( 'file' );
$helperUpload->setLabel( $iconOpen );
$helperUpload->setRequired( TRUE );

return '
<div class="content-panel">
	<div class="content-panel-inner">
		<div class="row-fluid">
			<form action="./manage/gallery/addImage/'.$gallery->galleryId.'" method="post" enctype="multipart/form-data">
				<div class="row-fluid">
					<div class="span12">
						<label for="input_file">'.$words['editUpload']['labelFile'].'</label>
						'.$helperUpload->render().'
					</div>
				</div>
				<div class="row-fluid">
					<div class="span1">
						<label for="input_rank">'.$words['editUpload']['labelRank'].'</label>
						<input type="text" name="rank" id="input_rank" class="span12" maxlength="2" value="'.$nextRank.'"/>
					</div>
					<div class="span11">
						<label for="input_title">'.$words['editUpload']['labelTitle'].'</label>
						<input type="text" name="title" id="input_title" class="span12" maxlength="120"/>
					</div>
				</div>
				<button type="button" class="btn btn-small" onclick="document.location.href=\'./manage/gallery\';"><i class="icon-arrow-left"></i> '.$words['editUpload']['buttonCancel'].'</button>
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> '.$words['editUpload']['buttonSave'].'</button>
			</form>
		</div>
	</div>
</div>';
