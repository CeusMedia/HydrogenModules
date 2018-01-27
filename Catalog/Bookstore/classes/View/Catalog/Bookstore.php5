<?php
class View_Catalog_Bookstore extends CMF_Hydrogen_View{

	static public function ___onRenderSearchResults( $env, $context, $module, $data ){
		$helper			= new View_Helper_Catalog_Bookstore( $env );
		$modelArticle	= new Model_Catalog_Bookstore_Article( $env );
		$modelAuthor	= new Model_Catalog_Bookstore_Author( $env );
		foreach( $data->documents as $resultDocument  ){
			if( !preg_match( "@^catalog/bookstore/@", $resultDocument->path ) )
				continue;

			if( preg_match( "@^catalog/bookstore/article/@", $resultDocument->path ) ){
				$path		= preg_replace( "@^catalog/bookstore/article/@", "", $resultDocument->path );
				if( $articleId = (int) $path ){
					$article	= $modelArticle->get( $articleId );
					$articleUri	= $helper->getArticleUri( $articleId, !TRUE );
					$resultDocument->facts	= (object) array(
						'category'		=> 'Katalog: Artikel:',
						'title'			=> $article->title,
						'link'			=> preg_replace( '/^\.\//', '', $articleUri ),
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
						'category'		=> 'Katalog: Autor:',
						'title'			=> $title,
						'link'			=> preg_replace( '/^\.\//', '', $authorUri ),
						'image'			=> $author->image ? './file/bookstore/author/'.$author->image : '',
					);
				}
			}
			if( preg_match( "@^catalog/bookstore/news@", $resultDocument->path ) ){
				$title		= $resultDocument->title;
				$resultDocument->facts	= (object) array(
					'category'		=> 'Katalog:',
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