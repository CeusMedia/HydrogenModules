<?php

//  @todo extract this data collection as hook to module Catalog
if( !empty( $article ) ){
	$helper		= new View_Helper_Catalog( $env );
	$article	= $helper->renderArticleListItem( $article );
	$article	= '
		<h3>Aktuelle Neuerscheinung</h3>
		<div class="articleList">
			'.$article.'
		</div>';
}
else
	$article	= "";

$helperNews	= new View_Helper_News( $env );
$list		= $helperNews->render( 10 );

extract( $view->populateTexts( ['info/news/top', 'info/news/bottom'], 'html' ) );

//  @todo push collected content thru View::renderContent for module shortcodes
return $textInfoNewsTop.$list.$article.$textInfoNewsBottom;
