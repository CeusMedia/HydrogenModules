<?php
class View_Manage_Catalog_Bookstore_Category extends View_Manage_Catalog_Bookstore{

	public function add(){
	}

	public function edit(){
	}

	public function index(){}


	protected function renderTree( $categories, $categoryId = NULL ){
		$this->env->clock->profiler->tick( 'View_Catalog_Bookstore_Category::renderTree start' );
		$logic		= new Logic_Catalog_Bookstore( $this->env );
		$listMain	= array();
		foreach( $categories as $nr => $category ){
			if( (int) $category->parentId !== 0 )
				continue;
			$listSub	= array();
			foreach( $categories as $entry ){
				if( (int) $entry->parentId !== (int) $category->categoryId )
					continue;
				$rank		= '<small class="muted">'.$entry->rank.'.</small> ';
				$link		= './manage/catalog/bookstore/category/edit/'.$entry->categoryId;
				$attributes	= array( 'href' => $link, 'class' => 'title' );
				$number		= $logic->countArticlesInCategory( $entry->categoryId, TRUE );
				$number		= ' <small class="muted">('.$number.')</small> ';
				$label		= $rank.$entry->label_de.$number;
				$link		= UI_HTML_Tag::create( 'a', $label, $attributes );
				$class		= $categoryId == $entry->categoryId ? "active" : NULL;
				$listSub[$entry->rank]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
			}
			ksort( $listSub );
			if( $listSub )
				$listSub	= UI_HTML_Tag::create( 'ul', $listSub, array( 'class' => 'nav nav-pills nav-stacked' ) );
			else
				$listSub	= "";
			$rank		= '<small class="muted">'.$category->rank.'.</small> ';
			$link		= './manage/catalog/bookstore/category/edit/'.$category->categoryId;
			$number		= $logic->countArticlesInCategory( $category->categoryId, TRUE );
			$number		= ' <small class="muted">('.$number.')</small> ';
			$label		= $rank.$category->label_de.$number;
			$attributes	= array( 'href' => $link, 'class' => 'title' );
			$link		= UI_HTML_Tag::create( 'a', $label, $attributes );
			$class		= $categoryId == $category->categoryId ? "active" : NULL;
			$listMain[$category->rank]	= UI_HTML_Tag::create( 'li', $link.$listSub, array( 'class' => $class ) );
//			$this->env->clock->profiler->tick( 'View_Catalog_Bookstore_Category::renderTree run '.$nr );
		}
		ksort( $listMain );
		$listMain	= UI_HTML_Tag::create( 'ul', $listMain, array( 'class' => 'nav nav-pills nav-stacked main boxed', 'style' => 'display: none' ) );
		$this->env->clock->profiler->tick( 'View_Catalog_Bookstore_Category::renderTree done' );
		return $listMain;
	}
}
?>
