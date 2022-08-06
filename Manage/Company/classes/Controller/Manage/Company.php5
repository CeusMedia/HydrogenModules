<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Company extends Controller
{
	public function activate( $companyId )
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$model			= new Model_Company( $this->env );
		$model->edit( $companyId, array( 'status' => 1, 'modifiedAt' => time() ) );
		$company		= $model->get( $companyId );
		$messenger->noteSuccess( 'Unternehmen "'.$company->title.'" aktiviert.' );
		$this->restart( NULL, TRUE );
	}

	public function deactivate( $companyId )
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$model			= new Model_Company( $this->env );
		$model->edit( $companyId, array( 'status' => -1, 'modifiedAt' => time() ) );
		$company		= $model->get( $companyId );
		$messenger->noteSuccess( 'Unternehmen "'.$company->title.'" deaktiviert.' );
		$this->restart( NULL, TRUE );
	}

	public function add(){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'add' );
		$model		= new Model_Company( $this->env );
		$data		= $request->getAllFromSource( 'POST' );

		if( $request->get( 'doAdd' ) ){
			if( empty( $data['title'] ) )
				$messenger->noteError( $words->msgNoTitle );
			else if( $model->getAll( array( 'title' => $data['title'] ) ) )
				$messenger->noteError( $words->msgTitleExisting, $data['title'] );
			if( empty( $data['city'] ) )
				$messenger->noteError( $words->msgNoCity );
			if( empty( $data['postcode'] ) )
				$messenger->noteError( $words->msgNoPostcode );
			if( empty( $data['street'] ) )
				$messenger->noteError( $words->msgNoStreet );
			if( empty( $data['number'] ) )
				$messenger->noteError( $words->msgNoNumber );

			if( !$messenger->gotError() ){
				$data['createdAt']	= time();
				$model->add( $data );
				$messenger->noteSuccess( $words->msgSuccess, $data['title'] );
				$this->restart( NULL, TRUE );
			}
		}
		$data	= new stdClass();
		foreach( $model->getColumns() as $column )
			$data->$column	= htmlentities ( $request->get( $column ) );
		$this->view->addData( 'company', $data );
	}

	public function delete( $companyId ){
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$model			= new Model_Company( $this->env );
		$data			= $model->get( $companyId );
		if( !$data ){
			$messenger->noteError( 'Invalid ID: '.$companyId );
			return $this->restart( NULL, TRUE );
		}
		$model->remove( $companyId );
		$messenger->noteSuccess( 'Removed: '.$data['title'] );
		$this->restart( NULL, TRUE );
	}

	public function edit( $companyId ){
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$title			= $request->get( 'title' );
		$words			= (object) $this->getWords( 'edit' );
		$modelCompany	= new Model_Company( $this->env );
		$modelUser		= new Model_User( $this->env );
		$data		= $request->getAllFromSource( 'POST' );
		if( $request->get( 'doEdit' ) ){
			if( empty( $data['title'] ) )
				$messenger->noteError( $words->msgNoTitle );
			else if( $modelCompany->getAll( array( 'title' => $data['title'], 'companyId' => '!= '.$companyId ) ) )
				$messenger->noteError( $words->msgTitleExisting, $data['title'] );
			if( empty( $data['city'] ) )
				$messenger->noteError( $words->msgNoCity );
			if( empty( $data['postcode'] ) )
				$messenger->noteError( $words->msgNoPostcode );
			if( empty( $data['street'] ) )
				$messenger->noteError( $words->msgNoStreet );
			if( empty( $data['number'] ) )
				$messenger->noteError( $words->msgNoNumber );

			if( !$messenger->gotError() ){
				$data['modifiedAt']	= time();
				$modelCompany->edit( $companyId, $data );
				$messenger->noteSuccess( $words->msgSuccess, $data['title'] );
				$this->restart( NULL, TRUE );
			}
		}
		$company		= $modelCompany->get( $companyId );
		$branches		= [];
		$modelBranch	= new Model_Branch( $this->env );
		$branches		= $modelBranch->getAllByIndex( 'companyId', $companyId );
		$company->branches	= $branches;
		$users		= [];
		if( in_array( 'companyId', $modelUser->getColumns() ) )
			$users	= $modelUser->getAllByIndex( 'companyId', $companyId );
		$company->users		= $users;
		$this->view->addData( 'company', $company );
	}

	public function filter(){
		$this->env->getMessenger()->noteSuccess( "Companies have been filtered." );
		$this->restart( NULL, TRUE );
	}

	public function index(){
		$model	= new Model_Company( $this->env );
		$this->view->setData( array( 'companies' => $model->getAll() ) );
		$this->addData( 'companies', $model->getAll() );
	}
}
?>
