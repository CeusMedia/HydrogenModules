<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use View_Manage_Image_Slider as View;

/** @var View $view */
/** @var array<string,array<string,string>> $words */
/** @var Entity_Image_Slide $slide */

$w		= (object) $words['editSlide'];

$optStatus	= $words['states'];
$optStatus	= HtmlElements::Options( $optStatus, $slide->status );

$wordsSlide	= (object) $words['editSlide'];

extract( $view->populateTexts( ['top', 'bottom'], 'html/manage/image/slider' ) );

return $textTop.'
<div class="content-panel">
	<h3 class="autocut">'.sprintf( $w->heading, htmlentities( $slider->title, ENT_QUOTES, 'UTF-8' ), $slide->source ).'</h3>
	<div class="content-panel-inner">
		<form action="./manage/image/slider/editSlide/'.$slideId.'" method="post">
			<div class="row-fluid">
				<div class="span6">
					<label for="input_source" class="autocut">'.$view->label( 'editSlide', 'source' ).'</label>
					<input type="text" name="source" id="input_source" class="span12" value="'.htmlentities( $slide->source, ENT_QUOTES, 'UTF-8' ).'" readonly="readonly"/>
				</div>
				<div class="span4">
					<label for="input_status" class="autocut">'.$view->label( 'editSlide', 'status' ).'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
				<div class="span2">
					<label for="input_rank" class="autocut">'.$view->label( 'editSlide', 'rank' ).'</label>
					<input type="text" name="rank" id="input_rank" class="span12" value="'.(int) $slide->rank.'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span8">
					<label for="input_title" class="autocut">'.$view->label( 'editSlide', 'title' ).'</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $slide->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span4">
					<label for="input_link" class="autocut">'.$view->label( 'editSlide', 'link' ).'</label>
					<input type="text" name="link" id="input_link" class="span12" value="'.htmlentities( $slide->link, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_content" class="autocut">'.$view->label( 'editSlide', 'content' ).'</label>
					<textarea name="content" id="input_content" class="span12 CodeMirror-auto" rows="10">'.htmlentities( $slide->content, ENT_QUOTES, 'UTF-8' ).'</textarea>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./manage/image/slider/edit/'.$slider->sliderId.'" class="btn btn-small"><i class="icon-arrow-left"></i>&nbsp;'.$w->buttonCancel.'</a>
				<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
				<a href="./manage/image/slider/removeSlide/'.$slideId.'" class="btn btn-small btn-danger"><i class="icon-remove icon-white"></i>&nbsp;'.$w->buttonRemove.'</a>
			</div>
		</form>
	</div>
</div>';

$panelInfo	= $view->loadTemplateFile( 'manage/image/slider/editSlide.info.php' );

return '
<div class="row-fluid">
	<div class="span9">
		'.$panelEdit.'
	</div>
	<div class="span3">
		'.$panelInfo.'
	</div>
</div>';
