<?php
$listImages	= '<div><em><small class="muted">'.$words['editImages']['noImages'].'</small></em></div>';
if( $images ){
	$list	= array();
	foreach( $images as $image ){
		$thumb		= $this->renderThumbnail( $image, TRUE, $gallery->path );
		$thumb		= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $baseUri.$gallery->path.'/'.$image->filename ) );
		$urlRemove	= './manage/gallery/removeImage/'.$image->galleryImageId;
		$source		= new UI_Image( $baseUri.$gallery->path.'/'.$image->filename );
		$size		= $source->getWidth().'x'.$source->getHeight();
		$item		= '
<div class="row-fluid">
	<div class="span3">
		<div class="thumbnail">
			'.$thumb.'
		</div>
	</div>
	<div class="span9">
		<form action="./manage/gallery/editImage/'.$image->galleryImageId.'" method="post">
			<table class="table table-condensed">
				<colgroup>
					<col width="20%"/>
					<col width="25%"/>
					<col width="55%"/>
				</colgroup>
				<tbody>
					<tr>
						<th>'.$words['editImages']['itemDimensions'].'</th>
						<th>'.$words['editImages']['itemDate'].'</th>
						<th>'.$words['editImages']['itemFilename'].'</th>
					</tr>
					<tr>
						<td>'.$source->getWidth().'x'.$source->getHeight().' '.$words['editImages']['itemDimensionsUnit'].'</td>
						<td>'.date( "d.m.Y", filemtime( $source->getFileName() ) ).' <small class="muted">'.date( "H:i:s", filemtime( $source->getFileName() ) ).'</small></td>
						<td>'.$image->filename.'</td>
					</tr>
				</tbody>
			</table>
			<div class="row-fluid">
				<div class="span1">
					<label for="input_rank">'.$words['editImages']['labelRank'].'</label>
					<input type="text" name="rank" id="input_rank" class="span12" maxlength="3" value="'.htmlentities( $image->rank, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span11">
					<label for="input_title">'.$words['editImages']['labelTitle'].'</label>
					<textarea name="title" id="input_title" class="span12" rows="2">'.htmlentities( $image->title, ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<div class="row-fluid">
				<button type="submit" class="btn btn-small btn-primary"><i class="icon-ok icon-white"></i> '.$words['editImages']['buttonSave'].'</button>
				<button type="button" class="btn btn-mini btn-danger" onclick="if(confirm(\''.addslashes( $words['editImages']['buttonRemoveConfirm'] ).'\'))document.location.href=\''.$urlRemove.'\';"><i class="icon-remove icon-white"></i> '.$words['editImages']['buttonRemove'].'</button>
			</div>
		</form>
	</div>
</div>';
		$list[]	= UI_HTML_Tag::create( 'li', $item, array( 'class' => 'gallery-image' ) );
	}
	$listImages	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'gallery-images' ) );
}

return '
<div class="content-panel">
	<div class="content-panel-inner">
		'.$listImages.'
		<div class="buttonbar">
			<button type="button" class="btn btn-small not-btn-info btn-success" onclick="$(\'#gallery-editor-tab-3>a\').trigger(\'click\');"><i class="icon-plus icon-white"></i> '.$words['editImages']['buttonAdd'].'</button>
		</div>
	</div>
</div>';
?>
