<?php
class View_Catalog_Bookstore extends CMF_Hydrogen_View{

	static public function ___onRenderContent( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$pattern		= "/^(.*)(\[CatalogBookstoreRelations([^\]]+)?\])(.*)$/sU";
		$helper			= new View_Helper_Catalog_Bookstore_Relations( $env );
		$defaultAttr	= array(
			'articleId'		=> '',
			'tags'			=> '',
			'heading'		=> '',
		);
		$modals			= array();
		if( preg_match( $pattern, $data->content ) ){
			$code		= preg_replace( $pattern, "\\2", $data->content );
			$code		= preg_replace( '/(\r|\n|\t)/', " ", $code );
			$code		= preg_replace( '/( ){2,}/', " ", $code );
			$code		= trim( $code );
			try{
				$node		= new XML_Element( '< '.substr( $code, 1, -1 ).'/>' );
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

	static public function ___onRenderSearchResults( CMF_Hydrogen_Environment $env, $context, $module, $data ){
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

	public function article(){
		$article	= $this->getData( 'article' );
		$title		= htmlentities( $article->title, ENT_QUOTES, 'UTF-8' );
		$this->env->getPage()->setTitle( $title, 'prepend' );
	}

	public function author(){
		$author		= $this->getData( 'author' );
		$title		= ( $author->firstname ? $author->firstname.' ' : '' ).$author->lastname;
		$title		= htmlentities( $title, ENT_QUOTES, 'UTF-8' );
		$this->env->getPage()->setTitle( $title, 'prepend' );
	}

	public function authors(){}

	public function categories(){
		$words		= $this->getWords( 'category' );
		$title		= htmlentities( $words->heading, ENT_QUOTES, 'UTF-8' );
		$this->env->getPage()->setTitle( $title, 'prepend' );
	}

	public function category(){
		$language	= $this->env->getLanguage()->getLanguage();
		$category	= $this->getData( 'category' );
		$title		= htmlentities( $category->{'label_'.$language}, ENT_QUOTES, 'UTF-8' );
		$this->env->getPage()->setTitle( $title, 'prepend' );
	}

	public function index(){}

	public function news(){
		$words		= $this->getWords( 'news' );
		$title		= htmlentities( $words->heading, ENT_QUOTES, 'UTF-8' );
		$this->env->getPage()->setTitle( $title, 'prepend' );
	}

	public function search(){
		$words		= $this->getWords( 'search' );
		$title		= htmlentities( $words->heading, ENT_QUOTES, 'UTF-8' );
		$this->env->getPage()->setTitle( $title, 'prepend' );
	}

	public function tag(){}
}
?>
