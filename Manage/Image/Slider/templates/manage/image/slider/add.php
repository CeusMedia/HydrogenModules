<?php

$iconCancel	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-check icon-white' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.' '.$words['add']['buttonCancel'], array(
	'href'	=> './manage/image/slider',
	'class'	=> 'btn btn-small',
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.' '.$words['add']['buttonSave'], array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
) );

$optStatus		= UI_HTML_Elements::Options( $words['states'], $data->get( 'status', NULL ) );
$optEasing		= UI_HTML_Elements::Options( $words['optEasing'], $data->get( 'easing' ) );
$optAnimation	= UI_HTML_Elements::Options( $words['optAnimation'], $data->get( 'animation' ) );
$optShowButtons	=  UI_HTML_Elements::Options( $words['optBoolean'], $data->get( 'showButtons' ) );
$optShowDots	=  UI_HTML_Elements::Options( $words['optBoolean'], $data->get( 'showDots' ) );
$optShowTitle	=  UI_HTML_Elements::Options( $words['optBoolean'], $data->get( 'showTitle' ) );
$optScaleToFit	=  UI_HTML_Elements::Options( $words['optBoolean'], $data->get( 'scaleToFit' ) );
$optRandomOrder	=  UI_HTML_Elements::Options( $words['optBoolean'], $data->get( 'randomOrder' ) );

$wordsSlider	= (object) $words['slider'];

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/manage/image/slider' ) );

return $textTop.'
<div class="content-panel">
	<h3>'.$words['add']['heading'].'</h3>
	<div class="content-panel-inner">
		<form action="./manage/image/slider/add" method="post">
			<div class="row-fluid">
				<div class="span4">
					<label for="input_title" class="mandatory required">'.$wordsSlider->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $data->get( 'title' ), ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span6">
					<label for="input_path"><abbr title="'.$wordsSlider->labelPath_title.'">'.$wordsSlider->labelPath.'</abbr></label>
					<input type="text" name="path" id="input_path" class="span12" required="required" value="'.htmlentities( $data->get( 'path' ), ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_status">'.$wordsSlider->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span2">
					<label for="input_width"><abbr title="'.$wordsSlider->labelWidth_title.'">'.$wordsSlider->labelWidth.'</abbr> <small class="muted">'.$wordsSlider->labelWidth_suffix.'</small></label>
					<input type="text" name="width" id="input_width" class="span12" required="required" value="'.$data->get( 'width' ).'"/>
				</div>
				<div class="span2">
					<label for="input_height"><abbr title="'.$wordsSlider->labelHeight_title.'">'.$wordsSlider->labelHeight.'</abbr> <small class="muted">'.$wordsSlider->labelHeight_suffix.'</small></label>
					<input type="text" name="height" id="input_height" class="span12" required="required" value="'.$data->get( 'height' ).'"/>
				</div>
				<div class="span2">
					<label for="input_durationShow"><abbr title="'.$wordsSlider->labelDurationShow_title.'">'.$wordsSlider->labelDurationShow.'</abbr> <small class="muted">'.$wordsSlider->labelDurationShow_suffix.'</small></label>
					<input type="text" name="durationShow" id="input_durationShow" class="span12" required="required" value="'.$data->get( 'durationShow' ).'"/>
				</div>
				<div class="span2">
					<label for="input_durationSlide"><abbr title="'.$wordsSlider->labelDurationSlide_title.'">'.$wordsSlider->labelDurationSlide.'</abbr> <small class="muted">'.$wordsSlider->labelDurationSlide_suffix.'</small></label>
					<input type="text" name="durationSlide" id="input_durationSlide" class="span12" required="required" value="'.$data->get( 'durationSlide' ).'"/>
				</div>
				<div class="span2">
					<label for="input_easing"><abbr title="'.$wordsSlider->labelEasing_title.'">'.$wordsSlider->labelEasing.'</abbr> <small class="muted">'.$wordsSlider->labelEasing_suffix.'</small></label>
					<select name="easing" id="input_easing" class="span12">'.$optEasing.'</select>
				</div>
				<div class="span2">
					<label for="input_animation"><abbr title="'.$wordsSlider->labelAnimation_title.'">'.$wordsSlider->labelAnimation.'</abbr> <small class="muted">'.$wordsSlider->labelAnimation_suffix.'</small></label>
					<select name="animation" id="input_animation" class="span12">'.$optAnimation.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span2">
					<label for="input_showDots"><abbr title="'.$wordsSlider->labelShowDots_title.'">'.$wordsSlider->labelShowDots.'</abbr></label>
					<select name="showDots" id="input_showDots" class="span12">'.$optShowDots.'</select>
				</div>
				<div class="span2">
					<label for="input_showButtons"><abbr title="'.$wordsSlider->labelShowButtons_title.'">'.$wordsSlider->labelShowButtons.'</abbr></label>
					<select name="showButtons" id="input_showButtons" class="span12">'.$optShowButtons.'</select>
				</div>
				<div class="span2">
					<label for="input_showTitle"><abbr title="'.$wordsSlider->labelShowTitle_title.'">'.$wordsSlider->labelShowTitle.'</abbr></label>
					<select name="showTitle" id="input_showTitle" class="span12">'.$optShowTitle.'</select>
				</div>
				<div class="span2">
					<label for="input_scaleToFit"><abbr title="'.$wordsSlider->labelScaleToFit_title.'">'.$wordsSlider->labelScaleToFit.'</abbr></label>
					<select name="scaleToFit" id="input_scaleToFit" class="span12">'.$optScaleToFit.'</select>
				</div>
				<div class="span2">
					<label for="input_randomOrder"><abbr title="'.$wordsSlider->labelRandomOrder_title.'">'.$wordsSlider->labelRandomOrder.'</abbr></label>
					<select name="randomOrder" id="input_randomOrder" class="span12">'.$optRandomOrder.'</select>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonCancel.'
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>';
