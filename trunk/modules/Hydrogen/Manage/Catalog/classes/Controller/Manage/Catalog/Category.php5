<?php
class Controller_Manage_Catalog_Category extends CMF_Hydrogen_Controller{

	protected function __onInit(){
		$this->logic		= new Logic_Catalog( $this->env );
		$this->session		= $this->env->getSession();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
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

	public function edit( $categoryId ){
		if( $this->request->has( 'save' ) ){
			$words		= (object) $this->getWords( 'edit' );
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
	}

	public function index(){
		$this->addData( 'categories', $this->logic->getCategories() );
	}

	public function remove( $categoryId ){
		$words	= $this->getWords( 'remove' );
		if( $this->logic->countArticlesInCategory( $categoryId, TRUE ) )
			$this->messenger->noteError( $words->msgErrorNotEmpty );
		else
			$this->logic->removeCategory( $categoryId );
	}
}
?>
