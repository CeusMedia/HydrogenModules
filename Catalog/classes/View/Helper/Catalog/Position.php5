<?php
class View_Helper_Catalog_Position{

	/**	@var	Logic_Catalog	$logic */
	protected $logic;

	/**	@var	View_Helper_Catalog	$helper */
	protected $helper;

	public function __construct( CMF_Hydrogen_Environment $env ){
		$this->env		= $env;
		$this->logic	= new Logic_Catalog( $env );
		$this->language	= $this->env->getLanguage();
		$this->helper	= new View_Helper_Catalog( $env );
	}

	public function renderFromArticle( $article ){
		$category	= $this->logic->getCategoryOfArticle( $article->articleId );
		$list	= array( $category );
		while( $category->parentId ){
			$category	= $this->logic->getCategory( $category->parentId );
			$list[]	= $category;
		}
		$categories	= array_reverse( $list );
		return $this->renderList( $categories, !FALSE );
	}

	public function renderFromCategory( $category = NULL ){
		if( !is_object( $category ) )
			return $this->renderList( array(), FALSE );
		$list	= array( $category );
		while( $category->parentId ){
			$category	= $this->logic->getCategory( $category->parentId );
			$list[]	= $category;
		}
		$categories	= array_reverse( $list );
		return $this->renderList( $categories, FALSE );
	}

	protected function renderList( $categories, $linkLast = TRUE ){
		$level		= count( $categories );
		$labelKey	= 'label_'.$this->language->getLanguage();
		array_unshift( $categories, (object) array(
			'label_de'		=> 'Übersicht',
			'categoryId'	=> 0,
		));
		$list	= [];
		foreach( $categories as $nr => $category ){
			$url	= './catalog'.( $category->categoryId ? '/category/'.$category->categoryId : '' );
			$url	= $this->helper->getCategoryUri( $category );
			$class	= 'category-level-'.$nr;
			$link	= '<a href="'.$url.'" class="'.$class.'">'.$category->$labelKey.'</a>';
			if( !$linkLast && $level == $nr )
				$link	= '<span class="'.$class.'">'.$category->$labelKey.'</span>';
			$list[]	= $link;
		}
		$list	= join( '&nbsp;>&nbsp;', $list );
		return '<div id="layout-position">'.$list.'</div>';
	}

}
?>
