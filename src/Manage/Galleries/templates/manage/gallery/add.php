<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$galleries	= $this->renderList();

$optStatus	= $words['states'];
$optStatus	= HtmlElements::Options( $optStatus, $gallery->status );

extract( $view->populateTexts( ['top', 'bottom'], 'html/manage/gallery' ) );

return $textTop.'
<div class="row-fluid">
	<div class="span3">
		<div class="content-panel">
			<h3>'.$words['index']['heading'].'</h3>
			<div class="content-panel-inner" id="not-layout-gallery-list">
				'.$galleries.'
				<div class="buttonbar">
					<a href="./manage/gallery/add" class="btn btn-small not-btn-info btn-success"><i class="icon-plus icon-white"></i> '.$words['index']['buttonAdd'].'</a>
				</div>
			</div>
		</div>
	</div>
	<div class="span9">
		<div class="content-panel">
			<h3>'.$words['addGallery']['heading'].'</h3>
			<div class="content-panel-inner">
				<form action="./manage/gallery/add" method="post">
					<div class="row-fluid">
						<div class="span1">
							<label for="input_rank">'.$words['addGallery']['labelRank'].'</label>
							<input type="text" name="rank" id="input_rank" class="span12" maxlength="2" value="'.$gallery->rank.'"/>
						</div>
						<div class="span5">
							<label for="input_title">'.$words['addGallery']['labelTitle'].'</label>
							<input type="text" name="title" id="input_title" class="span12" maxlength="120" value="'.htmlentities( $gallery->title ?? '', ENT_QUOTES, 'UTF-8' ).'" required="required"/>
						</div>
						<div class="span4">
							<label for="input_path"><abbr title="'.$words['addGallery']['labelPathHint'].'">'.$words['addGallery']['labelPath'].'</abbr></label>
							<input type="text" name="path" id="input_path" class="span12" maxlength="60" value="'.htmlentities( $gallery->path ?? '', ENT_QUOTES, 'UTF-8' ).'" required="required"/>
						</div>
						<div class="span2">
							<label for="input_status">'.$words['addGallery']['labelStatus'].'</label>
							<select name="status" id="input_status" class="span12" disabled="disabled" readonly="readonly">'.$optStatus.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label>'.$words['addGallery']['labelDescription'].'</label>
							<textarea name="description" class="span12 TinyMCE" rows="6" data-tinymce-mode="minimal">'.htmlentities( $gallery->description ?? '', ENT_QUOTES, 'UTF-8' ).'</textarea>
						</div>
					</div>
					<button type="button" class="btn btn-small" onclick="document.location.href=\'./manage/gallery\';"><i class="icon-arrow-left"></i> '.$words['addGallery']['buttonCancel'].'</button>
					<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> '.$words['addGallery']['buttonSave'].'</button>
				</form>
			</div>
		</div>
	</div>
</div>
'.$textBottom;
