<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Manage_Catalog_Category extends View_Manage_Catalog{

	public function add(){
	}

	public function edit(){
	}

	public function index(){}


	protected function renderTree( $categories, $categoryId = NULL ){
		$this->env->getRuntime()->reach( 'View_Catalog_Category::renderTree start' );
		$logic		= new Logic_Catalog( $this->env );
		$listMain	= [];
		foreach( $categories as $nr => $category ){
			if( (int) $category->parentId !== 0 )
				continue;
			$listSub	= [];
			foreach( $categories as $entry ){
				if( (int) $entry->parentId !== (int) $category->categoryId )
					continue;
				$rank		= '<small class="muted">'.$entry->rank.'.</small> ';
				$link		= './manage/catalog/category/edit/'.$entry->categoryId;
				$attributes	= ['href' => $link, 'class' => 'title'];
				$number		= $logic->countArticlesInCategory( $entry->categoryId, TRUE );
				$number		= ' <small class="muted">('.$number.')</small> ';
				$label		= $rank.$entry->label_de.$number;
				$link		= HtmlTag::create( 'a', $label, $attributes );
				$class		= $categoryId == $entry->categoryId ? "active" : NULL;
				$listSub[$entry->rank]	= HtmlTag::create( 'li', $link, ['class' => $class] );
			}
			ksort( $listSub );
			if( $listSub )
				$listSub	= HtmlTag::create( 'ul', $listSub, ['class' => 'nav nav-pills nav-stacked'] );
			else
				$listSub	= "";
			$rank		= '<small class="muted">'.$category->rank.'.</small> ';
			$link		= './manage/catalog/category/edit/'.$category->categoryId;
			$number		= $logic->countArticlesInCategory( $category->categoryId, TRUE );
			$number		= ' <small class="muted">('.$number.')</small> ';
			$label		= $rank.$category->label_de.$number;
			$attributes	= ['href' => $link, 'class' => 'title'];
			$link		= HtmlTag::create( 'a', $label, $attributes );
			$class		= $categoryId == $category->categoryId ? "active" : NULL;
			$listMain[$category->rank]	= HtmlTag::create( 'li', $link.$listSub, ['class' => $class] );
//			$this->env->getRuntime()->reach( 'View_Catalog_Category::renderTree run '.$nr );
		}
		ksort( $listMain );
		$listMain	= HtmlTag::create( 'ul', $listMain, ['class' => 'nav nav-pills nav-stacked main boxed', 'style' => 'display: none'] );
		$this->env->getRuntime()->reach( 'View_Catalog_Category::renderTree done' );
		return $listMain;
	}
}
?>
