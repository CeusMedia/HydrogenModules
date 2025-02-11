<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Info_Manual_CategoryPageList
{
	protected Environment $env;
	protected int|string $activePageId	= '0';
	protected array $pages			= [];

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	public function __toString(): string
	{
		return $this->render();
	}

	public function render(): string
	{
		$words	= $this->env->getLanguage()->getWords( 'info/manual' );
		if( !$this->pages )
			return '<div><em class="muted">'.$words['list']['empty'].'</em></div><br/>';
		$list	= [];
		foreach( $this->pages as $entry ){
			$link	= HtmlTag::create( 'a', $entry->title, ['href' => './info/manual/page/'.$entry->manualPageId.'-'.$this->urlencode( $entry->title )] );
			$class	= 'autocut '.( $this->activePageId == $entry->manualPageId ? 'active' : '' );
			$list[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
		}
		return HtmlTag::create( 'ul', $list, ['class' => 'nav nav-pills nav-stacked'] );
	}

	public function setActivePageId( int|string $pageId ): self
	{
		$this->activePageId	= $pageId;
		return $this;
	}

	public function setCategoryId( int|string $categoryId ): self
	{
		$model			= new Model_Manual_Page( $this->env );
		$conditions		= [
			'status'			=> '>= '.Model_Manual_Page::STATUS_NEW,
			'manualCategoryId'	=> $categoryId,
		];
		$orders			= ['rank' => 'ASC'];
		$this->pages	= $model->getAll( $conditions, $orders );
		return $this;
	}

	public function setPages( array $pages ): self
	{
		$this->pages	= $pages;
		return $this;
	}

	protected function urlencode( string $pageTitle ): string
	{
		return urlencode( $pageTitle );
	}
}
