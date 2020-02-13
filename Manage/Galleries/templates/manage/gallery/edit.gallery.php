<?php
$optStatus	= $words['states'];
$optStatus	= UI_HTML_Elements::Options( $optStatus, $gallery->status );

return '
<div class="content-panel">
	<div class="content-panel-inner">
		<form action="./manage/gallery/edit/'.$gallery->galleryId.'" method="post">
			<div class="row-fluid">
				<div class="span1">
					<label for="input_rank">'.$words['edit']['labelRank'].'</label>
					<input type="text" name="rank" id="input_rank" class="span12" maxlength="2" value="'.$gallery->rank.'"/>
				</div>
				<div class="span6">
					<label for="input_title">'.$words['edit']['labelTitle'].'</label>
					<input type="text" name="title" id="input_title" class="span12" maxlength="" value="'.htmlentities( $gallery->title, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
				</div>
				<div class="span3">
					<label for="input_path"><abbr title="'.$words['edit']['labelPathHint'].'">'.$words['edit']['labelPath'].'</abbr></label>
					<input type="text" name="path" id="input_path" class="span12" maxlength="60" value="'.htmlentities( $gallery->path, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
				</div>
				<div class="span2">
					<label for="input_status">'.$words['edit']['labelStatus'].'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label>'.$words['edit']['labelDescription'].'</label>
					<textarea name="description" class="span12 TinyMCE" rows="6" data-tinymce-mode="minimal">'.htmlentities( $gallery->description, ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<button type="button" class="btn btn-small" onclick="document.location.href=\'./manage/gallery\';"><i class="icon-arrow-left"></i> '.$words['edit']['buttonCancel'].'</button>
			<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> '.$words['edit']['buttonSave'].'</button>
			<button type="button" class="btn btn-small btn-danger" onclick="if(confirm(\''.addslashes( $words['edit']['buttonRemoveConfirm'] ).'\'))document.location.href=\'./manage/gallery/remove/'.$gallery->galleryId.'\';"><i class="icon-remove icon-white"></i> '.$words['edit']['buttonRemove'].'</button>
		</form>
		<hr/>
		<h4>Short-Code</h4>
		<div class="row-fluid">
			<div class="span9">
				<p>'.$words['edit']['code'].'</p>
			</div>
			<div class="span3">
				<pre>[gallery:'.$gallery->galleryId.']</pre>
			</div>
		</div>
	</div>
</div>';
?>
