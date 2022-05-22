<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Catalog extends View{

	static public function ___onRenderSearchResults( Environment $env, $context, $module, $data ){
		$helper		= new View_Helper_Catalog( $env );
		foreach( $data->documents as $resultDocument  ){

			if( !preg_match( "@^catalog/@", $resultDocument->path ) )
				continue;

			if( preg_match( "@^catalog/article/@", $resultDocument->path ) ){
				$path		= preg_replace( "@^catalog/article/@", "", $resultDocument->path );
				if( $articleId = (int) $path ){
					$model		= new Model_Catalog_Article( $env );
					$article	= $model->get( $articleId );
//					$resultDocument->html	= $helper->renderArticleListItem( $article );
					$url		= $helper->getArticleUri( $articleId, TRUE );
					$title		= $helper->renderArticleLink( $article );
					$urlTrimmed	= Alg_Text_Trimmer::trimCentric( $url, 120 );
					$link		= UI_HTML_Tag::create( 'a', $urlTrimmed, array(
						'href'	=> $url,
						'class'	=> 'search-result-link-path',
					) );
					$resultDocument->html	= '
					<div class="search-result">
						<div><small class="muted">Katalog: Artikel:</small></div>
						<div><span class="article-title">'.$title.'</span></div>
						<div><span class="article-link">'.$link.'</span></div>
					</div>
					';



				}
			}
			if( preg_match( "@^catalog/author/@", $resultDocument->path ) ){
				$path		= preg_replace( "@^catalog/author/@", "", $resultDocument->path );
				if( $authorId = (int) $path ){
					$model		= new Model_Catalog_Author( $env );
					$author		= $model->get( $authorId );
					$resultDocument->html	= $helper->renderAuthorListItem( $article );
				}
			}
		}
	}

	public function article(){}
	public function author(){}
	public function authors(){}
	public function categories(){}
	public function category(){}
	public function index(){}
	public function news(){}
	public function search(){}
	public function tag(){}
}
?>
