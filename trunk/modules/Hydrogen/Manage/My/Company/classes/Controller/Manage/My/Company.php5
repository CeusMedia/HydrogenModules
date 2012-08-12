<?php
class Controller_Manage_My_Company extends CMF_Hydrogen_Controller
{
	public function edit( $companyId ){
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'index' );
		$model		= new Model_Company( $this->env );
		$data		= $request->getAllFromSource( 'POST' )->getAll();
		if( $request->get( 'doEdit' ) ){
			if( empty( $data['title'] ) )
				$messenger->noteError( $words->msgNoTitle );
			else if( $model->getAll( array( 'title' => $data['title'], 'companyId' => '!='.$companyId ) ) )
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
				$model->edit( $companyId, $data );
				$messenger->noteSuccess( $words->msgSuccess, $data['title'] );
			}
		}
		$this->restart( './manage/my/company' );
	}

	public function index(){
		$config			= $this->env->getConfig();
		$session		= $this->env->getSession();
		$messenger		= $this->env->getMessenger();
		$roleId			= $session->get( 'roleId' );
		$modelCompany	= new Model_Company( $this->env );
		$modelUser		= new Model_User( $this->env );
		$user			= $modelUser->get( $session->get( 'userId' ) );
		if( !$user ){
			$messenger->noteFailure( 'Ungültiger Benutzer. Zugriff verweigert.' );
			$this->restart( './' );
		}
		$company	= $modelCompany->get( $user->companyId );
		if( !$company ){
			$messenger->noteFailure( 'Ungültiges Unternehmen. Zugriff verweigert.' );
			$this->restart( './' );
		}

		if( $config->get( 'module.roles' ) ){
			$modelRole	= new Model_Role( $this->env );
			$user->role	= $modelRole->get( $user->roleId );
		}
		if( $config->get( 'module.companies' ) ){
			$modelCompany	= new Model_Company( $this->env );
			$user->company	= $modelCompany->get( $user->companyId );
		}
		$branches			= array();
		if( $this->env->getModules()->has( 'Manage_Branch' ) ){
			$modelBranch	= new Model_Branch( $this->env );
			$branches		= $modelBranch->getAllByIndex( 'companyId', $user->companyId );
			
		}
		$company->branches	= $branches;
		$company->users		= $modelUser->getAllByIndex( 'companyId', $user->companyId );
		$this->view->addData( 'company', $company );
	}
}
?>