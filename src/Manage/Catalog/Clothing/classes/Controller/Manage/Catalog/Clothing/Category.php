<?php

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_Catalog_Clothing_Category extends Controller
{
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Model_Catalog_Clothing_Category $modelCategory;

	public function add(): void
	{
		if( $this->request->has( 'save' ) ){
			$data				= $this->request->getAll();
			$data['createdAt']	= time();
			$categoryId	= $this->modelCategory->add( $data );
			$this->messenger->noteSuccess( 'Added.' );
			$this->restart( 'edit/'.$categoryId, TRUE );
		}
	}

	public function edit( string $categoryId ): void
	{
		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			$data['modifiedAt']	= time();
			$this->modelCategory->edit( $categoryId, $data );
			$this->messenger->noteSuccess( 'Saved.' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'category', $this->modelCategory->get( $categoryId ) );
	}

	public function index(): void
	{
		$this->addData( 'categories', $this->modelCategory->getAll() );
	}

	public function remove( string $categoryId ): void
	{
		$this->addData( 'category', $this->modelCategory->get( $categoryId ) );
		$this->modelCategory->remove( $categoryId );
		$this->messenger->noteSuccess( 'Removed.' );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->messenger		= $this->env->getMessenger();
//		$this->modelArticle		= new Model_Catalog_Clothing_Article( $this->env );
		$this->modelCategory	= new Model_Catalog_Clothing_Category( $this->env );
	}
}
