<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['editCategory'];

$optStatus	= UI_HTML_Elements::Options( $words['states'], $category->status );

$iconUpload	= '<i class="icon-folder-open icon-white"></i>';

$thumb		= "";
if( $category->image )
	$thumb	= HtmlTag::create( 'img', NULL, array(
		'src'		=> $pathImages.$category->image,
		'class'		=> 'img-polaroid',
		'width'		=> "100%",
	) );

return '
<div class="content-panel">
	<h3><span class="autocut"><span class="muted"><a href="./manage/catalog/gallery" class="muted">'.$w->heading.'</a>: </span>'.htmlentities( $category->title, ENT_QUOTES, 'UTF-8' ).'</span></h3>
	<div class="content-panel-inner">
		<form action="./manage/catalog/gallery/editCategory/'.$categoryId.'" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span9">
					<div class="row-fluid">
						<div class="span8">
							<label for="input_title">'.$w->labelTitle.'</label>
							<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $category->title, ENT_QUOTES, 'UTF-8' ).'" required="required" onkeyup="ManageCatalogGallery.updatePath(this)"/>
						</div>
						<div class="span3">
							<label for="input_status">'.$w->labelStatus.'</label>
							<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span7">
							<label for="input_path">'.$w->labelPath.'</label>
							<input type="text" name="path" id="input_path" class="span12" value="'.htmlentities( $category->path, ENT_QUOTES, 'UTF-8' ).'" required="required" onkeyup="ManageCatalogGallery.updatePath(this)"/>
						</div>
						<div class="span3">
							<label for="input_price"><abbr title="'.$w->labelPrice_title.'">'.$w->labelPrice.'</abbr> <small class="muted">'.$w->labelPrice_suffix.'</small></label>
							<input type="text" name="price" id="input_price" class="span12" value="'.htmlentities( $category->price, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span1">
							<label for="input_rank">'.$w->labelRank.'</label>
							<input type="text" name="rank" id="input_rank" class="span12" value="'.$category->rank.'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span11">
							<label for="input_path">'.$w->labelImage.'</label>
							'.View_Helper_Input_File::renderStatic( $env, 'image', $iconUpload ).'
						</div>
					</div>
				</div>
				<div class="span2">
					'.$thumb.'
				</div>
			</div>

			<div class="buttonbar">
				<a href="./manage/catalog/gallery" class="btn btn-small"><i class="icon-arrow-left"></i>&nbsp;'.$w->buttonCancel.'</a>
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
				<a href="./manage/catalog/gallery/addImage/'.$categoryId.'" class="btn btn-success"><i class="icon-plus icon-white"></i>&nbsp;'.$w->buttonAddImage.'</a>
				<a href="./manage/catalog/gallery/removeCategory/'.$categoryId.'" onclick="if(!confirm(\''.$w->buttonRemove_confirm.'\')) return false;" class="btn btn-danger btn-small"><i class="icon-trash icon-white"></i>&nbsp;'.$w->buttonRemove.'</a>
				<button type="button" onclick="document.location.href=\'./manage/catalog/gallery/removeCategoryCover/'.$categoryId.'\';" class="btn btn-mini" '.( !$category->image ? 'disabled="disabled"' : '' ).'><i class="icon-remove"></i>&nbsp;remove cover</a>
			</div>
		</form>
	</div>
</div>';
?>
