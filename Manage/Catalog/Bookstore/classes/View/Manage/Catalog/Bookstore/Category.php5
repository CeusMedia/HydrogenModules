<?php
class View_Manage_Catalog_Bookstore_Category extends View_Manage_Catalog_Bookstore
{
	public function add()
	{
	}

	public function edit()
	{
	}

	public function index()
	{
	}

	protected function renderTree( $categories, $categoryId = NULL )
	{
/*		$cache	= $this->env->getCache();
		if( NULL !== ( $data = $cache->get( 'admin.categories.list.html' ) ) ){
			$this->env->getRuntime()->reach( 'View_Catalog_Bookstore_Category::renderTree from cache' );
			return $data;
		}*/

		$this->env->getRuntime()->reach( 'View_Catalog_Bookstore_Category::renderTree start' );
		$logic		= new Logic_Catalog_Bookstore( $this->env );
		$listMain	= [];
		foreach( $categories as $nr => $category ){
			if( (int) $category->parentId !== 0 )
				continue;
			$listSub	= [];
			foreach( $categories as $entry ){
				if( (int) $entry->parentId !== (int) $category->categoryId )
					continue;
				$rank		= '<small class="muted">'.$entry->rank.'.</small> ';
				$link		= './manage/catalog/bookstore/category/edit/'.$entry->categoryId;
				$attributes	= array( 'href' => $link, 'class' => 'title' );
				$number		= $logic->countArticlesInCategory( $entry->categoryId, TRUE );
				$number		= '&nbsp;'.UI_HTML_Tag::create( 'small', '('.$number.')', array( 'class' => 'muted' ) );
				$label		= $rank.$entry->label_de.$number;
				$link		= UI_HTML_Tag::create( 'a', $label, $attributes );
				$class		= $categoryId == $entry->categoryId ? "active" : NULL;
				$item		= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
				$key		= str_pad( $entry->rank, 3, '0', STR_PAD_LEFT ).'_'.uniqid();
				$listSub[$key]	= $item;
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
//			$this->env->getRuntime()->reach( 'View_Catalog_Bookstore_Category::renderTree run '.$nr );
		}
		ksort( $listMain );
		$listMain	= UI_HTML_Tag::create( 'ul', $listMain, array( 'class' => 'nav nav-pills nav-stacked main boxed', 'style' => 'display: none' ) );
		$this->env->getRuntime()->reach( 'View_Catalog_Bookstore_Category::renderTree done' );
//		$cache->set( 'admin.categories.list.html', $listMain );
		return $listMain;
	}
}
