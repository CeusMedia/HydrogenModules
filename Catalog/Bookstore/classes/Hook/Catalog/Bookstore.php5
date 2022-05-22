<?php

use CeusMedia\HydrogenFramework\Environment;

class Hook_Catalog_Bookstore extends CMF_Hydrogen_Hook
{
	public static function onRenderContent( Environment $env, $context, $module, $data )
	{
		$pattern		= "/^(.*)(\[CatalogBookstoreRelations([^\]]+)?\])(.*)$/sU";
		$helper			= new View_Helper_Catalog_Bookstore_Relations( $env );
		$defaultAttr	= array(
			'articleId'		=> '',
			'tags'			=> '',
			'heading'		=> '',
		);
		$modals			= [];
		if( preg_match( $pattern, $data->content ) ){
			$code		= preg_replace( $pattern, "\\2", $data->content );
			$code		= preg_replace( '/(\r|\n|\t)/', " ", $code );
			$code		= preg_replace( '/( ){2,}/', " ", $code );
			$code		= trim( $code );
			try{
				$node		= new XML_Element( '<'.substr( $code, 1, -1 ).'/>' );
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
				$env->getMessenger()->noteFailure( 'Short code failed: '.$code );
				$subcontent	= '';
			}
			$replacement	= "\\1".$subcontent."\\4";												//  insert content of nested page...
			$data->content	= preg_replace( $pattern, $replacement, $data->content );				//  ...into page content
		}
	}

	public static function onRenderSearchResults( Environment $env, $context, $module, $data )
	{
		$helper			= new View_Helper_Catalog_Bookstore( $env );
		$modelArticle	= new Model_Catalog_Bookstore_Article( $env );
		$modelAuthor	= new Model_Catalog_Bookstore_Author( $env );
		$modelCategory	= new Model_Catalog_Bookstore_Category( $env );
		$words			= $env->getLanguage()->getWords( 'search' );
		$categories		= (object) $words['result-categories'];
		foreach( $data->documents as $nrDocument => $resultDocument  ){
			if( !preg_match( "@^catalog/bookstore/@", $resultDocument->path ) )
				continue;

			if( preg_match( "@^catalog/bookstore/article/@", $resultDocument->path ) ){
				$path		= preg_replace( "@^catalog/bookstore/article/@", "", $resultDocument->path );
				if( $articleId = (int) $path ){
					$article	= $modelArticle->get( $articleId );
					$articleUri	= $helper->getArticleUri( $articleId, !TRUE );
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
					$authorUri	= $helper->getAuthorUri( $author->authorId, !TRUE );
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
					$categoryUri	= $helper->getCategoryUri( $categoryId, !TRUE );
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

	public static function onRegisterSitemapLinks( Environment $env, $context, $module, $data )
	{
		$baseUrl	= $env->url.'catalog/bookstore/';
		$logic		= new Logic_Catalog_Bookstore( $env );
		$language	= $env->getLanguage()->getLanguage();

		$conditions	= [];
		$orders		= array( 'articleId' => 'DESC' );
		foreach( $logic->getArticles( $conditions, $orders ) as $article ){
			$url	= $logic->getArticleUri( $article, TRUE );
			$date	= max( $article->createdAt, $article->modifiedAt );
			$context->addLink( $url, $date > 0 ? $data : NULL );
		}

		$conditions	= [];
		$orders		= array( 'authorId' => 'DESC' );
		foreach( $logic->getAuthors( $conditions, $orders ) as $author ){
			$url	= $logic->getAuthorUri( $author, TRUE );
			$date	= NULL;//max( $author->createdAt, $author->modifiedAt );
			$context->addLink( $url, $date );
		}

		$conditions	= array( 'visible' => 1 );
		$orders		= array( 'categoryId' => 'DESC' );
		foreach( $logic->getCategories( $conditions, $orders ) as $category ){
			$url	= $logic->getCategoryUri( $category, $language, TRUE );
			$date	= NULL;//max( $author->createdAt, $author->modifiedAt );
			$context->addLink( $url, $date );
		}
	}
}
