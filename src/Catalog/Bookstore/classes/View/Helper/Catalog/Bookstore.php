<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Language;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;

class View_Helper_Catalog_Bookstore
{
	/**	@var	Environment					$env */
	protected Environment $env;

	/**	@var	Language					$language */
	protected Language $language;

	/**	@var	Logic_Catalog_Bookstore		$logic */
	protected Logic_Catalog_Bookstore $logic;

	protected $cache;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->logic	= new Logic_Catalog_Bookstore( $env );
		$this->language	= $this->env->getLanguage();
		$this->cache	= $this->env->getCache();
	}

	/**
	 *	@param		Environment		$env
	 *	@param		string			$content
	 *	@return		array|mixed|string|string[]|null
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public static function applyLinks( Environment $env, string $content/*&$item*/ )
	{
//		$content	= $item->content;
		$patternAuthor = "/\[author:([0-9]+)\|?([^\]]+)?\]/";
		$logic	= new Logic_Catalog_Bookstore( $env );
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
			$url		= $logic->getArticleUri( (int) $matches[1] );
			if( !isset( $matches[2] ) )
				$matches[2]	= $logic->getArticle( (int) $matches[1] )->title;
			$link		= HtmlTag::create( 'a', $matches[2], ['href' => $url] );
			$content	= preg_replace( $patternArticle, $link, $content, 1 );
		}
		$patternCategory	= "/\[category:([0-9]+)\|?([^\]]+)?\]/";
		while( preg_match( $patternCategory, $content ) ){
			$matches		= [];
			preg_match( $patternCategory, $content, $matches );
			$url		= $logic->getCategoryUri( (int) $matches[1] );
			if( !isset( $matches[2] ) )
				$matches[2]	= $logic->getCategory( (int) $matches[1] )->label_de;
			$link		= HtmlTag::create( 'a', $matches[2], ['href' => $url] );
			$content	= preg_replace( $patternCategory, $link, $content, 1 );
		}
	//	$item->content	= $content;
		return $content;
	}

	/**
	 *	Returns a float formatted as Currency.
	 *	@static
	 *	@access		public
	 *	@param		mixed		$price			Price to be formatted
	 *	@param		string		$separator		Separator
	 *	@return		string
	 */
	public static function formatPrice( $price, string $separator = '.' ): string
	{
		$price	= (float) $price;
		$price	= sprintf( "%01.2f", $price );
		return str_replace( ".", $separator, $price );
	}

	/**
	 *	@param		string		$articleId
	 *	@param		bool		$absolute
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getArticleUri( string $articleId, bool $absolute = FALSE ): string
	{
		return $this->logic->getArticleUri( (int) $articleId, $absolute );
	}

	/**
	 *	@param		string		$authorId
	 *	@param		bool		$absolute
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getAuthorUri( string $authorId, bool $absolute = FALSE ): string
	{
		return $this->logic->getAuthorUri( (int) $authorId, $absolute );
	}

	/**
	 *	@param		$categoryOrId
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getCategoryUri( $categoryOrId ): string
	{
		return $this->logic->getCategoryUri( $categoryOrId );
	}

	/**
	 *	@param		$tagOrId
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function getTagUri( $tagOrId ): string
	{
		return $this->logic->getTagUri( $tagOrId );
	}

	/**
	 *	@param		object		$article
	 *	@return		array
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function prepareArticleData( object $article ): array
	{
		$config		= $this->env->getConfig();
		$language	= $this->env->getLanguage();
		$words		= $language->getWords( 'catalog/bookstore' );

		$words		= $words['index'];
		$item		= [];
		$item['volume']	= !empty( $article->volume ) ? $words['volume'].$article->volume : "";

		$authorlist	= [];
		$logic		= new Logic_Catalog_Bookstore( $this->env );
		$authors	= $logic->getAuthorsOfArticle( $article->articleId );
		foreach( $authors as $author ){
			$authorlist[] = $this->renderAuthorLink( $author );
		}
		$item['author']	= implode( ", ", $authorlist );

		$item['future']	= "";
#		if( $article->publication ){
#			$article	= new Model_Article();
#			$item['future']	= $article->isFuture( $article->articleId ) ? "future_".$this->env->getSession()->get( 'language' )." " : "";
#		}

		$item['hasCover']	= strlen( trim( $article->cover ) ) > 0;
		$item['thumb']		= $this->renderArticleThumbnail( $article, $words['no_picture'] );
//		$item['image']		= $this->renderArticleImage( $article );
		$item['title']		= $this->renderArticleLink( $article );
		$item['text']		= View_Helper_Text::applyFormat( $article->subtitle );
		$info	= [];
		if( $article->size )
			$info[]	= $article->size;
		if( $article->digestion )
			$info[]	= $article->digestion;
		if( $article->price )
			$info[]	= str_replace( ".", ",", $this->formatPrice( $article->price, "." ) ).$words['price_suffix'];
		$item['info']	= implode( ", ", $info );
		$labelISN	= $article->series ? $words['issn'] : $words['isbn'];
		if( isset( $article->branches ) )
			foreach( $article->branches as $branchId => $branch )
				if( $branchId == 25 )
					$labelISN	= $words->issn;

		$item['isbn']		= $labelISN.$article->isn;
		$item['status']		= $article->status;
		$item['app_lan']	= $this->env->getLanguage()->getLanguage();
		$item['language']	= $article->language;
		return $item;
	}

	/**
	 *	Returns image tag of article cover or a placeholder if none set.
	 *	@access		public
	 *	@param		object		$article			Data object of article
	 *	@param		string		$labelNoPicture		Title of placeholder image
	 *	@return		string		Rendered HTML tag of article cover image (or placeholder).
	 */
	public function renderArticleImage( object $article, string $labelNoPicture = '', bool $absolute = FALSE ): string
	{
		if( $article->cover ){
			$logicBucket	= new Logic_FileBucket( $this->env );
			$fileMedium		= $logicBucket->getByPath( 'bookstore/article/m/'.$article->cover, 'catalog_bookstore' );
			$fileLarge		= $logicBucket->getByPath( 'bookstore/article/l/'.$article->cover, 'catalog_bookstore' );
			if( $fileMedium ){
				$title	= htmlentities( strip_tags( View_Helper_Text::applyFormat( $article->title ) ) );
//				if( $fileLarge ){
//				}
				$uri	= './file/bookstore/article/m/'.$article->cover;
				return HtmlElements::Image( $uri, $title, 'dropshadow' );
			}
		}
		$pathImages	= $this->env->getConfig()->get( 'path.images' );
		return HtmlElements::Image( $pathImages."bookstore/no_picture.png", $labelNoPicture );
	}

	/**
	 *	@param		object		$article
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function renderArticleLink( object $article ): string
	{
		$title		= View_Helper_Text::applyFormat( $article->title );
		$url		= $this->logic->getArticleUri( $article );
		return HtmlTag::create( 'a', $title, ['href' => $url] );
	}

	/**
	 *	@param		object		$article
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function renderArticleListItem( object $article ): string
	{
		$data	= $this->prepareArticleData( $article );
		$view	= new View_Catalog_Bookstore( $this->env );
		return $view->loadTemplateFile( 'catalog/bookstore/article/item.php', $data );
	}

	public function renderArticleThumbnail( $article, string $labelNoPicture = '', bool $absolute = FALSE ): string
	{
		if( $article->cover ){
			$logicBucket	= new Logic_FileBucket( $this->env );
			$fileSmall		= $logicBucket->getByPath( 'bookstore/article/s/'.$article->cover, 'catalog_bookstore' );
			if( $fileSmall ){
				$title	= htmlentities( strip_tags( View_Helper_Text::applyFormat( $article->title ) ) );
				$uri	= './file/bookstore/article/s/'.$article->cover;
				return HtmlElements::Image( $uri, $title, 'dropshadow' );
			}
		}
		$pathImages	= $this->env->getConfig()->get( 'path.images' );
		return HtmlElements::Image( $pathImages."bookstore/no_picture.png", $labelNoPicture );
	}

	/**
	 *	@param		object		$author
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function renderAuthorLink( object $author ): string
	{
		$name	= $author->lastname;
		if( $author->firstname )
			$name	= $author->firstname." ".$name;
		if( isset( $author->editor ) && $author->editor ){
			$words		= $this->language->getWords( 'catalog/bookstore' );
//			$language	= $this->language->getLanguage();
//			$name		.= ' '.$words['editors'][$language];
			$name		.= $words['roles'][$author->editor];
		}
		$url	= $this->logic->getAuthorUri( $author );
		return HtmlTag::create( 'a', $name, ['href' => $url] );
	}

	/**
	 *	@param		object			$category
	 *	@param		string|NULL		$heading
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function renderCategory( object $category, ?string $heading = NULL ): string
	{
		if( is_string( $heading ) )
			$heading	= HtmlTag::create( 'h3', $heading );
		else if( $heading ){
			$labelKey	= 'label_'.$this->language->getLanguage();
			$heading	= HtmlTag::create( 'h3', $category->$labelKey );
		}
#		if($data['label_former'])
#			$content = '<small>'.$words['aka'].'</small>&nbsp;'.$data['label_former'].'<br/><br/>'.$content;
		$descriptions	= [];
		if( strlen( trim( $category->publisher ) ) )
			$descriptions[]	= $category->publisher;
		if( strlen( trim( $category->issn ) ) )
			$descriptions[]	= 'ISSN: '.$category->issn;
		$descriptions	= join( '<br/>', $descriptions );
		if( $descriptions )
			$descriptions	= HtmlTag::create( 'div', $descriptions, ['class' => 'well'] );
		$articles	= HtmlTag::create( 'div', $this->renderCategoryArticleList( $category ), ['class' => 'catalog-article-list'] );
		return $heading.$descriptions.$articles;
	}

	/**
	 *	@param		object		$category
	 *	@return		array
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function renderCategoryArticleList( object $category ): array
	{
		$cacheKey	= 'catalog.bookstore.html.categoryArticleList.'.$category->categoryId;
		if( NULL === ( $list = $this->cache->get( $cacheKey ) ) ){
			$orders		= ['articleCategoryId' => 'DESC', 'articleId' => 'DESC'];
			$articles	= $this->logic->getCategoryArticles( $category, $orders );
			$list	= [];
			foreach( $articles as $article )
				$list[]	= $this->renderArticleListItem( $article );
			$this->cache->set( $cacheKey, $list );
		}
		return $list;
	}

	/**
	 *	@param		object		$category
	 *	@param		string		$language
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function renderCategoryLink( object $category, string $language = 'de' ): string
	{
		$labelKey	= 'label_'.$language;
		$title		= View_Helper_Text::applyFormat( $category->$labelKey );
		$url		= $this->logic->getCategoryUri( $category, $language );
		return HtmlTag::create( 'a', $title, ['href' => $url] );
	}

	/**
	 *	@param		array		$data
	 *	@param		string		$language
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function renderCategoryList( array $data, string $language = 'de' ): string
	{
		$list	= [];
		foreach( $data as $category ){
			$sub	= [];
			foreach( $category->categories as $subcategory ){
				$link	= $this->renderCategoryLink( $subcategory, $language );
				$sub[]	= HtmlElements::ListItem( $link, 1, ['class' => 'topic'] );
			}
			$sub	= $sub ? HtmlElements::unorderedList( $sub, 1, ['class' => 'topics'] ) : '';
			$area	= '<span class="hitarea '.( $sub ? 'closed' : 'empty' ).'"></span>';

			$link	= $this->renderCategoryLink( $category, $language );
			if( !empty( $category->label_former ) )
				$link	.= '<br/><small>vormals <em>'.$category->label_former.'</em></small>';
			$list[]	= HtmlElements::ListItem( $area.$link.$sub, 0, ['class' => 'branch'] );
		}
		return HtmlElements::unorderedList( $list, 0, ['class' => 'branches'] );
	}

	public function renderDocumentLink( $document ): string
	{
		return HtmlTag::create( 'a', $document->title, [
			'href'		=> 'file/bookstore/document/'.$document->url,
			'class'		=> 'document',
			'target'	=> '_blank',
		] );
	}

	/**
	 *	@param		object		$article
	 *	@param		string		$language
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function renderPositionFromArticle( object $article, string $language = 'de' ): string
	{
		$helper	= new View_Helper_Catalog_Bookstore_Position( $this->env );
		return $helper->renderFromArticle( $article );
	}

	/**
	 *	@param		object|NULL		$category
	 *	@return		string
	 *	@throws		SimpleCacheInvalidArgumentException
	 */
	public function renderPositionFromCategory( ?object $category = NULL ): string
	{
		$helper	= new View_Helper_Catalog_Bookstore_Position( $this->env );
		return $helper->renderFromCategory( $category );
	}
}
