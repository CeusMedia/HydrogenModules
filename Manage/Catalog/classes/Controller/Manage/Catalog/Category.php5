<?php
class Controller_Manage_Catalog_Category extends CMF_Hydrogen_Controller{

	protected function __onInit(){
		$this->env->clock->profiler->tick( 'Controller_Manage_Catalog_Category::init start' );
		$this->logic		= new Logic_Catalog( $this->env );
		$this->session		= $this->env->getSession();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->env->clock->profiler->tick( 'Controller_Manage_Catalog_Category::init done' );
	}

	static public function ___onTinyMCE_getLinkList( $env, $context, $module, $arguments = array() ){
		$cache		= $env->getCache();
		if( !( $categories = $cache->get( 'catalog.tinymce.links.categories' ) ) ){
			$logic		= new Logic_Catalog( $env );
			$config		= $env->getConfig()->getAll( 'module.manage_catalog.', TRUE );
			$language	= $env->getLanguage()->getLanguage();
			$conditions	= array( 'visible' => '>0', 'parentId' => 0 );
			$categories	= $logic->getCategories( $conditions, array( 'rank' => 'ASC' ) );
			foreach( $categories as $nr1 => $item ){
				$conditions	= array( 'visible' => '>0', 'parentId' => $item->categoryId );
				$subs		= $logic->getCategories( $conditions, array( 'rank' => 'ASC' ) );
				foreach( $subs as $nr2 => $sub ){
					$subs[$nr2] = (object) array(
						'title'	=> $sub->{"label_".$language},
						'value'	=> 'catalog/category/'.$item->categoryId,
					);
				}
				$categories[$nr1] = (object) array(
					'title'	=> $item->{"label_".$language},
					'menu'	=> array_values( $subs ),
				);
			}
			$cache->set( 'catalog.tinymce.links.categories', $categories );
		}
        $context->list  = array_merge( $context->list, array( (object) array(		//  extend global collection by submenu with list of items
			'title'	=> 'Kategorien:',											//  label of submenu @todo extract
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
				$this->restart( 'manage/catalog/category/edit/'.$categoryId );
			}
		}
		$model		= new Model_Catalog_Category( $this->env );
		$category	= array();
		foreach( $model->getColumns() as $column )
			$category[$column]	= $this->request->get( $column );
		$category['parentId']	= (int) $parentId;
		$this->addData( 'category', (object) $category );
		$this->addData( 'categories', $this->logic->getCategories() );
	}

	public function ajaxSetTab( $tabKey ){
		$this->session->set( 'manage.catalog.category.tab', $tabKey );
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
				$this->restart( 'manage/catalog/category/edit/'.$categoryId );
			}
		}
		$this->addData( 'category', $this->logic->getCategory( $categoryId ) );
		$this->addData( 'categories', $this->logic->getCategories() );
		$this->addData( 'nrArticles', $this->logic->countArticlesInCategory( $categoryId, TRUE ) );
		$this->addData( 'articles', $this->logic->getCategoryArticles( $category ) );
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
		$this->messenger->noteSuccess( $words->msgSuccess, htmlentities( $category->title, ENT_QUOTES, 'UTF-8' ) );
		$this->restart( ( $category->parentId ? 'edit/'.$category->parentId : NULL ), TRUE );
	}
}
?>
