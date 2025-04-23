<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

/** @var object $gallery */
/** @var string $baseUri */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['edit'];

$optStatus	= $words['states'];
$optStatus	= HtmlElements::Options( $optStatus, $gallery->status );

return '
<div class="content-panel">
	<div class="content-panel-inner">
		<form action="./manage/gallery/edit/'.$gallery->galleryId.'" method="post">
			<div class="row-fluid">
				<div class="span1">
					<label for="input_rank">'.$w->labelRank.'</label>
					<input type="text" name="rank" id="input_rank" class="span12" maxlength="2" value="'.$gallery->rank.'"/>
				</div>
				<div class="span6">
					<label for="input_title">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" maxlength="" value="'.htmlentities( $gallery->title, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
				</div>
				<div class="span3">
					<label for="input_path"><abbr title="'.$w->labelPathHint.'">'.$w->labelPath.'</abbr></label>
					<input type="text" name="path" id="input_path" class="span12" maxlength="60" value="'.htmlentities( $gallery->path, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
				</div>
				<div class="span2">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label>'.$w->labelDescription.'</label>
					<textarea name="description" class="span12 TinyMCE" rows="6" data-tinymce-mode="minimal">'.htmlentities( $gallery->description, ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<button type="button" class="btn btn-small" onclick="document.location.href=\'./manage/gallery\';"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</button>
			<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
			<button type="button" class="btn btn-small btn-danger" onclick="if(confirm(\''.addslashes( $w->buttonRemoveConfirm ).'\'))document.location.href=\'./manage/gallery/remove/'.$gallery->galleryId.'\';"><i class="icon-remove icon-white"></i> '.$w->buttonRemove.'</button>
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
