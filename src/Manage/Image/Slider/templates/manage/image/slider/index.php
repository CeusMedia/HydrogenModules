<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use View_Manage_Image_Slider as View;

/** @var Web $env */
/** @var View $view */
/** @var string $basePath */
/** @var array $words */
/** @var array<Entity_Image_Slider> $sliders */

$iconDurationShow		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconDurationTransition	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrows-h'] );
$iconSlides				= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-image'] );
$iconFormatLandscape	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-file-o fa-rotate-90'] );
$iconFormatPortrait		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-file-o'] );
$iconViews				= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconAge				= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-clock-o'] );

$helperDuration	= new View_Helper_Image_Slider_Duration();
$helperDuration->setPrecisionBySliders( $sliders );

$list	= '<div><em class="muted">'.$words['index']['noEntries'].'</em></div>';
if( $sliders ){
	$list	= [];
	foreach( $sliders as $slider ){
		$cover	= '';
		if( $slider->slides )
			$cover	= HtmlTag::create( 'img', NULL, [
				'src'	=> $basePath.$slider->path.$slider->slides[0]->source,
				'style'	=> 'max-width: 96px; max-height: 64px'
			] );
		$link	= HtmlTag::create( 'a', $slider->title, ['href' => './manage/image/slider/edit/'.$slider->sliderId] );
		$createdAt	= date( 'd.m.Y', $slider->createdAt ).' <small>'.date( 'H:i:s', $slider->createdAt ).'</small>';
		if( $env->getModules()->has( 'UI_Helper_TimePhraser' ) )
			$createdAt	= View_Helper_TimePhraser::convertStatic( $env, $slider->createdAt, TRUE );
		$rowClass	= $slider->status ? ( $slider->status > 0 ? 'success' : 'notice' ) : 'warning';
		$iconFormat	= $iconFormatLandscape;
		if( $slider->width < $slider->height )
			$iconFormat	= $iconFormatPortrait;
		$dimensions	= join( '<br/>', [
			$iconSlides.' '.count( $slider->slides ).' Slides',
			$iconFormat.' '.$slider->width.'&times;'.$slider->height.'px',
		] );
		$transition	= join( ', ', [
			'Animation: '.$words['optAnimation'][$slider->animation],
			'Übergang: '.$words['optEasing'][$slider->easing],
		] );
		$durations	= join( '<br/>', [
			$iconDurationShow.' '.$helperDuration->formatDuration( $slider->durationShow ).'s',
			HtmlTag::create( 'acronym', $iconDurationTransition.' '.$helperDuration->formatDuration( $slider->durationSlide ).'s', ['title' => $transition] ),
		] );
		$views		= join( '<br/>', [
			$iconViews.' '.$slider->views,
			$iconAge.' '.$createdAt,
		] );
		$list[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $cover, ['class' => 'image-slider-cover', 'style' => 'text-align: center'] ),
			HtmlTag::create( 'td', $link.'<br/><small class="muted">'.$slider->path.'</small>', ['class' => 'image-slider-title'] ),
			HtmlTag::create( 'td', $dimensions, ['class' => 'image-slider-dimensions'] ),
			HtmlTag::create( 'td', $durations, ['class' => 'image-slider-times'] ),
			HtmlTag::create( 'td', $views, ['class' => 'image-slider-views-since'] ),
		], ['class' => $rowClass, 'style' => 'height: 70px'] );
	}
	$heads	= [
		'Cover',
		'Title / Path',
		'Dimensions',
		'Durations',
		'Views since',
	];
	$colgroup	= HtmlElements::ColumnGroup( "84px", "", "130px", "120px", "135px" );
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( $heads ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-condensed'] );
}

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );

if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconAdd	= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-plus fa-inverse'] );
}

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$words['index']['buttonAdd'], [
	'href'		=> './manage/image/slider/add',
	'class'		=> 'btn btn-small not-btn-info btn-success',
] );

extract( $view->populateTexts( ['top', 'bottom'], 'html/manage/image/slider' ) );

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

class View_Helper_NumberCommons
{
	public static function getDivider( array $numbers )
	{
		return array_reduce( array_unique( $numbers ), [self::class, '_gcd_rec'] );
	}

	public static function getPrecision( array $numbers, $maxPrecision = 3 ): int
	{
		if( !count( $numbers ) || $maxPrecision < 1 )
			return 0;
		$gcd	= static::getDivider( array_unique( $numbers ) );
		$zeros	= static::_count_trailing_zeros( $gcd );
		return ( $maxPrecision - min( $zeros, $maxPrecision ) );
	}

	protected static function _count_trailing_zeros( int|float $number ): int
	{
		$zeros	= 0;
		while( $number >= 10 && $number % 10 === 0 )
			$zeros	+= (int)(bool)( $number /= 10 );
		return $zeros;
	}

	protected static function _gcd_rec( $a, $b )
	{
		return $b ? static::_gcd_rec( $b, $a % $b ) : $a;
	}
}

class View_Helper_Image_Slider_Duration
{
	public const MAX_PRECISION		= 3;

	protected int $precision		= 0;

	function getSlidersPrecision( array $sliders, int $maxPrecision = self::MAX_PRECISION ): int
	{
		$times	= [];
		foreach( $sliders as $slider ){
			$times[]	= $slider->durationShow;
			$times[]	= $slider->durationSlide;
		}
		return View_Helper_NumberCommons::getPrecision( $times, $maxPrecision );
	}

	function formatDuration( int|float $msecs, ?int $precision = NULL, string $sepDecimal = '.', string $sepThousand = ',' ): string
	{
		$precision	= $precision ?? $this->precision;
		return number_format( (float) $msecs / pow( 10, 3 ), $precision, $sepDecimal, $sepThousand );
	}

	public function setPrecision( int $precision ): self
	{
		$this->precision	= $precision;
		return $this;
	}

	public function setPrecisionBySliders( array $sliders, int $maxPrecision = self::MAX_PRECISION ): self
	{
		return $this->setPrecision( $this->getSlidersPrecision( $sliders, $maxPrecision ) );
	}
}
