<?php

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\Net\HTTP\PartitionSession as HttpPartitionSession;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_Catalog_Category extends Controller
{
	protected HttpRequest $request;
	protected HttpPartitionSession $session;
	protected Logic_Catalog $logic;
	protected MessengerResource $messenger;

	public function add( $parentId = NULL ): void
	{
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
		$category	= [];
		foreach( $model->getColumns() as $column )
			$category[$column]	= $this->request->get( $column );
		$category['parentId']	= (int) $parentId;
		$this->addData( 'category', (object) $category );
		$this->addData( 'categories', $this->logic->getCategories() );
	}

	public function ajaxSetTab( $tabKey )
	{
		$this->session->set( 'manage.catalog.category.tab', $tabKey );
		exit;
	}

	/**
	 *	@param		int|string		$categoryId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string $categoryId ): void
	{
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

	public function index(): void
	{
		$this->addData( 'categories', $this->logic->getCategories() );
	}

	/**
	 *	@param		int|string		$categoryId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( int|string $categoryId ): void
	{
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

	protected function __onInit(): void
	{
		$this->env->getRuntime()->reach( 'Controller_Manage_Catalog_Category::init start' );
		$this->logic		= new Logic_Catalog( $this->env );
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->env->getRuntime()->reach( 'Controller_Manage_Catalog_Category::init done' );
	}
}
