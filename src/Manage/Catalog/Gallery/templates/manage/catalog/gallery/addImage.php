<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$w		= (object) $words['addImage'];

$panelCategories	= $view->loadTemplateFile( 'manage/catalog/gallery/index.categories.php' );

$optCategory	= [];
foreach( $categories as $item )
	$optCategory[$item->galleryCategoryId]  = $item->title;
$optCategory	= HtmlElements::Options( $optCategory, $categoryId );

$optStatus		= HtmlElements::Options( $words['states'], 0 );

$image->price	= $image->price ? $image->price : $category->price;

$iconFolder		= '<i class="icon-folder-open icon-white"></i>';

$panelAdd	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/catalog/gallery/addImage/'.$categoryId.'" enctype="multipart/form-data" method="post">
			<div class="row-fluid">
				<div class="span10">
					<label for="input_category">'.$w->labelCategory.'</label>
					<select name="category" id="input_category" class="span12">'.$optCategory.'</select>
				</div>
				<div class="span2">
					<label for="input_rank">'.$w->labelRank.'</label>
					<input type="text" name="rank" id="input_rank" class="span12" value="'.$image->rank.'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_upload">'.$w->labelUpload.'</label>
					'.View_Helper_Input_File::renderStatic( $env, 'upload', $iconFolder ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">'.$w->labelTitle.'</label>
					<textarea name="title" id="input_title" class="span12" rows="4">'.$image->title.'</textarea>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_price"><abbr title="'.$w->labelPrice_title.'">'.$w->labelPrice.'</abbr> <small class="muted">'.$w->labelPrice_suffix.'</small></label>
					<input type="text" name="price" id="input_price" class="span12" value="'.$image->price.'"/>
				</div>
<!--				<div class="span3">
					<label for="input_type">Type</label>
					<input type="text" name="type" id="input_type" class="span12" value="'.$image->type.'"/>
				</div>-->
				<div class="span3">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/catalog/gallery/editCategory/'.$categoryId.'" class="btn btn-small"><i class="icon-arrow-left"></i>&nbsp;'.$w->buttonCancel.'</a>
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>';

if( $moduleConfig->get( 'layout' ) == 'matrix' ){
	return '
	<div class="row-fluid">
		<div class="span6">
			'.$panelAdd.'
		</div>
	</div>';
}


return '
<div class="row-fluid">
	<div class="span4">
		'.$panelCategories.'
	</div>
	<div class="span8">
		'.$panelAdd.'
	</div>
</div>';
?>
