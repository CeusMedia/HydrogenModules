<?php

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );

$colors	= array(
	Model_News::STATUS_HIDDEN	=> 'info',
	Model_News::STATUS_NEW		=> 'warning',
	Model_News::STATUS_PUBLIC	=> 'success'
);

$table	= UI_HTML_Tag::create( 'div', $words['index']['empty'], array( 'class' => 'alert alert-info' ) );
if( $news ){
	$rows	= [];
	foreach( $news as $item ){
		$url		= './manage/news/edit/'.$item->newsId;
		$link		= UI_HTML_Tag::create( 'a', $item->title, array( 'href' => $url ) );
		$starts		= $item->startsAt ? date( "d.m.Y", $item->startsAt ) : "";
		$ends		= $item->endsAt ? date( "d.m.Y", $item->endsAt ) : "";
		$duration	= '';
		if( $starts && $ends )
	 		$duration	= $starts.' - '.$ends;
		else if( $starts )
	 		$duration	= 'ab '.$starts;
		else if( $ends )
	 		$duration	= 'bis '.$ends;

		$cells		= array(
			UI_HTML_Tag::create( 'td', $link, array( 'class' => 'autocut' ) ),
			UI_HTML_Tag::create( 'td', $duration ),
	//		UI_HTML_Tag::create( 'td', date( 'd.m.Y', $item->createdAt ).' '.date( 'H:i', $item->createdAt ) ),
		);
		$rows[]	= UI_HTML_Tag::create( 'tr', $cells, array( 'class' => $colors[$item->status] ) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array(
		'*',
		'30%',
	) );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		$words['index']['headTitle'],
		$words['index']['headRange'],
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$table	= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-fixed' ) );
}


$buttonAdd		= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;'.$words['index']['buttonAdd'], array(
	'href'	=> './manage/news/add',
	'class'	=> 'btn btn-small btn-success',
) );

$pagination     = new CMM_Bootstrap_PageControl( './manage/news', $pageNr, ceil( $total / $limit ) );

return '
<div class="content-panel">
	<h3>'.$words['index']['heading'].'</h3>
	<div class="content-panel-inner">
		'.$table.'
		'.UI_HTML_Tag::create( 'div', join( '&nbsp;', array(
			$pagination,
			$buttonAdd,
		) ), array( 'class' => 'buttonbar' ) ).'
		</div>
	</div>
</div>';
?>
