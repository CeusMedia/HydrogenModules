<?php

use CeusMedia\Common\Alg\Text\Trimmer as TextTrimmer;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

class View_Catalog extends View
{
	/**
	 * @todo #hook
	 */
	public static function ___onRenderSearchResults( Environment $env, object $context, object $module, array & $payload ): void
	{
		/** @var Environment\Web $env */
		$helper		= new View_Helper_Catalog( $env );
		foreach( $payload['documents'] as $resultDocument  ){

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
					$urlTrimmed	= TextTrimmer::trimCentric( $url, 120 );
					$link		= HtmlTag::create( 'a', $urlTrimmed, array(
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

	public function article(): void
	{
	}

	public function author(): void
	{
	}

	public function authors(): void
	{
	}

	public function categories(): void
	{
	}

	public function category(): void
	{
	}

	public function index(): void
	{
	}

	public function news(): void
	{
	}

	public function search(): void
	{
	}

	public function tag(): void
	{
	}
}
