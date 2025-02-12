<?php

use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<string,array<string,string>> $words */
/** @var object $news */
/** @var int $pageNr */
/** @var int $limit */
/** @var int $total */

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );

$colors	= [
	Model_News::STATUS_HIDDEN	=> 'info',
	Model_News::STATUS_NEW		=> 'warning',
	Model_News::STATUS_PUBLIC	=> 'success'
];

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
	$colgroup	= HtmlElements::ColumnGroup( [
		'*',
		'30%',
	] );
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( [
		$words['index']['headTitle'],
		$words['index']['headRange'],
	] ) );
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$table	= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table table-fixed'] );
}


$buttonAdd		= HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$words['index']['buttonAdd'], [
	'href'	=> './manage/news/add',
	'class'	=> 'btn btn-small btn-success',
] );

$pagination		= new PageControl( './manage/news', $pageNr, ceil( $total / $limit ) );

return '
<div class="content-panel">
	<h3>'.$words['index']['heading'].'</h3>
	<div class="content-panel-inner">
		'.$table.'
		'.HtmlTag::create( 'div', join( '&nbsp;', [
			$pagination,
			$buttonAdd,
		] ), ['class' => 'buttonbar'] ).'
		</div>
	</div>
</div>';
