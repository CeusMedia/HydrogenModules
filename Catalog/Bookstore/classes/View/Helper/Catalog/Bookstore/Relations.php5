<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Catalog_Bookstore_Relations{

	protected $articleId	= 0;
	protected $env;
	protected $limit		= 20;
	protected $logic;
	protected $heading		= "Ähnliche Veröffentlichungen";
	protected $tags			= [];

	public function __construct( $env ){
		$this->env		= $env;
		$this->logic	= new Logic_Catalog_Bookstore( $env );
	}

	public function render(){
		if( !$this->tags )
			return;
		$relatedArticles	= $this->logic->getArticlesFromTags( $this->tags, [$this->articleId] );
		foreach( $relatedArticles as $id => $relation )
			if( !$relation->article->cover )
				unset( $relatedArticles[$id] );
		$total				= count( $relatedArticles );
		$relatedArticles	= array_slice( $relatedArticles, 0, $this->limit );
		if( !$total )
			return;

		$helper				= new View_Helper_Catalog_Bookstore( $this->env );

		$list		= [];
		foreach( $relatedArticles as $relation ){
			$title		= $relation->article->title;//Alg_Text_Trimmer::trim( $relation->article->title, 60 );
			$subtitle	= $relation->article->subtitle;//Alg_Text_Trimmer::trim( $relation->article->subtitle, 60 );
			$url		= $helper->getArticleUri( $relation->article->articleId, !TRUE );
			$image		= HtmlTag::create( 'a', $helper->renderArticleImage( $relation->article, "" ), ['href' => $url] );
		    $image		= HtmlTag::create( 'div', $image, ['class' => 'related-articles-image-container'] );
		    $title		= HtmlTag::create( 'div', HtmlTag::create( 'a', $title, ['href' => $url] ) );
		    $sub		= HtmlTag::create( 'div', HtmlTag::create( 'small', $subtitle, ['class' => ''] ) );
		    $list[]		=  HtmlTag::create( 'div', [$image, $title, $sub], array(
				'class'	=> 'related-articles-list-item',
			) );
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

	public function setArticleId( $articleId ){
		$this->tags			= [];
		$this->articleId	= $articleId;
		foreach( $this->logic->getTagsOfArticle( $articleId, FALSE ) as $tag )
			$this->tags[]	= $tag->tag;
	}

	public function setHeading( $heading ){
		$this->heading	= $heading;
	}

	public function setTags( $tags ){
		$this->tags	= $tags;
	}
}
