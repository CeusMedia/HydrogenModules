<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Catalog_Bookstore_Relations
{
	protected Environment $env;
	protected Logic_Catalog_Bookstore $logic;
	protected int|string $articleId		= '0';
	protected int $limit				= 20;
	protected string $heading			= "Ähnliche Veröffentlichungen";
	protected array $tags				= [];

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->logic	= new Logic_Catalog_Bookstore( $env );
	}

	public function render(): string
	{
		if( !$this->tags )
			return '';
		$relatedArticles	= $this->logic->getArticlesFromTags( $this->tags, [$this->articleId] );
		foreach( $relatedArticles as $id => $relation )
			if( !$relation->article->cover )
				unset( $relatedArticles[$id] );
		$total				= count( $relatedArticles );
		$relatedArticles	= array_slice( $relatedArticles, 0, $this->limit );
		if( !$total )
			return '';

		$helper		= new View_Helper_Catalog_Bookstore( $this->env );

		$list		= [];
		foreach( $relatedArticles as $relation ){
			$title		= $relation->article->title;//TextTrimmer::trim( $relation->article->title, 60 );
			$subtitle	= $relation->article->subtitle;//TextTrimmer::trim( $relation->article->subtitle, 60 );
			$url		= $helper->getArticleUri( $relation->article->articleId );
			$image		= HtmlTag::create( 'a', $helper->renderArticleImage( $relation->article ), ['href' => $url] );
		    $image		= HtmlTag::create( 'div', $image, ['class' => 'related-articles-image-container'] );
		    $title		= HtmlTag::create( 'div', HtmlTag::create( 'a', $title, ['href' => $url] ) );
		    $sub		= HtmlTag::create( 'div', HtmlTag::create( 'small', $subtitle, ['class' => ''] ) );
		    $list[]		=  HtmlTag::create( 'div', [$image, $title, $sub], [
				'class'	=> 'related-articles-list-item',
			] );
		}

		return '
<div id="related-articles" class="">
	<h3>'.$this->heading.'</h3>
	<div class="related-articles-slider">
		<div class="related-articles-container">
			<div class="related-articles-list" style="width: '.( count( $relatedArticles ) * 260 ).'px;">
				'.join( $list ).'
			</div>
		</div>
		<div class="related-articles-arrow related-articles-arrow-left" onclick="ModuleCatalogBookstoreRelatedArticlesSlider.slideLeft()"><span>&lt;</span></div>
		<div class="related-articles-arrow related-articles-arrow-right" onclick="ModuleCatalogBookstoreRelatedArticlesSlider.slideRight()"><span>&gt;</span></div>
	</div>
</div>';
	}

	public function setArticleId( int|string $articleId ): self
	{
		$this->tags			= [];
		$this->articleId	= $articleId;
		foreach( $this->logic->getTagsOfArticle( $articleId ) as $tag )
			$this->tags[]	= $tag->tag;
		return $this;
	}

	public function setHeading( string $heading ): self
	{
		$this->heading	= $heading;
		return $this;
	}

	public function setTags( array $tags ): self
	{
		$this->tags	= $tags;
		return $this;
	}
}
