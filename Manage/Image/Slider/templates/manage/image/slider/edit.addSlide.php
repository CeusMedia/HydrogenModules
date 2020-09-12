<?php
$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$iconUpload		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-folder-open icon-white' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconAdd		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-upload' ) );
	$iconUpload		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-folder-open' ) );
}

$rank		= count( $slider->slides ) + 1;
$minWidth	= ceil( $slider->width / 2 );
$minHeight	= ceil( $slider->height / 2 );

$wordsSlide	= (object) $words['edit.addSlide'];

$optPositionX	= UI_HTML_Elements::Options( $words['slide-position-x'], 'center' );
$optPositionY	= UI_HTML_Elements::Options( $words['slide-position-x'], 'center' );

$helperUpload	= new View_Helper_Input_File( $env );
$helperUpload->setName( 'image' );
$helperUpload->setLabel( $iconUpload );
$helperUpload->setRequired( TRUE );

return '
<div class="content-panel">
	<h3>'.$words['edit.addSlide']['heading'].'</h3>
	<div class="content-panel-inner">
		<form action="./manage/image/slider/addSlide/'.$slider->sliderId.'" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_image">'.$wordsSlide->labelFile.' <small class="muted">'.sprintf( $wordsSlide->labelFile_suffix, $minWidth, $minHeight ).'</small></label>
					'.$helperUpload->render().'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span10">
					<label for="input_title">'.$wordsSlide->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12"/>
				</div>
				<div class="span2">
					<label for="input_rank">'.$wordsSlide->labelRank.'</label>
					<input type="text" name="rank" id="input_rank" class="span12" value="'.$rank.'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_link">'.$view->label( 'edit.addSlide', 'link' ).'</label>
					<input type="text" name="link" id="input_link" class="span12"/>
				</div>
			</div>
			'.$wordsSlide->textCrop.'
			<div class="row-fluid">
				<div class="span6">
					<label for="input_positionX">'.$wordsSlide->labelPositionX.'</label>
					<select name="positionX" id="input_positionX" class="span12">'.$optPositionX.'</select>
				</div>
				<div class="span6">
					<label for="input_positionY">'.$wordsSlide->labelPositionY.'</label>
					<select name="positionY" id="input_positionY" class="span12">'.$optPositionY.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary">'.$iconAdd.'&nbsp;'.$words['edit.addSlide']['buttonSave'].'</button>
			</div>
		</form>
	</div>
</div>';
