<?php

$w			= (object) $words['index.filter'];

$helperPages    = new \CeusMedia\Bootstrap\PageControl( './manage/catalog/tag/'.$limit, $page, ceil( $total / $limit ) );
$pagination     = $helperPages->render();

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


$panelFilter	= '
		<div class="content-panel content-panel-filter">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./manage/catalog/tag/filter" method="post">
					<label for="input_search">'.$w->heading.'</label>
					<input type="text" name="search" id="input_search" value="'.htmlentities( $filterSearch, ENT_QUOTES, 'UTF-8' ).'"/>
					<div class="buttonbar">
						<a href="./manage/catalog/tag/filter/reset" class="btn btn-small btn-inverse">'.$w->buttonReset.'</a>
						<button type="submit" name="filter" class="btn btn-small btn-primary">'.$w->buttonFilter.'</button>
					</div>
				</form>
			</div>
		</div>
';

$w			= (object) $words['index.list'];

$panelList	= '
		<div class="content-panel content-panel-table">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				'.$list.'
				'.$pagination.'
			</div>
		</div>
';

$tabs		= $this->renderMainTabs();

extract( $view->populateTexts( array( 'top', 'bottom', 'index' ), 'html/manage/catalog/tag/' ) );

return $tabs.'
<!--<h2>Schlagwörter</h2>-->
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>
';

