<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );

$colors	= array(
	Model_News::STATUS_HIDDEN	=> 'info',
	Model_News::STATUS_NEW		=> 'warning',
	Model_News::STATUS_PUBLIC	=> 'success'
);

$table	= HtmlTag::create( 'div', $words['index']['empty'], ['class' => 'alert alert-info'] );
if( $news ){
	$rows	= [];
	foreach( $news as $item ){
		$url		= './manage/news/edit/'.$item->newsId;
		$link		= HtmlTag::create( 'a', $item->title, ['href' => $url] );
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
			HtmlTag::create( 'td', $link, ['class' => 'autocut'] ),
			HtmlTag::create( 'td', $duration ),
	//		HtmlTag::create( 'td', date( 'd.m.Y', $item->createdAt ).' '.date( 'H:i', $item->createdAt ) ),
		);
		$rows[]	= HtmlTag::create( 'tr', $cells, ['class' => $colors[$item->status]] );
	}
	$colgroup	= HtmlElements::ColumnGroup( array(
		'*',
		'30%',
	) );
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( array(
		$words['index']['headTitle'],
		$words['index']['headRange'],
	) ) );
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$table	= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table table-fixed'] );
}


$buttonAdd		= HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$words['index']['buttonAdd'], array(
	'href'	=> './manage/news/add',
	'class'	=> 'btn btn-small btn-success',
) );

$pagination     = new \CeusMedia\Bootstrap\Nav\PageControl( './manage/news', $pageNr, ceil( $total / $limit ) );

return '
<div class="content-panel">
	<h3>'.$words['index']['heading'].'</h3>
	<div class="content-panel-inner">
		'.$table.'
		'.HtmlTag::create( 'div', join( '&nbsp;', array(
			$pagination,
			$buttonAdd,
		) ), ['class' => 'buttonbar'] ).'
		</div>
	</div>
</div>';
