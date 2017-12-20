<?php

$w		= (object) $words['editImage'];

$helperTime	 = new View_Helper_TimePhraser( $env );

$panelCategories	= $view->loadTemplateFile( 'manage/catalog/gallery/index.categories.php' );

$preview	= UI_HTML_Tag::create( 'a', UI_HTML_Tag::create( 'img', NULL, array(
		'src'   => $pathPreview.rawurlencode( $category->path ).'/'.$image->filename,
		'class' => 'img-polaroid',
	) ),
	array(
		'href'  => $pathPreview.$category->path.'/'.$image->filename,
		'href'	=> './manage/catalog/gallery/viewOriginal/'.$image->galleryImageId.'?.jpg',
		'class' => 'fancybox-auto',
) );

$uriOriginal	=  './manage/catalog/gallery/viewOriginal/'.$image->galleryImageId;
$uriPreview		=  $pathPreview.rawurlencode( $category->path ).'/'.$image->filename;
$uriThumbnail	=  $pathThumbnail.rawurlencode( $category->path ).'/'.$image->filename;


$optCategory	= array();
foreach( $categories as $item )
	$optCategory[$item->galleryCategoryId]  = $item->title;
$optCategory	= UI_HTML_Elements::Options( $optCategory, $image->galleryCategoryId );

$optStatus		= UI_HTML_Elements::Options( $words['states'], $image->status );

$imageTitle		= strlen( trim( $image->title ) ) ? $image->title : $image->filename;



$panelEdit  	= '
<div class="content-panel">
	<h3><span class="autocut"><span class="muted"><a href="./manage/catalog/gallery/editCategory/'.$category->galleryCategoryId.'" class="muted">'.$w->heading.'</a>: </span>'.htmlentities( $imageTitle, ENT_QUOTES, 'UTF-8' ).'</span></h3>
	<div class="content-panel-inner">
		<form action="./manage/catalog/gallery/editImage/'.$image->galleryImageId.'" enctype="multipart/form-data" method="post">
			<div class="row-fluid">
				<div class="span12">
					<div class="row-fluid">
						<div class="span10">
							<label for="input_categoryId">'.$w->labelCategory.'</label>
							<select name="categoryId" id="input_categoryId" class="span12">'.$optCategory.'</select>
						</div>
						<div class="span2">
							<label for="input_rank">'.$w->labelRank.'</label>
							<input type="text" name="rank" id="input_rank" class="span12" value="'.$image->rank.'"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_upload">'.$w->labelUpload.'</label>
							'.View_Helper_Input_File::render( 'upload', '<i class="icon-folder-open icon-white"></i>' ).'
						</div>
					</div>
					<div class="row-fluid">
						<div class="span10">
							<label for="input_filename">'.$w->labelFilename.'</label>
							<input type="text" name="filename" id="input_filename" class="span12" value="'.htmlentities( $image->filename, ENT_QUOTES, 'UTF-8' ).'"/>
						</div>
						<div class="span2">
							<label for="input_id">'.$w->labelId.'</label>
							<input type="text" name="id" id="input_id" class="span12" value="'.$image->galleryImageId.'" readonly="readonly"/>
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
							<label for="input_price">'.$w->labelPrice.' <small class="muted">'.$w->labelPrice_suffix.'</small></label>
							<input type="text" name="price" id="input_price" class="span12" value="'.$image->price.'"/>
						</div>
						<div class="span3">
							<label for="input_price">'.$w->labelCategoryPrice.'</label>
							<input type="text" name="price" id="input_price" class="span12" value="'.$category->price.'" readonly="readonly"/>
						</div>
<!--						<div class="span3">
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
						<a href="./manage/catalog/gallery/removeImage/'.$imageId.'" onclick="if(!confirm(\''.$w->buttonRemove_confirm.'\')) return false;" class="btn btn-danger btn-small"><i class="icon-trash icon-white"></i>&nbsp;'.$w->buttonRemove.'</a>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>';


