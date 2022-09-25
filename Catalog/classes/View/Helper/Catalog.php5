<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Catalog{

	/**	@var	CMF_Hydrogen_Environment					$env */
	protected $env;
	/**	@var	CMF_Hydrogen_Environment_Resource_Language	$language */
	protected $language;
	/**	@var	Logic_Catalog								$logic */
	protected $logic;

	public function __construct( Environment $env ){
		$this->env		= $env;
		$this->logic	= new Logic_Catalog( $env );
		$this->language	= $this->env->getLanguage();
		$this->cache	= $this->env->getCache();
	}

	static public function ___onRenderNewsItem( Environment $env, &$context, $module, $data = [] ){
		$context->content	= self::applyLinks( $env, $context->content );
	}

	static public function applyLinks( Environment $env, $content/*&$item*/ ){
//		$content	= $item->content;
		$patternAuthor = "/\[author:([0-9]+)\|?([^\]]+)?\]/";
		$logic	= new Logic_Catalog( $env );
		while( preg_match( $patternAuthor, $content ) ){
			$matches	= [];
			preg_match( $patternAuthor, $content, $matches );
			$url		= $logic->getAuthorUri( (int) $matches[1] );
			if( !isset( $matches[2] ) ){
				$author		= $logic->getAuthor( (int) $matches[1] );
				$matches[2]	= $author->firstname ? $author->firstname." ".$author->lastname : $author->lastname;
			}
			$link		= HtmlTag::create( 'a', $matches[2], array( 'href' => $url ) );
			$content	= preg_replace( $patternAuthor, $link, $content, 1 );
		}
		$patternArticle	= "/\[article:([0-9]+)\|?([^\]]+)?\]/";
		while( preg_match( $patternArticle, $content ) ){
			$matches		= [];
			preg_match( $patternArticle, $content, $matches );
			$url		= $logic->getArticleUri( (int) $matches[1] );
			if( !isset( $matches[2] ) )
				$matches[2]	= $logic->getArticle( (int) $matches[1] )->title;
			$link		= HtmlTag::create( 'a', $matches[2], array( 'href' => $url ) );
			$content	= preg_replace( $patternArticle, $link, $content, 1 );
		}
		$patternCategory	= "/\[category:([0-9]+)\|?([^\]]+)?\]/";
		while( preg_match( $patternCategory, $content ) ){
			$matches		= [];
			preg_match( $patternCategory, $content, $matches );
			$url		= $logic->getCategoryUri( (int) $matches[1] );
			if( !isset( $matches[2] ) )
				$matches[2]	= $logic->getCategory( (int) $matches[1] )->label_de;
			$link		= HtmlTag::create( 'a', $matches[2], array( 'href' => $url ) );
			$content	= preg_replace( $patternCategory, $link, $content, 1 );
		}
	//	$item->content	= $content;
		return $content;
	}

	/**
	 *  Returns a float formated as Currency.
	 *  @static
	 *  @access     public
	 *  @param      mixed       $price          Price to be formated
	 *  @param      string      $separator      Separator
	 *  @return     string
	 */
	static public function formatPrice( $price, $separator = "." ){
		$price  = (float) $price;
		ob_start();
		$price  = sprintf( "%01.2f", $price );
		$price  = str_replace( ".", $separator, $price );
		return $price;
	}

	public function getArticleUri( $articleId, $absolute = FALSE ){
		return $this->logic->getArticleUri( (int) $articleId, $absolute );
	}

	public function getCategoryUri( $categoryOrId ){
		return $this->logic->getCategoryUri( $categoryOrId );
	}

	public function getTagUri( $tagOrId ){
		return $this->logic->getTagUri( $tagOrId );
	}

	public function prepareArticleData( $article ){
		$config		= $this->env->getConfig();
		$language	= $this->env->getLanguage();
		$words		= $language->getWords( 'catalog' );

		$words		= $words['index'];
		$item		= [];
		$item['volume']	= !empty( $article->volume ) ? $words['volume'].$article->volume : "";

		$authorlist	= [];
		$logic		= new Logic_Catalog( $this->env );
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

		$item['thumb']	= $this->renderArticleThumbnail( $article, $words['no_picture'] );
		$item['image']	= $this->renderArticleImage( $article );
		$item['title']	= $this->renderArticleLink( $article );
		$item['text']	= View_Helper_Text::applyFormat( $article->subtitle );
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
	public function renderArticleImage( $article, $labelNoPicture = "" ){
		$title	= htmlentities( strip_tags( View_Helper_Text::applyFormat( $article->title ) ) );
		if( strlen( $uri = $this->logic->getArticleCoverUrl( $article, FALSE/*, TRUE*/ ) ) )
			return UI_HTML_Elements::Image( $uri, $title, 'thumb dropshadow' );
		$pathImages	= $this->env->getConfig()->get( 'path.images' );
		return UI_HTML_Elements::Image( $pathImages."no_picture.png", $labelNoPicture );
	}

	public function renderArticleLink( $article ){
		$title		= View_Helper_Text::applyFormat( $article->title );
		$url		= $this->logic->getArticleUri( (int) $article->articleId, $article );
		return HtmlTag::create( 'a', $title, array( 'href' => $url ) );
	}

	public function renderArticleListItem( $article ){
		$data	= $this->prepareArticleData( $article );
		$view	= new View_Catalog( $this->env );
		return $view->loadTemplateFile( 'catalog/article/item.php', $data );
	}

	public function renderArticleThumbnail( $article, $labelNoPicture = "" ){
		if( strlen( $uri = $this->logic->getArticleCoverUrl( $article, TRUE/*, TRUE*/ ) ) ){
			$url	= $this->logic->getArticleUri( $article );
			$title	= htmlentities( strip_tags( View_Helper_Text::applyFormat( $article->title ) ) );
			$image	= UI_HTML_Elements::Image( $uri, $title, 'thumb dropshadow' );
			return UI_HTML_Elements::Link( $url, $image, 'image' );
		}
		$pathImages	= $this->env->getConfig()->get( 'path.images' );
		return UI_HTML_Elements::Image( $pathImages."no_picture.png", $labelNoPicture );
	}

	public function renderAuthorLink( $author ){
		$name	= $author->lastname;
		if( $author->firstname )
			$name	= $author->firstname." ".$name;
		if( $author->editor ){
			$words		= $this->language->getWords( 'catalog' );
			$language	= $this->language->getLanguage();
			$name		.= ' '.$words['editors'][$language];
		}
		$url	= $this->logic->getAuthorUri( $author );
		return HtmlTag::create( 'a', $name, array( 'href' => $url ) );
	}

	public function renderCategory( $category, $heading = NULL ){
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
			$descriptions	= HtmlTag::create( 'div', $descriptions, array( 'class' => 'well' ) );
		$articles	= HtmlTag::create( 'div', $this->renderCategoryArticleList( $category ), array( 'class' => 'catalog-article-list' ) );
		return $heading.$descriptions.$articles;
	}

	public function renderCategoryArticleList( $category ){
		$cacheKey	= 'catalog.html.categoryArticleList.'.$category->categoryId;
		if( NULL === ( $list = $this->cache->get( $cacheKey ) ) ){
			$orders		= array( 'ABS(volume)' => 'DESC', 'articleId' => 'DESC' );
			$articles	= $this->logic->getCategoryArticles( $category, $orders );
			$list	= [];
			foreach( $articles as $article )
				$list[]	= $this->renderArticleListItem( $article );
			$this->cache->set( $cacheKey, $list );
		}
		return $list;
	}

	public function renderCategoryLink( $category, $language = "de" ){
		$labelKey	= 'label_'.$language;
		$title		= View_Helper_Text::applyFormat( $category->$labelKey );
		$url		= $this->logic->getCategoryUri( $category, $language );
		return HtmlTag::create( 'a', $title, array( 'href' => $url ) );
	}

	public function renderCategoryList( $data, $language = "de" ){
		$list	= [];
		foreach( $data as $category ){
			$sub	= [];
			foreach( $category->categories as $subcategory ){
				$link	= $this->renderCategoryLink( $subcategory, $language );
				$sub[]	= UI_HTML_Elements::ListItem( $link, 1, array( 'class' => 'topic' ) );
			}
			$sub	= $sub ? UI_HTML_Elements::unorderedList( $sub, 1, array( 'class' => 'topics' ) ) : '';
			$area	= '<span class="hitarea '.( $sub ? 'closed' : 'empty' ).'"></span>';

			$link	= $this->renderCategoryLink( $category, $language );
			if( !empty( $category->label_former ) )
				$link	.= '<br/><small>vormals <em>'.$category->label_former.'</em></small>';
			$list[]	= UI_HTML_Elements::ListItem( $area.$link.$sub, 0, array( 'class' => 'branch' ) );
		}
		return UI_HTML_Elements::unorderedList( $list, 0, array( 'class' => 'branches' ) );
	}

	public function renderDocumentLink( $document ){
		$id			= str_pad( $document->articleId, 5, 0, STR_PAD_LEFT );
		$config		= $this->env->getConfig();
		$path		= $config->get( 'path.contents' ).'articles/documents/';
		$url		= $path.$id.'_'.$document->url;
		$attributes	= array( 'href' => $url, 'class' => 'document', 'target' => '_blank' );
		$link		= HtmlTag::create( 'a', $document->title, $attributes );
		return $link;
	}

	public function renderPositionFromArticle( $article, $language = "de" ){
		$helper	= new View_Helper_Catalog_Position( $this->env );
		return $helper->renderFromArticle( $article, $language );
	}

	public function renderPositionFromCategory( $category = NULL ){
		$helper	= new View_Helper_Catalog_Position( $this->env );
		return $helper->renderFromCategory( $category );
	}
}
?>
