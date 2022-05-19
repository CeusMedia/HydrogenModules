<?php

$w		= (object) $words['edit.slides'];

$iconEdit		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil not-icon-white' ) );
$iconRankUp		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-up' ) );
$iconRankDown	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-down' ) );

$list	= '<div><em class="muted">No slides available.</em></div>';
if( $slider->slides ){
	$list	= [];
	foreach( $slider->slides as $nr => $slide ){
		$buttonEdit	= UI_HTML_Tag::create( 'a', $iconEdit, array( 'href' => './manage/image/slider/editSlide/'.$slide->sliderSlideId, 'class' => 'btn btn-mini' ) );
		$buttonUp	= UI_HTML_Tag::create( 'a', $iconRankUp, array( 'href' => './manage/image/slider/rankSlide/'.$slide->sliderSlideId.'/-1', 'class' => 'btn btn-mini' ) );
		$buttonDown	= UI_HTML_Tag::create( 'a', $iconRankDown, array( 'href' => './manage/image/slider/rankSlide/'.$slide->sliderSlideId.'/1', 'class' => 'btn btn-mini' ) );
		if( $slide->rank <= 1 )
			$buttonUp	= UI_HTML_Tag::create( 'a', $iconRankUp, array( 'class' => 'btn btn-mini disabled' ) );
		if( $slide->rank >= count( $slider->slides ) )
			$buttonDown	= UI_HTML_Tag::create( 'a', $iconRankDown, array( 'class' => 'btn btn-mini disabled' ) );
		$thumb		= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $basePath.$slider->path.$slide->source, 'width' => '60px' ) );
		$thumb		= UI_HTML_Tag::create( 'a', $thumb, array( 'href' => $basePath.$slider->path.$slide->source, 'class' => 'fancybox-auto', 'rel' => 'slides' ) );
		$title		= $slide->title ? $slide->title : '<em>- ohne Titel -</em>';
		$title		= UI_HTML_Tag::create( 'span', $title, array( 'class' => '' ) );
		$title		= UI_HTML_Tag::create( 'a', $title, array( 'href' => './manage/image/slider/editSlide/'.$slide->sliderSlideId ) );
		$source		= UI_HTML_Tag::create( 'small', $slide->source, array( 'class' => 'muted' ) );
		$rowClass	= $slide->status == 1 ? 'success' : ( $slide->status == 0 ? 'warning' : 'info' );
		$rank		= UI_HTML_Tag::create( 'div', $slide->rank, array( 'class' => 'slide-rank-number' ) );
		$buttons	= UI_HTML_Tag::create( 'div', $buttonUp.$buttonDown, array( 'class' => 'btn-group' ) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $rank, array( 'class' => 'cell-slide-rank' ) ),
			UI_HTML_Tag::create( 'td', $thumb, array( 'class' => 'cell-slide-thumb' ) ),
			UI_HTML_Tag::create( 'td', $title.'<br/>'.$source, array( 'class' => 'cell-slide-title' ) ),
			UI_HTML_Tag::create( 'td', $buttonEdit.$buttons, array( 'class' => 'cell-slide-actions' ) ),
		), array(
//			'id'	=> 'slide-'.$slide->sliderSlideId,
			'class'	=> $rowClass,
			'data-slide-id' => $slide->sliderSlideId,
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "50px", "75px", "", "90px" );
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		$w->headRank,
		$w->headImage,
		$w->headSource,
		$w->headActions,
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-condensed', 'id' => 'table-slides' ) );
}

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
