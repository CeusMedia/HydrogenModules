<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['editSlide.info'];

$slideFilePath	= $basePath.$slider->path.$slide->source;
$slideImage		= new UI_Image( $slideFilePath );

$slideThumb		= HtmlTag::create( 'img', NULL, array(
	'class'	=> 'img-polaroid',
	'src'	=> $slideFilePath,
	'alt'	=> htmlentities( $slide->title, ENT_QUOTES, 'UTF-8' ),
) );
if( $env->getModules()->has( 'UI_JS_fancyBox' ) )
	$slideThumb		= HtmlTag::create( 'a', $slideThumb, array(
		'class'	=> 'fancybox-auto',
		'href'	=> $slideFilePath,
		'title'	=> htmlentities( $slide->title, ENT_QUOTES, 'UTF-8' ),
) );

return '
<div class="content-panel" id="">
	<h3 class="autocut">'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				'.$slideThumb.'
			</div>
		</div>
		<dl class="dl-horizontal">
			<dt>'.$w->labelSource.'</dt>
			<dd>'.$slide->source.'</dd>
			<dt>'.$w->labelImageSize.'</dt>
			<dd>'.$slideImage->getWidth().'&times;'.$slideImage->getHeight().'px</dd>
			<dt>'.$w->labelFileSize.'</dt>
			<dd>'.Alg_UnitFormater::formatBytes( filesize( $slideFilePath ) ).'</dd>
		</dl>
		<dl class="dl-horizontal">
			<dt>'.$w->labelSlider.'</dt>
			<dd><a href="./manage/image/slider/edit/'.$slider->sliderId.'">'.htmlentities( $slider->title, ENT_QUOTES, 'UTF-8' ).'</a></dd>
		</dl>
	</div>
</div>';
