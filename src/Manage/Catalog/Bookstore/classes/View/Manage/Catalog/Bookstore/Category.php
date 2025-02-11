<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Manage_Catalog_Bookstore_Category extends View_Manage_Catalog_Bookstore
{
	public function add(): void
	{
	}

	public function edit(): void
	{
	}

	public function index(): void
	{
	}

	protected function renderTree( $categories, $categoryId = NULL ): string
	{
/*		$cache	= $this->env->getCache();
		if( NULL !== ( $data = $cache->get( 'admin.categories.list.html' ) ) ){
			$this->env->getRuntime()->reach( 'View_Catalog_Bookstore_Category::renderTree from cache' );
			return $data;
		}*/

		$this->env->getRuntime()->reach( 'View_Catalog_Bookstore_Category::renderTree start' );
		$logic		= new Logic_Catalog_BookstoreManager( $this->env );
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
				$attributes	= ['href' => $link, 'class' => 'title'];
				$number		= $logic->countArticlesInCategory( $entry->categoryId, TRUE );
				$number		= '&nbsp;'.HtmlTag::create( 'small', '('.$number.')', ['class' => 'muted'] );
				$label		= $rank.$entry->label_de.$number;
				$link		= HtmlTag::create( 'a', $label, $attributes );
				$class		= $categoryId == $entry->categoryId ? "active" : NULL;
				$item		= HtmlTag::create( 'li', $link, ['class' => $class] );
				$key		= str_pad( $entry->rank, 3, '0', STR_PAD_LEFT ).'_'.uniqid();
				$listSub[$key]	= $item;
			}
			ksort( $listSub );
			if( $listSub )
				$listSub	= HtmlTag::create( 'ul', $listSub, ['class' => 'nav nav-pills nav-stacked'] );
			else
				$listSub	= "";
			$rank		= '<small class="muted">'.$category->rank.'.</small> ';
			$link		= './manage/catalog/bookstore/category/edit/'.$category->categoryId;
			$number		= $logic->countArticlesInCategory( $category->categoryId, TRUE );
			$number		= ' <small class="muted">('.$number.')</small> ';
			$label		= $rank.$category->label_de.$number;
			$attributes	= ['href' => $link, 'class' => 'title'];
			$link		= HtmlTag::create( 'a', $label, $attributes );
			$class		= $categoryId == $category->categoryId ? "active" : NULL;
			$listMain[$category->rank]	= HtmlTag::create( 'li', $link.$listSub, ['class' => $class] );
//			$this->env->getRuntime()->reach( 'View_Catalog_Bookstore_Category::renderTree run '.$nr );
		}
		ksort( $listMain );
		$listMain	= HtmlTag::create( 'ul', $listMain, ['class' => 'nav nav-pills nav-stacked main boxed', 'style' => 'display: none'] );
		$this->env->getRuntime()->reach( 'View_Catalog_Bookstore_Category::renderTree done' );
//		$cache->set( 'admin.categories.list.html', $listMain );
		return $listMain;
	}
}
