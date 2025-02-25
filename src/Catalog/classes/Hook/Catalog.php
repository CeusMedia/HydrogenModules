<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Hook;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;

class Hook_Catalog extends Hook
{
	/**
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function onRegisterSitemapLinks(): void
	{
		$baseUrl	= $this->env->url.'catalog/';
		$logic		= new Logic_Catalog( $this->env );
		$articles	= $logic->getArticles( [], ['articleId' => 'DESC'] );
		foreach( $articles as $article ){
			$url	= $logic->getArticleUri( $article, TRUE );
			$date	= max( $article->createdAt, $article->modifiedAt );
			$this->context->addLink( $url, $date > 0 ? $this->payload : NULL );
		}
		$authors	= $logic->getAuthors( [], ['authorId' => 'DESC'] );
		foreach( $authors as $author ){
			$url	= $logic->getAuthorUri( $author, TRUE );
			$date	= NULL;//max( $author->createdAt, $author->modifiedAt );
			$this->context->addLink( $url, $date );
		}
	}

	/**
	 *	@return		void
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function onRenderNewsItem(): void
	{
		$this->context->content	= self::applyLinks( $this->context->content );
	}

	/**
	 *	@param		string			$content
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	protected function applyLinks( string $content/*&$item*/ ): string
	{
//		$content	= $item->content;
		$patternAuthor = "/\[author:([0-9]+)\|?([^\]]+)?\]/";
		$logic	= new Logic_Catalog( $this->env );
		while( preg_match( $patternAuthor, $content ) ){
			$matches	= [];
			preg_match( $patternAuthor, $content, $matches );
			$url		= $logic->getAuthorUri( (int) $matches[1] );
			if( !isset( $matches[2] ) ){
				$author		= $logic->getAuthor( (int) $matches[1] );
				$matches[2]	= $author->firstname ? $author->firstname." ".$author->lastname : $author->lastname;
			}
			$link		= HtmlTag::create( 'a', $matches[2], ['href' => $url] );
			$content	= preg_replace( $patternAuthor, $link, $content, 1 );
		}
		$patternArticle	= "/\[article:([0-9]+)\|?([^\]]+)?\]/";
		while( preg_match( $patternArticle, $content ) ){
			$matches		= [];
			preg_match( $patternArticle, $content, $matches );
			$url		= $logic->getArticleUri( $matches[1] );
			if( !isset( $matches[2] ) )
				$matches[2]	= $logic->getArticle( $matches[1] )->title;
			$link		= HtmlTag::create( 'a', $matches[2], ['href' => $url] );
			$content	= preg_replace( $patternArticle, $link, $content, 1 );
		}
		$patternCategory	= "/\[category:([0-9]+)\|?([^\]]+)?\]/";
		while( preg_match( $patternCategory, $content ) ){
			$matches		= [];
			preg_match( $patternCategory, $content, $matches );
			$url		= $logic->getCategoryUri( $matches[1] );
			if( !isset( $matches[2] ) )
				$matches[2]	= $logic->getCategory( $matches[1] )->label_de;
			$link		= HtmlTag::create( 'a', $matches[2], ['href' => $url] );
			$content	= preg_replace( $patternCategory, $link, $content, 1 );
		}
		//	$item->content	= $content;
		return $content;
	}
}