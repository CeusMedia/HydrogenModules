<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$w	= (object) $words['addCategory'];

$optStatus	= HtmlElements::Options( $words['states'], $category->status );

$panelCategory	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/catalog/gallery/addCategory/'.$parentCategoryId.'" method="post">
			<div class="row-fluid">
				<div class="span8">
					<label for="input_title">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $category->title, ENT_QUOTES, 'UTF-8' ).'" required="required" onkeyup="ManageCatalogGallery.updatePath(this)"/>
				</div>
				<div class="span4">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span8">
					<label for="input_path">'.$w->labelPath.'</label>
					<input type="text" name="path" id="input_path" class="span12" value="'.htmlentities( $category->path, ENT_QUOTES, 'UTF-8' ).'" required="required" onkeyup="ManageCatalogGallery.updatePath(this)"/>
				</div>
<!--				<div class="span2">
					<label for="input_price"><abbr title="'.$w->labelPrice_title.'">'.$w->labelPrice.'</abbr> <small class="muted">'.$w->labelPrice_suffix.'</small></label>
					<input type="text" name="price" id="input_price" class="span12" value="'.htmlentities( $category->price, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>-->
				<div class="span2">
					<label for="input_rank">'.$w->labelRank.'</label>
					<input type="text" name="rank" id="input_rank" class="span12" value="'.$category->rank.'"/>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/catalog/gallery" class="btn btn-small"><i class="icon-arrow-left"></i>&nbsp;'.$w->buttonCancel.'</a>
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>';

$panelCategories	= $view->loadTemplateFile( 'manage/catalog/gallery/index.categories.php' );

if( $moduleConfig->get( 'layout' ) == 'matrix' ){
	return '
	<!--<h2><span class="muted">Galerien</span></h2>-->
	<div class="row-fluid">
		<div class="span6">
			'.$panelCategory.'
		</div>
	</div>';
}

return '
	<!--<h2><span class="muted">Galerien</span></h2>-->
	<div class="row-fluid">
		<div class="span4">
			'.$panelCategories.'
		</div>
		<div class="span8">
			'.$panelCategory.'
		</div>
	</div>';
