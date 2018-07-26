<?php
class View_Helper_Info_Manual_CategoryPageList{

	protected $activePageId	= 0;
	protected $pages		= array();

	public function __construct( CMF_Hydrogen_Environment $env ){
		$this->env	= $env;
	}

	public function __toString(){
		return $this->render();
	}

	public function render(){
		$words	= $this->env->getLanguage()->getWords( 'info/manual' );
		if( !$this->pages )
			return '<div><em class="muted">'.$words['list']['empty'].'</em></div><br/>';
		$list	= array();
		foreach( $this->pages as $entry ){
			$link	= UI_HTML_Tag::create( 'a', $entry->title, array( 'href' => './info/manual/page/'.$entry->manualPageId.'-'.$this->urlencode( $entry->title ) ) );
			$class	= 'autocut '.( $this->activePageId == $entry->manualPageId ? 'active' : '' );
			$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
		}
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked' ) );
	}

	public function setActivePageId( $pageId ){
		$this->activePageId	= $pageId;
	}

	public function setCategoryId( $categoryId ){
		$model			= new Model_Manual_Page( $this->env );
		$conditions		= array(
			'status'			=> '>='.Model_Manual_Page::STATUS_NEW,
			'manualCategoryId'	=> $categoryId,
		);
		$orders			= array( 'rank' => 'ASC' );
		$this->pages	= $model->getAll( $conditions, $orders );
	}

	public function setPages( $pages ){
		$this->pages	= $pages;
	}

	protected function urlencode( $pageTitle ){
		return urlencode( $pageTitle );
	}
}
