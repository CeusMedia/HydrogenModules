<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w				= (object) $words['edit'];

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'icon-check icon-white'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'icon-remove icon-white'] );
$iconView		= HtmlTag::create( 'i', '', ['class' => 'icon-eye-open icon-white'] );

if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconCancel		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-arrow-left'] );
	$iconSave		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-check'] );
	$iconRemove		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-trash'] );
	$iconView		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-eye'] );
}

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.' '.$w->buttonCancel, [
	'href'	=> './manage/image/slider',
	'class'	=> 'btn btn-small',
] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.' '.$w->buttonSave, [
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary',
] );
$buttonRemove	= HtmlTag::create( 'a', $iconRemove.' '.$w->buttonRemove, [
	'href'	=> './manage/image/slider/remove/'.$slider->sliderId,
	'class'	=> 'btn btn-danger btn-small',
] );
$buttonView		= HtmlTag::create( 'a', $iconView.' '.$w->buttonView, [
	'href'	=> './manage/image/slider/demo/'.$slider->sliderId,
	'class'	=> 'btn btn-info btn-small',
] );

$optStatus		= HtmlElements::Options( $words['states'], $slider->status );
$optEasing		= HtmlElements::Options( $words['optEasing'], $slider->easing );
$optAnimation	= HtmlElements::Options( $words['optAnimation'], $slider->animation );
$optShowButtons	= HtmlElements::Options( $words['optBoolean'], $slider->showButtons );
$optShowDots	= HtmlElements::Options( $words['optBoolean'], $slider->showDots );
$optShowTitle	= HtmlElements::Options( $words['optBoolean'], $slider->showTitle );
$optScaleToFit	= HtmlElements::Options( $words['optBoolean'], $slider->scaleToFit );
$optRandomOrder	= HtmlElements::Options( $words['optBoolean'], $slider->randomOrder );

$panelAddSlide	= $view->loadTemplateFile( 'manage/image/slider/edit.addSlide.php' );
$panelSlides	= $view->loadTemplateFile( 'manage/image/slider/edit.slides.php' );

$wordsSlider	= (object) $words['slider'];

extract( $view->populateTexts( ['top', 'bottom'], 'html/manage/image/slider' ) );

return $textTop.'
<div class="content-panel">
	<h3>'.sprintf( $words['edit']['heading'], htmlentities( $slider->title, ENT_QUOTES, 'UTF-8' ) ).'</h3>
	<div class="content-panel-inner">
		<form action="./manage/image/slider/edit/'.$slider->sliderId.'" method="post">
			<div class="row-fluid">
				<div class="span4">
					<label for="input_title" class="mandatory required">'.$wordsSlider->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $slider->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span6">
					<label for="input_path"><abbr title="'.$wordsSlider->labelPath_title.'">'.$wordsSlider->labelPath.'</abbr></label>
					<input type="text" name="path" id="input_path" class="span12" required="required" value="'.htmlentities( $slider->path, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_status">'.$wordsSlider->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span2">
					<label for="input_width"><abbr title="'.$wordsSlider->labelWidth_title.'">'.$wordsSlider->labelWidth.'</abbr> <small class="muted">'.$wordsSlider->labelWidth_suffix.'</small></label>
					<input type="text" name="width" id="input_width" class="span12" required="required" value="'.(int) $slider->width.'"/>
				</div>
				<div class="span2">
					<label for="input_height"><abbr title="'.$wordsSlider->labelHeight_title.'">'.$wordsSlider->labelHeight.'</abbr> <small class="muted">'.$wordsSlider->labelHeight_suffix.'</small></label>
					<input type="text" name="height" id="input_height" class="span12" required="required" value="'.(int) $slider->height.'"/>
				</div>
				<div class="span2">
					<label for="input_durationShow"><abbr title="'.$wordsSlider->labelDurationShow_title.'">'.$wordsSlider->labelDurationShow.'</abbr> <small class="muted">'.$wordsSlider->labelDurationShow_suffix.'</small></label>
					<input type="text" name="durationShow" id="input_durationShow" class="span12" required="required" value="'.(int) $slider->durationShow.'"/>
				</div>
				<div class="span2">
					<label for="input_durationSlide"><abbr title="'.$wordsSlider->labelDurationSlide_title.'">'.$wordsSlider->labelDurationSlide.'</abbr> <small class="muted">'.$wordsSlider->labelDurationSlide_suffix.'</small></label>
					<input type="text" name="durationSlide" id="input_durationSlide" class="span12" required="required" value="'.(int) $slider->durationSlide.'"/>
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
				'.$buttonView.'
				'.$buttonRemove.'
			</div>
		</form>
	</div>
</div>
<div class="row-fluid">
	<div class="span8">
		'.$panelSlides.'
	</div>
	<div class="span4">
		'.$panelAddSlide.'
	</div>
</div>';
