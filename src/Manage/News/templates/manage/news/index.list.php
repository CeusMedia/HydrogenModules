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
	Model_News::STATUS_HIDDEN	=> 'danger',
	Model_News::STATUS_NEW		=> 'warning',
	Model_News::STATUS_PUBLIC	=> 'success',
	Model_News::STATUS_OUTDATED	=> 'info',
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

		$isVisible	= FALSE;
		if( Model_News::STATUS_PUBLIC === (int) $item->status ){
			if( 0 !== (int) $item->startsAt && 0 !== (int) $item->endsAt )
				$isVisible	= $item->startsAt < time() && $item->endsAt > time();
			else if( 0 === (int) $item->startsAt && 0 === (int) $item->endsAt )
				$isVisible	= TRUE;
			else if( 0 !== (int) $item->endsAt )
				$isVisible	= $item->endsAt > time();
			else if( 0 !== (int) $item->startsAt )
				$isVisible	= $item->startsAt < time();
		}

		$cells		= [
			HtmlTag::create( 'td', $link, ['class' => 'autocut'] ),
			HtmlTag::create( 'td', $isVisible ? HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] ) : '' ),
			HtmlTag::create( 'td', $duration ),
	//		HtmlTag::create( 'td', date( 'd.m.Y', $item->createdAt ).' '.date( 'H:i', $item->createdAt ) ),
		];
		$rows[]	= HtmlTag::create( 'tr', $cells, ['class' => $colors[$item->status]] );
	}
	$colgroup	= HtmlElements::ColumnGroup( [
		'*',
		'25px',
		'30%',
	] );
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( [
		$words['index']['headTitle'],
		'',
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
