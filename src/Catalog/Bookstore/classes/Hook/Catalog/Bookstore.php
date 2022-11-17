<?php

use CeusMedia\Common\XML\Element as XmlElement;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Catalog_Bookstore extends Hook
{
	public function onRenderContent()
	{
		$pattern		= "/^(.*)(\[CatalogBookstoreRelations([^\]]+)?\])(.*)$/sU";
		$helper			= new View_Helper_Catalog_Bookstore_Relations( $this->env );
		$defaultAttr	= array(
			'articleId'		=> '',
			'tags'			=> '',
			'heading'		=> '',
		);

		if( preg_match( $pattern, $this->payload['content'] ) ){
			$code		= preg_replace( $pattern, "\\2", $this->payload['content'] );
			$code		= preg_replace( '/(\r|\n|\t)/', " ", $code );
			$code		= preg_replace( '/( ){2,}/', " ", $code );
			$code		= trim( $code );
			try{
				$node		= new XmlElement( '<'.substr( $code, 1, -1 ).'/>' );
				$attr		= array_merge( $defaultAttr, $node->getAttributes() );
				if( $attr['articleId'] )
					$helper->setArticleId( $attr['articleId'] );
				else if( $attr['tags'] ){
					$tags	= preg_split( '/, */', $attr['tags'] );
					$helper->setTags( $tags );
				}
				if( $attr['heading'] )
					$helper->setHeading( $attr['heading'] );
				$subcontent		= $helper->render();
				$subcontent		.= '<script>jQuery(document).ready(function(){ModuleCatalogBookstoreRelatedArticlesSlider.init(260)});</script>';
			}
			catch( Exception $e ){
				$this->env->getMessenger()->noteFailure( 'Short code failed: '.$code );
				$subcontent	= '';
			}
			$replacement	= "\\1".$subcontent."\\4";												//  insert content of nested page...
			$this->payload['content']	= preg_replace( $pattern, $replacement, $this->payload['content'] );				//  ...into page content
		}
	}

	public function onRenderNewsItem()
	{
		$this->context->content	= View_Helper_Catalog_Bookstore::applyLinks( $this->env, $this->context->content );
	}

	public function onRenderSearchResults()
	{
		$helper			= new View_Helper_Catalog_Bookstore( $this->env );
		$modelArticle	= new Model_Catalog_Bookstore_Article( $this->env );
		$modelAuthor	= new Model_Catalog_Bookstore_Author( $this->env );
		$modelCategory	= new Model_Catalog_Bookstore_Category( $this->env );
		$words			= $this->env->getLanguage()->getWords( 'search' );
		$categories		= (object) $words['result-categories'];
		foreach( $this->payload['documents'] as $nrDocument => $resultDocument  ){
			if( !preg_match( "@^catalog/bookstore/@", $resultDocument->path ) )
				continue;

			if( preg_match( "@^catalog/bookstore/article/@", $resultDocument->path ) ){
				$path		= preg_replace( "@^catalog/bookstore/article/@", "", $resultDocument->path );
				if( $articleId = (int) $path ){
					$article	= $modelArticle->get( $articleId );
					$articleUri	= $helper->getArticleUri( $articleId );
					$resultDocument->facts	= (object) array(
						'title'			=> $article->title,
						'link'			=> preg_replace( '/^\.\//', '', $articleUri ),
						'category'		=> $categories->article,
						'image'			=> $article->cover ? './file/bookstore/article/s/'.$article->cover : '',
					);
				}
			}
			if( preg_match( "@^catalog/bookstore/author/@", $resultDocument->path ) ){
				$path		= preg_replace( "@^catalog/bookstore/author/@", "", $resultDocument->path );
				if( $authorId = (int) $path ){
					$author		= $modelAuthor->get( $authorId );
					$title		= $author->lastname;
					if( $author->firstname )
						$title	= $author->firstname." ".$title;
					$authorUri	= $helper->getAuthorUri( $author->authorId );
					$resultDocument->facts	= (object) array(
						'title'			=> $title,
						'link'			=> preg_replace( '/^\.\//', '', $authorUri ),
						'category'		=> $categories->author,
						'image'			=> $author->image ? './file/bookstore/author/'.$author->image : '',
					);
				}
			}
			if( preg_match( "@^catalog/bookstore/category/@", $resultDocument->path ) ){
				$path		= preg_replace( "@^catalog/bookstore/category/@", "", $resultDocument->path );
				if( $categoryId = (int) $path ){
					$category		= $modelCategory->get( $categoryId );
					$categoryUri	= $helper->getCategoryUri( $categoryId );
					$resultDocument->facts	= (object) array(
						'title'			=> $category->label_de,
						'link'			=> preg_replace( '/^\.\//', '', $categoryUri ),
						'category'		=> $categories->category,
						'image'			=> '',
					);
				}
			}
			if( preg_match( "@^catalog/bookstore/news@", $resultDocument->path ) ){
				$title		= $resultDocument->title;
				$resultDocument->facts	= (object) array(
					'category'		=> $categories->news,
					'title'			=> 'Neuerscheinungen',
					'link'			=> $resultDocument->path,
					'image'			=> NULL,
				);
			}
		}
	}

	public function onRegisterSitemapLinks()
	{
		$baseUrl	= $this->env->url.'catalog/bookstore/';
		$logic		= new Logic_Catalog_Bookstore( $this->env );
		$language	= $this->env->getLanguage()->getLanguage();

		$orders		= ['articleId' => 'DESC'];
		foreach( $logic->getArticles( [], $orders ) as $article ){
			$url	= $logic->getArticleUri( $article, TRUE );
			$date	= max( $article->createdAt, $article->modifiedAt );
			$this->context->addLink( $url, $date > 0 ? $this->payload : NULL );
		}

		$orders		= ['authorId' => 'DESC'];
		foreach( $logic->getAuthors( [], $orders ) as $author ){
			$url	= $logic->getAuthorUri( $author, TRUE );
			$date	= NULL;//max( $author->createdAt, $author->modifiedAt );
			$this->context->addLink( $url, $date );
		}

		$orders		= ['categoryId' => 'DESC'];
		foreach( $logic->getCategories( ['visible' => 1], $orders ) as $category ){
			$url	= $logic->getCategoryUri( $category, $language, TRUE );
			$date	= NULL;//max( $author->createdAt, $author->modifiedAt );
			$this->context->addLink( $url, $date );
		}
	}
}
