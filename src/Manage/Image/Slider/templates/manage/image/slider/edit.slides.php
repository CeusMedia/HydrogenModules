<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use View_Manage_Image_Slider as View;

/** @var View $view */
/** @var array<string,array<string,string>> $words */
/** @var object $slider */
/** @var string $basePath */

$w		= (object) $words['edit.slides'];

$iconEdit		= HtmlTag::create( 'i', '', ['class' => 'icon-pencil not-icon-white'] );
$iconRankUp		= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-up'] );
$iconRankDown	= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-down'] );

$list	= '<div><em class="muted">No slides available.</em></div>';
if( $slider->slides ){
	$list	= [];
	foreach( $slider->slides as $nr => $slide ){
		$buttonEdit	= HtmlTag::create( 'a', $iconEdit, ['href' => './manage/image/slider/editSlide/'.$slide->sliderSlideId, 'class' => 'btn btn-mini'] );
		$buttonUp	= HtmlTag::create( 'a', $iconRankUp, ['href' => './manage/image/slider/rankSlide/'.$slide->sliderSlideId.'/-1', 'class' => 'btn btn-mini'] );
		$buttonDown	= HtmlTag::create( 'a', $iconRankDown, ['href' => './manage/image/slider/rankSlide/'.$slide->sliderSlideId.'/1', 'class' => 'btn btn-mini'] );
		if( $slide->rank <= 1 )
			$buttonUp	= HtmlTag::create( 'a', $iconRankUp, ['class' => 'btn btn-mini disabled'] );
		if( $slide->rank >= count( $slider->slides ) )
			$buttonDown	= HtmlTag::create( 'a', $iconRankDown, ['class' => 'btn btn-mini disabled'] );
		$thumb		= HtmlTag::create( 'img', NULL, ['src' => $basePath.$slider->path.$slide->source, 'width' => '60px'] );
		$thumb		= HtmlTag::create( 'a', $thumb, ['href' => $basePath.$slider->path.$slide->source, 'class' => 'fancybox-auto', 'rel' => 'slides'] );
		$title		= $slide->title ?: '<em>- ohne Titel -</em>';
		$title		= HtmlTag::create( 'span', $title, ['class' => ''] );
		$title		= HtmlTag::create( 'a', $title, ['href' => './manage/image/slider/editSlide/'.$slide->sliderSlideId] );
		$source		= HtmlTag::create( 'small', $slide->source, ['class' => 'muted'] );
		$rowClass	= $slide->status == 1 ? 'success' : ( $slide->status == 0 ? 'warning' : 'info' );
		$rank		= HtmlTag::create( 'div', $slide->rank, ['class' => 'slide-rank-number'] );
		$buttons	= HtmlTag::create( 'div', $buttonUp.$buttonDown, ['class' => 'btn-group'] );
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $rank, ['class' => 'cell-slide-rank'] ),
			HtmlTag::create( 'td', $thumb, ['class' => 'cell-slide-thumb'] ),
			HtmlTag::create( 'td', $title.'<br/>'.$source, ['class' => 'cell-slide-title'] ),
			HtmlTag::create( 'td', $buttonEdit.$buttons, ['class' => 'cell-slide-actions'] ),
		), [
//			'id'	=> 'slide-'.$slide->sliderSlideId,
			'class'	=> $rowClass,
			'data-slide-id' => $slide->sliderSlideId,
		] );
	}
	$colgroup	= HtmlElements::ColumnGroup( "50px", "75px", "", "90px" );
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( [
		$w->headRank,
		$w->headImage,
		$w->headSource,
		$w->headActions,
	] ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-condensed', 'id' => 'table-slides'] );
}

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
