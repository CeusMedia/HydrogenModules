<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Company extends Controller
{
	/**
	 *	@param		int|string		$companyId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function activate( int|string $companyId ): void
	{
		$messenger		= $this->env->getMessenger();
		$model			= new Model_Company( $this->env );
		$model->edit( $companyId, ['status' => 1, 'modifiedAt' => time()] );
		$company		= $model->get( $companyId );
		$messenger->noteSuccess( 'Unternehmen "'.$company->title.'" aktiviert.' );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int|string		$companyId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function deactivate( int|string $companyId ): void
	{
		$messenger		= $this->env->getMessenger();
		$model			= new Model_Company( $this->env );
		$model->edit( $companyId, ['status' => -1, 'modifiedAt' => time()] );
		$company		= $model->get( $companyId );
		$messenger->noteSuccess( 'Unternehmen "'.$company->title.'" deaktiviert.' );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'add' );
		$model		= new Model_Company( $this->env );
		$data		= $request->getAllFromSource( 'POST' );

		if( $request->get( 'doAdd' ) ){
			if( empty( $data['title'] ) )
				$messenger->noteError( $words->msgNoTitle );
			else if( $model->getAll( ['title' => $data['title']] ) )
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

	/**
	 *	@param		int|string		$companyId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function delete( int|string $companyId ): void
	{
		$messenger		= $this->env->getMessenger();
		$model			= new Model_Company( $this->env );
		$data			= $model->get( $companyId );
		if( !$data ){
			$messenger->noteError( 'Invalid ID: '.$companyId );
			$this->restart( NULL, TRUE );
		}
		$model->remove( $companyId );
		$messenger->noteSuccess( 'Removed: '.$data['title'] );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int|string		$companyId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string $companyId ): void
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'edit' );
		$modelCompany	= new Model_Company( $this->env );
		$modelUser		= new Model_User( $this->env );
		$data		= $request->getAllFromSource( 'POST' );
		if( $request->get( 'doEdit' ) ){
			if( empty( $data['title'] ) )
				$messenger->noteError( $words->msgNoTitle );
			else if( $modelCompany->getAll( ['title' => $data['title'], 'companyId' => '!= '.$companyId] ) )
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
		$modelBranch	= new Model_Branch( $this->env );
		$branches		= $modelBranch->getAllByIndex( 'companyId', $companyId );
		$company->branches	= $branches;
		$users		= [];
		if( in_array( 'companyId', $modelUser->getColumns() ) )
			$users	= $modelUser->getAllByIndex( 'companyId', $companyId );
		$company->users		= $users;
		$this->view->addData( 'company', $company );
	}

	public function filter(): void
	{
		$this->env->getMessenger()->noteSuccess( "Companies have been filtered." );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@return		void
	 */
	public function index(): void
	{
		$model	= new Model_Company( $this->env );
		$this->view->setData( ['companies' => $model->getAll()] );
		$this->addData( 'companies', $model->getAll() );
	}
}
