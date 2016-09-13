<?php

$w			= (object) $words['index'];

$tabs		= $this->renderMainTabs();

extract( $view->populateTexts( array( 'top', 'bottom', 'index' ), 'html/manage/catalog/tag/' ) );

//print_m( $tags );die;

/*
$list	= array();
foreach( $tags as $tag ){
	$id	= $tags->articleTagId;
	if( !isset( $list[$id] ) )
		$list[$id]	= (object) array(
			'label'			=> $tag->tag,
			'articleIds'	=> array(),
		);
	$list[$id]->articleIds[]	= $tag->articleId;
}
*/

//print_m( $articles[0] );die;

//$tags	= array_slice( $tags, $page * $limit, $limit );


$helperPages    = new \CeusMedia\Bootstrap\PageControl( './manage/catalog/tag/'.$limit, $page, ceil( $total / $limit ) );
$pagination     = $helperPages->render();

//$helper			= new View_Helper_Catalog( $env );

$rows	= array();
foreach( $tags as $tag ){
	$articleList	= array();
	foreach( $tag->articleIds as $articleId ){
		$link	= UI_HTML_Tag::create( 'a', $articles[$articleId]->title, array( 'href' => './manage/catalog/article/edit/'.$articleId ) );
		$articleList[]	= UI_HTML_Tag::create( 'li', $link );
	}
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $tag->tag ),
		UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'ul', $articleList ) )
	) );
}
$list	= UI_HTML_Tag::create( 'table', array(
	UI_HTML_Tag::create( 'tbody', $rows ),
), array( 'class' => 'table table-striped' ) );

return $tabs.'
<h2>Schlagwörter</h2>
<div class="row-fluid">
	<div class="span3">
		<div class="content-panel content-panel-filter">
			<h3>Filter</h3>
			<div class="content-panel-inner">
				<form action="./manage/catalog/tag/filter" method="post">
					<label for="input_search">Suchwort</label>
					<input type="text" name="search" id="input_search" value="'.htmlentities( $filterSearch, ENT_QUOTES, 'UTF-8' ).'"/>
					<div class="buttonbar">
						<a href="./manage/catalog/tag/filter/reset" class="btn btn-small btn-inverse">leeren</a>
						<button type="submit" name="filter" class="btn btn-small btn-primary">suchen</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="span9">
		<div class="content-panel content-panel-table">
			<h3>Verwendung</h3>
			<div class="content-panel-inner">
				'.$list.'
				'.$pagination.'
			</div>
		</div>
	</div>
</div>
';

