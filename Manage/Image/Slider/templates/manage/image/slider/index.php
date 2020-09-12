<?php
$iconDurationShow		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconDurationTransition	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrows-h' ) );
$iconSlides				= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-image' ) );
$iconFormatLandscape	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-file-o fa-rotate-90' ) );
$iconFormatPortrait		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-file-o' ) );
$iconViews				= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconAge				= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-clock-o' ) );

$helperDuration	= new View_Helper_Image_Slider_Duration();
$helperDuration->setPrecisionBySliders( $sliders );

$list	= '<div><em class="muted">'.$words['index']['noEntries'].'</em></div>';
if( $sliders ){
	$list	= array();
	foreach( $sliders as $slider ){
		$cover	= '';
		if( $slider->slides )
			$cover	= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $basePath.$slider->path.$slider->slides[0]->source, 'style' => 'max-width: 96px; max-height: 64px' ) );
		$link	= UI_HTML_Tag::create( 'a', $slider->title, array( 'href' => './manage/image/slider/edit/'.$slider->sliderId ) );
		$createdAt	= date( 'd.m.Y', $slider->createdAt ).' <small>'.date( 'H:i:s', $slider->createdAt ).'</small>';
		if( $env->getModules()->has( 'UI_Helper_TimePhraser' ) )
			$createdAt	= View_Helper_TimePhraser::convertStatic( $env, $slider->createdAt, TRUE );
		$rowClass	= $slider->status ? ( $slider->status > 0 ? 'success' : 'notice' ) : 'warning';
		$iconFormat	= $iconFormatLandscape;
		if( $slider->width < $slider->height )
			$iconFormat	= $iconFormatPortrait;
		$dimensions	= join( '<br/>', array(
			$iconSlides.' '.count( $slider->slides ).' Slides',
			$iconFormat.' '.$slider->width.'&times;'.$slider->height.'px',
		) );
		$transition	= join( ', ', array(
			'Animation: '.$words['optAnimation'][$slider->animation],
			'Übergang: '.$words['optEasing'][$slider->easing],
		) );
		$durations	= join( '<br/>', array(
			$iconDurationShow.' '.$helperDuration->formatDuration( $slider->durationShow ).'s',
			UI_HTML_Tag::create( 'acronym', $iconDurationTransition.' '.$helperDuration->formatDuration( $slider->durationSlide ).'s', array( 'title' => $transition ) ),
		) );
		$views		= join( '<br/>', array(
			$iconViews.' '.$slider->views,
			$iconAge.' '.$createdAt,
		) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $cover, array( 'class' => 'image-slider-cover', 'style' => 'text-align: center' ) ),
			UI_HTML_Tag::create( 'td', $link.'<br/><small class="muted">'.$slider->path.'</small>', array( 'class' => 'image-slider-title' ) ),
			UI_HTML_Tag::create( 'td', $dimensions, array( 'class' => 'image-slider-dimensions' ) ),
			UI_HTML_Tag::create( 'td', $durations, array( 'class' => 'image-slider-times' ) ),
			UI_HTML_Tag::create( 'td', $views, array( 'class' => 'image-slider-views-since' ) ),
		), array( 'class' => $rowClass, 'style' => 'height: 70px' ) );
	}
	$heads	= array(
		'Cover',
		'Title / Path',
		'Dimensions',
		'Durations',
		'Views since',
	);
	$colgroup	= UI_HTML_Elements::ColumnGroup( "84px", "", "130px", "120px", "135px" );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( $heads ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-condensed' ) );
}

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );

if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconAdd	= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-plus fa-inverse' ) );
}

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;'.$words['index']['buttonAdd'], array(
	'href'		=> './manage/image/slider/add',
	'class'		=> 'btn btn-small not-btn-info btn-success',
) );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/manage/image/slider' ) );

return $textTop.'
<div class="content-panel">
	<h3>'.$words['index']['heading'].'</h2>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';

class View_Helper_NumberCommons{

	static function getDivider( $numbers ){
		return array_reduce( array_unique( $numbers ), 'static::_gcd_rec' );
	}

	static function getPrecision( $numbers, $maxPrecision = 3 ){
		if( !count( $numbers ) || $maxPrecision < 1 )
			return 0;
		$gcd	= static::getDivider( array_unique( $numbers ) );
		$zeros	= static::_count_trailing_zeros( $gcd );
		return ( $maxPrecision - min( $zeros, $maxPrecision ) );
	}

	protected static function _count_trailing_zeros( $number ){
		$zeros	= 0;
		while( $number >= 10 && $number % 10 === 0 )
			$zeros	+= (int)(bool)( $number /= 10 );
		return $zeros;
	}

	protected static function _gcd_rec( $a, $b ){
		return $b ? static::_gcd_rec( $b, $a % $b ) : $a;
	}
}

class View_Helper_Image_Slider_Duration{

	const MAX_PRECISION			= 3;

	protected $precision		= 0;

	function getSlidersPrecision( $sliders, $maxPrecision = self::MAX_PRECISION ){
		$times	= array();
		foreach( $sliders as $slider ){
			$times[]	= $slider->durationShow;
			$times[]	= $slider->durationSlide;
		}
		return View_Helper_NumberCommons::getPrecision( $times, $maxPrecision );
	}

	function formatDuration( $msecs, $precision = NULL, $sepDecimal = '.', $sepThousand = ',' ){
		$precision	= is_null( $precision ) ? $this->precision : 0;
		return number_format( $msecs / pow( 10, 3 ), $precision, $sepDecimal, $sepThousand );
	}

	public function setPrecision( $precision ){
		$this->precision	= $precision;
		return $this;
	}

	public function setPrecisionBySliders( $sliders, $maxPrecision = self::MAX_PRECISION ){
		return $this->setPrecision( $this->getSlidersPrecision( $sliders, $maxPrecision ) );
	}
}
