<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array $links */

$table	= HtmlTag::create( 'div', 'Keine Links vorhanden.', ['class' => 'alert alert-danger'] );

if( [] !== $links ){
	$rows	= [];
	foreach( $links as $link )
	{
		$rows[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $link->url ),
			HtmlTag::create( 'td', $link->title ),
			HtmlTag::create( 'td', $link->clicks ),
		] );
	}
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( ['URL', 'Titel', 'Clicks'] ) );
	$tbody	= HtmlTag::create( 'tbody', join( $rows ) );
	$table	= HtmlTag::create( 'table', [$thead, $tbody], ['class' => 'table table-striped table-condensed'] );
}

$panel	= HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', 'Links' ),
	HtmlTag::create( 'div', $table, ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );

return $panel;
