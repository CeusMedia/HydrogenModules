<?php

use CeusMedia\HydrogenFramework\View;

class View_Catalog_Bookstore extends View
{
	public function article(): void
	{
		$article	= $this->getData( 'article' );
		$title		= htmlentities( $article->title, ENT_QUOTES, 'UTF-8' );
		$this->env->getPage()->setTitle( $title, 'prepend' );
	}

	public function author(): void
	{
		$author		= $this->getData( 'author' );
		$title		= ( $author->firstname ? $author->firstname.' ' : '' ).$author->lastname;
		$title		= htmlentities( $title, ENT_QUOTES, 'UTF-8' );
		$this->env->getPage()->setTitle( $title, 'prepend' );
	}

	public function authors(): void
	{
	}

	public function categories(): void
	{
		$words		= $this->getWords( 'category' );
		$title		= htmlentities( $words->heading, ENT_QUOTES, 'UTF-8' );
		$this->env->getPage()->setTitle( $title, 'prepend' );
	}

	public function category(): void
	{
		$language	= $this->env->getLanguage()->getLanguage();
		$category	= $this->getData( 'category' );
		$title		= htmlentities( $category->{'label_'.$language}, ENT_QUOTES, 'UTF-8' );
		$this->env->getPage()->setTitle( $title, 'prepend' );
	}

	public function index(): void
	{
	}

	public function news(): void
	{
		$words		= $this->getWords( 'news' );
		$title		= htmlentities( $words->heading, ENT_QUOTES, 'UTF-8' );
		$this->env->getPage()->setTitle( $title, 'prepend' );
	}

	public function search(): void
	{
		$words		= $this->getWords( 'search' );
		$title		= htmlentities( $words->heading, ENT_QUOTES, 'UTF-8' );
		$this->env->getPage()->setTitle( $title, 'prepend' );
	}

	public function tag(): void
	{
	}
}
