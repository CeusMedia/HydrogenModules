<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as Environment;

/** @var Environment $env */
/** @var array<string,array<string,string>> $words */
/** @var object[] $workshops */
/** @var object $workshop */

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-plus'] );

$list	= '<em>Keine Workshops vorhanden.</em>';
if( $workshops ){
	$list	= [];
	foreach( $workshops as $item ){
		$link	= HtmlTag::create( 'a', $item->title, [
			'href'	=> './manage/workshop/edit/'.$item->workshopId,
		] );
		$status		= $words['statuses'][$item->status];
		$rank		= $words['ranks'][$item->rank];
		$list[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $status ),
			HtmlTag::create( 'td', $rank ),
			HtmlTag::create( 'td', date( 'd.m.Y H:i', $item->modifiedAt ) ),
		] );
	}
	$heads	= HtmlElements::TableHeads( ['Bezeichnung', 'Zustand', 'Anordnung', 'letzte Änderung'] );
	$thead	= HtmlTag::create( 'thead', $heads );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', [$thead, $tbody], ['class' => 'table table-striped'] );
}

$panelList	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', 'Workshops' ),
	HtmlTag::create( 'div', [
		$list,
		HtmlTag::create( 'div', [
			HtmlTag::create( 'a', $iconAdd.'&nbsp;neuer Workshop', ['href' => './manage/workshop/add', 'class' => 'btn btn-success'] ),
		], ['class' => 'buttonbar'] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );

return $panelList;
