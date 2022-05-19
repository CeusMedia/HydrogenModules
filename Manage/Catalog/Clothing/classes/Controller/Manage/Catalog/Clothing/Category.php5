<?php
class Controller_Manage_Catalog_Clothing_Category extends CMF_Hydrogen_Controller
{
	public function add()
	{
		if( $this->request->has( 'save' ) ){
			$data				= $this->request->getAll();
			$data['createdAt']	= time();
			$categoryId	= $this->modelCategory->add( $data );
			$this->messenger->noteSuccess( 'Added.' );
			$this->restart( 'edit/'.$categoryId, TRUE );
		}
	}

	public function edit( $categoryId )
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

	public function index()
	{
		$this->addData( 'categories', $this->modelCategory->getAll() );
	}

	public function remove( $categoryId )
	{
		$this->addData( 'category', $this->modelCategory->get( $categoryId ) );
		$this->modelCategory->remove( $categoryId );
		$this->messenger->noteSuccess( 'Removed.' );
		$this->restart( NULL, TRUE );
	}

	protected function __onInit()
	{
		$this->request			= $this->env->getRequest();
		$this->messenger		= $this->env->getMessenger();
//		$this->modelArticle		= new Model_Catalog_Clothing_Article( $this->env );
		$this->modelCategory	= new Model_Catalog_Clothing_Category( $this->env );
	}
}
