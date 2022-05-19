<?php
class Controller_Manage_Catalog_Bookstore_Category extends CMF_Hydrogen_Controller{

	public function ajaxGetNextRank( $categoryId ){
		$nextRank			= 0;
		$categoryArticles	= $this->logic->getCategoryArticles( $categoryId, array( 'rank' => 'DESC' ) );
		if( $categoryArticles )
			$nextRank	= $categoryArticles[0]->rank + 1;
		header( 'Content-Type: application/json' );
		print( json_encode( $nextRank ) );
		exit;
	}

	public function rankArticle( $categoryId, $articleId, $direction ){
		$model		= new Model_Catalog_Bookstore_Article_Category( $this->env );
		$category	= $this->logic->getCategory( $categoryId );
		$article	= $this->logic->getArticle( $articleId );
		$articles	= $this->logic->getCategoryArticles( $category, array( 'rank' => 'ASC' ) );
		foreach( $articles as $nr => $item ){
			if( $item->articleId == $article->articleId ){
				if( $direction === "up" ){
					if( $nr > 0 ){
						$other	= $articles[$nr - 1];
						$model->edit( $other->articleCategoryId, array( 'rank' => $item->rank ) );
						$model->edit( $item->articleCategoryId, array( 'rank' => $other->rank ) );
					}
					break;
				}
				else if( $direction === "down" ){
					if( ( $nr + 1 ) < count( $articles ) ){
						$other	= $articles[$nr + 1];
						$model->edit( $other->articleCategoryId, array( 'rank' => $item->rank ) );
						$model->edit( $item->articleCategoryId, array( 'rank' => $other->rank ) );
					}
					break;
				}
			}
		}
		$this->restart( './manage/catalog/bookstore/category/edit/'.$categoryId );
	}

	protected function __onInit(){
		$this->env->getRuntime()->reach( 'Controller_Manage_Catalog_Bookstore_Category::init start' );
		$this->logic		= new Logic_Catalog_Bookstore( $this->env );
		$this->session		= $this->env->getSession();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->env->getRuntime()->reach( 'Controller_Manage_Catalog_Bookstore_Category::init done' );
	}

	static public function ___onTinyMCE_getLinkList( CMF_Hydrogen_Environment $env, $context, $module, $arguments = [] ){
		$cache		= $env->getCache();
		if( !( $categories = $cache->get( 'catalog.tinymce.links.catalog.bookstore.categories' ) ) ){
			$logic		= new Logic_Catalog_Bookstore( $env );
			$config		= $env->getConfig()->getAll( 'module.manage_catalog_bookstore.', TRUE );
			$language	= $env->getLanguage()->getLanguage();
			$conditions	= array( 'visible' => '> 0', 'parentId' => 0 );
			$categories	= $logic->getCategories( $conditions, array( 'rank' => 'ASC' ) );
			foreach( $categories as $nr1 => $item ){
				$conditions	= array( 'visible' => '> 0', 'parentId' => $item->categoryId );
				$subs		= $logic->getCategories( $conditions, array( 'rank' => 'ASC' ) );
				foreach( $subs as $nr2 => $sub ){
					$subs[$nr2] = (object) array(
						'title'	=> $sub->{"label_".$language},
						'value'	=> 'catalog/bookstore/category/'.$item->categoryId,
					);
				}
				$categories[$nr1] = (object) array(
					'title'	=> $item->{"label_".$language},
					'menu'	=> array_values( $subs ),
				);
			}
			$cache->set( 'catalog.tinymce.links.catalog.bookstore.categories', $categories );
		}
		$words	= $env->getLanguage()->getWords( 'manage/catalog/bookstore' );
        $context->list  = array_merge( $context->list, array( (object) array(		//  extend global collection by submenu with list of items
			'title'	=> $words['tinymce-menu-links']['categories'],											//  label of submenu @todo extract
			'menu'	=> array_values( $categories ),									//  items of submenu
		) ) );
	}

	public function add( $parentId = NULL ){
		if( $this->request->has( 'save' ) ){
			$words		= (object) $this->getWords( 'add' );
			$data	= $this->request->getAll();
			if( !strlen( $data['label_de'] ) )
				$this->messenger->noteError( $words->msgErrorLabelMissing );
			else{
				$categoryId	= $this->logic->addCategory( $data );
				$this->restart( 'manage/catalog/bookstore/category/edit/'.$categoryId );
			}
		}
		$model		= new Model_Catalog_Bookstore_Category( $this->env );
		$category	= [];
		foreach( $model->getColumns() as $column )
			$category[$column]	= $this->request->get( $column );
		$category['parentId']	= (int) $parentId;
		$this->addData( 'category', (object) $category );
		$this->addData( 'categories', $this->logic->getCategories( array(), array( 'rank' => 'ASC' ) ) );
	}

	public function ajaxSetTab( $tabKey ){
		$this->session->set( 'manage.catalog.bookstore.category.tab', $tabKey );
		exit;
	}

	public function edit( $categoryId ){
		$words		= (object) $this->getWords( 'edit' );
		$category	= $this->logic->getCategory( $categoryId );
		if( !$category ){
			$this->messenger->noteError( $words->msgErrorInvalidId );
			$this->restart( NULL, TRUE );
		}
		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			if( !strlen( $data['label_de'] ) )
				$this->messenger->noteError( $words->msgErrorLabelMissing );
			else{
				$this->logic->editCategory( $categoryId, $data );
				$this->restart( 'manage/catalog/bookstore/category/edit/'.$categoryId );
			}
		}
		$this->addData( 'category', $this->logic->getCategory( $categoryId ) );
		$this->addData( 'categories', $this->logic->getCategories( array(), array( 'rank' => 'ASC' ) ) );
		$this->addData( 'nrArticles', $this->logic->countArticlesInCategory( $categoryId, TRUE ) );
		$this->addData( 'articles', $this->logic->getCategoryArticles( $category, array( 'rank' => 'ASC' ) ) );
	}

	public function index(){
		$this->addData( 'categories', $this->logic->getCategories() );
	}

	public function remove( $categoryId ){
		$words		= (object) $this->getWords( 'remove' );
		$category	= $this->logic->getCategory( $categoryId );
		if( !$category ){
			$this->messenger->noteError( $words->msgErrorInvalidId );
			$this->restart( NULL, TRUE );
		}
		if( $this->logic->countArticlesInCategory( $categoryId, TRUE ) ){
			$this->messenger->noteError( $words->msgErrorNotEmpty );
			$this->restart( 'edit/'.$categoryId, TRUE );
		}
		$this->logic->removeCategory( $categoryId );
		$this->messenger->noteSuccess( $words->msgSuccess, htmlentities( $category->label_de, ENT_QUOTES, 'UTF-8' ) );
		$this->restart( ( $category->parentId ? 'edit/'.$category->parentId : NULL ), TRUE );
	}
}
?>
