<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

/** @var View_Manage_Gallery $view */
/** @var object $gallery */
/** @var array<string,array<string,string>> $words */

$wIndex	= (object) $words['index'];
$wAdd	= (object) $words['addGallery'];

$galleries	= $view->renderList();

$optStatus	= $words['states'];
$optStatus	= HtmlElements::Options( $optStatus, $gallery->status );

extract( $view->populateTexts( ['top', 'bottom'], 'html/manage/gallery' ) );

return $textTop.'
<div class="row-fluid">
	<div class="span3">
		<div class="content-panel">
			<h3>'.$wIndex->heading.'</h3>
			<div class="content-panel-inner" id="not-layout-gallery-list">
				'.$galleries.'
				<div class="buttonbar">
					<a href="./manage/gallery/add" class="btn btn-small not-btn-info btn-success"><i class="icon-plus icon-white"></i> '.$wIndex->buttonAdd.'</a>
				</div>
			</div>
		</div>
	</div>
	<div class="span9">
		<div class="content-panel">
			<h3>'.$wAdd->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./manage/gallery/add" method="post">
					<div class="row-fluid">
						<div class="span1">
							<label for="input_rank">'.$wAdd->labelRank.'</label>
							<input type="text" name="rank" id="input_rank" class="span12" maxlength="2" value="'.$gallery->rank.'"/>
						</div>
						<div class="span5">
							<label for="input_title">'.$wAdd->labelTitle.'</label>
							<input type="text" name="title" id="input_title" class="span12" maxlength="120" value="'.htmlentities( $gallery->title ?? '', ENT_QUOTES, 'UTF-8' ).'" required="required"/>
						</div>
						<div class="span4">
							<label for="input_path"><abbr title="'.$wAdd->labelPathHint.'">'.$wAdd->labelPath.'</abbr></label>
							<input type="text" name="path" id="input_path" class="span12" maxlength="60" value="'.htmlentities( $gallery->path ?? '', ENT_QUOTES, 'UTF-8' ).'" required="required"/>
						</div>
						<div class="span2">
							<label for="input_status">'.$wAdd->labelStatus.'</label>
							<select name="status" id="input_status" class="span12" disabled="disabled" readonly="readonly">'.$optStatus.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label>'.$wAdd->labelDescription.'</label>
							<textarea name="description" class="span12 TinyMCE" rows="6" data-tinymce-mode="minimal">'.htmlentities( $gallery->description ?? '', ENT_QUOTES, 'UTF-8' ).'</textarea>
						</div>
					</div>
					<button type="button" class="btn btn-small" onclick="document.location.href=\'./manage/gallery\';"><i class="icon-arrow-left"></i> '.$wAdd->buttonCancel.'</button>
					<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> '.$wAdd->buttonSave.'</button>
				</form>
			</div>
		</div>
	</div>
</div>
'.$textBottom;