$width	= $imageObject->getWidth();
$height	= $imageObject->getHeight();
$pixels	= round( $width * $height / 1048576, 1 );

$w	= (object) $words['editImage.info'];
$panelInfo	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span6">
				'.$preview.'
			</div>
			<div class="span6">
				<dl class="dl-horizontal autocut">
					<dt>'.$w->labelId.':</dt>
					<dd><a href="'.$frontend->getUri().'catalog/gallery/image/'.$image->galleryImageId.'" target="_blank">'.$image->galleryImageId.'</a></dd>
					<dt>'.$w->labelUploaded.':</dt>
					<dd>'.( $image->createdAt ? $helperTime->convert( $image->createdAt, TRUE, $w->timePrasePrefix, $w->timePraseSuffix ) : '-' ).'</dd>
					<dt>'.$w->labelTaken.':</dt>
					<dd>'.( $image->takenAt ? $helperTime->convert( $image->takenAt, TRUE, $w->timePrasePrefix, $w->timePraseSuffix ) : '-' ).'</dd>
					<dt>'.$w->labelResolution.'</dt>
					<dd>'.$width.'&times;'.$height.' px</dd>
					<dt>'.$w->labelSize.'</dt>
					<dd>'.$pixels.' MP</dd>
					<dt>Resources</dt>
					<dd><a href="'.$uriOriginal.'?'.time().'" target="_blank">Original</a></dd>
					<dd><a href="'.$uriPreview.'?'.time().'" target="_blank">Preview</a></dd>
					<dd><a href="'.$uriThumbnail.'?'.time().'" target="_blank">Thumbnail</a></dd>
				</dl>
			</div>
		</div>
	</div>
</div>';

$panelSlider	= '';
if( $env->getModules()->has( 'UI_Image_Slider' ) ){
	$w				= (object) $words['editImage.slider'];
	$modelSliders	= new Model_Image_Slider( $env );
	$sliders		= $modelSliders->getAll( array(), array() );
	$optSlider		= array();
	foreach( $sliders as $slider )
		$optSlider[$slider->sliderId]	= $slider->title;
	$optSlider		= UI_HTML_Elements::Options( $optSlider );

	$optPositionX	= UI_HTML_Elements::Options( $words['slider-positions-x'], 'center' );
	$optPositionY	= UI_HTML_Elements::Options( $words['slider-positions-y'], 'center' );

	$panelSlider	= '
	<div class="content-panel">
		<h3>In Slider einfügen</h3>
		<div class="content-panel-inner">
			<form action="./manage/catalog/gallery/addImageToSlider/'.$image->galleryImageId.'" method="post">
				<div class="row-fluid">
					<div class="span10">
						<label for="input_sliderId">'.$w->labelSlider.'</label>
						<select name="sliderId" id="input_sliderId" class="span12">'.$optSlider.'</select>
					</div>
				</div>
				<div class="row-fluid">
					<div class="span6">
						<label for="input_positionX">'.$w->labelPositionX.'</label>
						<select name="positionX" id="input_positionX" class="span12">'.$optPositionX.'</select>
					</div>
					<div class="span6">
						<label for="input_positionY">'.$w->labelPositionY.'</label>
						<select name="positionY" id="input_positionY" class="span12">'.$optPositionY.'</select>
					</div>
				</div>
				<div class="buttonbar">
					<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
				</div>
			</form>
		</div>
	</div>';
}

if( $moduleConfig->get( 'layout' ) == 'matrix' ){
	return '
	<div class="row-fluid">
		<div class="span6">
			'.$panelEdit.'
		</div>
		<div class="span6">
			'.$panelInfo.'
			'.$panelSlider.'
		</div>
	</div>';
}

return '
<div class="row-fluid">
	<div class="span4">
		'.$panelCategories.'
	</div>
	<div class="span8">
		'.$panelEdit.'
		'.$panelInfo.'
	</div>
</div>';
?>
