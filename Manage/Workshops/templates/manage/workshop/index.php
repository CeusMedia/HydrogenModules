<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconAdd		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-plus' ) );

$list	= '<em>Keine Workshops vorhanden.</em>';
if( $workshops ){
	$list	= [];
	foreach( $workshops as $item ){
		$link	= HtmlTag::create( 'a', $item->title, array(
			'href'	=> './manage/workshop/edit/'.$item->workshopId,
		) );
		$status		= $words['statuses'][$item->status];
		$rank		= $words['ranks'][$item->rank];
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $status ),
			HtmlTag::create( 'td', $rank ),
			HtmlTag::create( 'td', date( 'd.m.Y H:i', $item->modifiedAt ) ),

		), array() );
	}
	$heads	= HtmlElements::TableHeads( array( 'Bezeichnung', 'Zustand', 'Anordnung', 'letzte Änderung' ) );
	$thead	= HtmlTag::create( 'thead', $heads );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', array( $thead, $tbody ), array( 'class' => 'table table-striped' ) );
}

$panelList	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Workshops' ),
	HtmlTag::create( 'div', array(
		$list,
		HtmlTag::create( 'div', array(
			HtmlTag::create( 'a', $iconAdd.'&nbsp;neuer Workshop', array( 'href' => './manage/workshop/add', 'class' => 'btn btn-success' ) ),
		), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

return $panelList;
