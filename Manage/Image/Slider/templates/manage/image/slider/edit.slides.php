<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words['edit.slides'];

$iconEdit		= HtmlTag::create( 'i', '', array( 'class' => 'icon-pencil not-icon-white' ) );
$iconRankUp		= HtmlTag::create( 'i', '', array( 'class' => 'icon-arrow-up' ) );
$iconRankDown	= HtmlTag::create( 'i', '', array( 'class' => 'icon-arrow-down' ) );

$list	= '<div><em class="muted">No slides available.</em></div>';
if( $slider->slides ){
	$list	= [];
	foreach( $slider->slides as $nr => $slide ){
		$buttonEdit	= HtmlTag::create( 'a', $iconEdit, array( 'href' => './manage/image/slider/editSlide/'.$slide->sliderSlideId, 'class' => 'btn btn-mini' ) );
		$buttonUp	= HtmlTag::create( 'a', $iconRankUp, array( 'href' => './manage/image/slider/rankSlide/'.$slide->sliderSlideId.'/-1', 'class' => 'btn btn-mini' ) );
		$buttonDown	= HtmlTag::create( 'a', $iconRankDown, array( 'href' => './manage/image/slider/rankSlide/'.$slide->sliderSlideId.'/1', 'class' => 'btn btn-mini' ) );
		if( $slide->rank <= 1 )
			$buttonUp	= HtmlTag::create( 'a', $iconRankUp, array( 'class' => 'btn btn-mini disabled' ) );
		if( $slide->rank >= count( $slider->slides ) )
			$buttonDown	= HtmlTag::create( 'a', $iconRankDown, array( 'class' => 'btn btn-mini disabled' ) );
		$thumb		= HtmlTag::create( 'img', NULL, array( 'src' => $basePath.$slider->path.$slide->source, 'width' => '60px' ) );
		$thumb		= HtmlTag::create( 'a', $thumb, array( 'href' => $basePath.$slider->path.$slide->source, 'class' => 'fancybox-auto', 'rel' => 'slides' ) );
		$title		= $slide->title ? $slide->title : '<em>- ohne Titel -</em>';
		$title		= HtmlTag::create( 'span', $title, array( 'class' => '' ) );
		$title		= HtmlTag::create( 'a', $title, array( 'href' => './manage/image/slider/editSlide/'.$slide->sliderSlideId ) );
		$source		= HtmlTag::create( 'small', $slide->source, array( 'class' => 'muted' ) );
		$rowClass	= $slide->status == 1 ? 'success' : ( $slide->status == 0 ? 'warning' : 'info' );
		$rank		= HtmlTag::create( 'div', $slide->rank, array( 'class' => 'slide-rank-number' ) );
		$buttons	= HtmlTag::create( 'div', $buttonUp.$buttonDown, array( 'class' => 'btn-group' ) );
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $rank, array( 'class' => 'cell-slide-rank' ) ),
			HtmlTag::create( 'td', $thumb, array( 'class' => 'cell-slide-thumb' ) ),
			HtmlTag::create( 'td', $title.'<br/>'.$source, array( 'class' => 'cell-slide-title' ) ),
			HtmlTag::create( 'td', $buttonEdit.$buttons, array( 'class' => 'cell-slide-actions' ) ),
		), array(
//			'id'	=> 'slide-'.$slide->sliderSlideId,
			'class'	=> $rowClass,
			'data-slide-id' => $slide->sliderSlideId,
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "50px", "75px", "", "90px" );
	$thead		= HtmlTag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		$w->headRank,
		$w->headImage,
		$w->headSource,
		$w->headActions,
	) ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-condensed', 'id' => 'table-slides' ) );
}

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
