<?php

use CeusMedia\HydrogenFramework\View;

class View_Catalog_Bookstore extends View{

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
