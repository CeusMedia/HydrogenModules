<?php
class Controller_Manage_My_Company extends CMF_Hydrogen_Controller{

	protected $request;
	protected $messenger;
	protected $modelBranch;
	protected $modelCompany;
	protected $modelCompanyUser;
	protected $modelUser;
	protected $userId;

	public function __onInit(){
		$this->request			= $this->env->getRequest();
		$this->messenger		= $this->env->getMessenger();
		$this->modelBranch		= new Model_Branch( $this->env );
		$this->modelCompany		= new Model_Company( $this->env );
		$this->modelCompanyUser	= new Model_Company_User( $this->env );
		$this->modelUser		= new Model_User( $this->env );
		$this->userId			= $this->env->getSession()->get( 'userId' );
		$this->companies		= $this->getMyCompanies();
	}

	protected function checkCompany( $companyId ){
		$words	= (object) $this->getWords( 'msg' );
		if( !array_key_exists( $companyId, $this->companies ) ){
			$this->messenger->noteFailure( $words->errorCompanyInvalid );
			$this->restart( NULL, TRUE );
		}
		if( !$this->isMyCompany( $companyId ) ){
			$this->messenger->noteError( $words->errorCompanyNotOwned );
			$this->restart( NULL, TRUE );
		}
		return $this->companies[$companyId];
	}

	protected function getMyCompanies( $sortByColumn = 'companyId' ){
		$list		= array();
		$relations	= $this->modelCompanyUser->getAllByIndex( 'userId', $this->userId );
		foreach( $relations as $relation ){
			$company	= $this->modelCompany->get( $relation->companyId );
			$list[$company->{$sortByColumn}]	= $company;
		}
		ksort( $list );
		return $list;
	}

	protected function isMyCompany( $companyId ){
		$indices	= array( 'companyId' => $companyId, 'userId' => $this->userId );
		return $this->modelCompanyUser->countByIndices( $indices );
	}

	public function edit( $companyId ){
		$words		= (object) $this->getWords( 'msg' );
		$company	= $this->checkCompany( $companyId );

		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAllFromSource( 'POST' )->getAll();
			if( empty( $data['title'] ) )
				$this->messenger->noteError( $words->errorTitleMissing );
			else if( $this->modelCompany->getAll( array( 'title' => $data['title'], 'companyId' => '!='.$companyId ) ) )
				$this->messenger->noteError( $words->errorTitleExisting, $data['title'] );
			if( empty( $data['city'] ) )
				$this->messenger->noteError( $words->errorCityMissing );
			if( empty( $data['postcode'] ) )
				$this->messenger->noteError( $words->errorPostcodeMissing );
			if( empty( $data['street'] ) )
				$this->messenger->noteError( $words->errorStreetExisting );
			if( empty( $data['number'] ) )
				$this->messenger->noteError( $words->errorNumberExisting );
			if( !$this->messenger->gotError() ){
				$data['modifiedAt']	= time();
				$this->modelCompany->edit( $companyId, $data );
				$this->messenger->noteSuccess( $words->successModified, $data['title'] );
				$this->restart( 'edit/'.$companyId, TRUE );
			}
		}
		$user				= $this->modelUser->get( $this->userId );
		$modelRole			= new Model_Role( $this->env );
		$user->role			= $modelRole->get( $user->roleId );
		$user->company		= $this->modelCompany->get( $companyId );
		$company->branches	= $this->modelBranch->getAllByIndex( 'companyId', $companyId, array( 'title' => 'ASC' ) );
		$company->users		= array();
		$relations	= $this->modelCompanyUser->getAllByIndex( 'companyId', $companyId );
		foreach( $relations as $relation )
			$company->users[$relation->userId]	= $this->modelUser->get( $relation->userId );

		$this->view->addData( 'company', $company );
	}

	public function index(){

/*		if( !$this->companies ){
			$messenger->noteFailure( 'Kein Unternehmen zugewiesen. Weiterleitung zu Startseite.' );
			$this->restart();
		}
*/		if( count( $this->companies ) === 1 ){
			$companyIds	= array_keys( $this->companies );
			$this->restart( 'edit/'.$companyIds[0], TRUE );
		}
		$this->addData( 'companies', $this->companies );
	}

	public function uploadLogo( $companyId ){
		$company	= $this->checkCompany( $companyId );
		$image		= $this->request->get( 'image' );
		try{
			$imagePath	= 'images/companies/';									//  @todo to configuration
			Folder_Editor::createFolder( $imagePath, 0777 );
			$upload		= new Logic_Upload( $this->env );
			$upload->setUpload( $image );										//  @todo handle upload errors before
			if( !$upload->checkIsImage() )
				$this->messenger->noteError( 'Das ist kein Bild.' );
			else if( !$upload->checkSize( 1048576 ) )							//  @todo to configuration
				$this->messenger->noteError( 'Das Bild ist zu groß.' );
			else{
				$extension	= pathinfo( $image['name'], PATHINFO_EXTENSION );
				$imageName	= $companyId.'_'.md5( time() ).'.'.$extension;
				$upload->saveTo( $imagePath.$imageName );
				$image		= new UI_Image( $imagePath.$imageName );
				$processor	= new UI_Image_Processing( $image );
				$size		= min( $image->getWidth(), $image->getHeight() );
				$offsetX	= (int) floor( ( $image->getWidth() - $size ) / 2 );
				$offsetY	= (int) floor( ( $image->getHeight() - $size ) / 2 );
				$processor->crop( $offsetX, $offsetY, $size, $size );
				$processor->scaleDownToLimit( 512, 512 );
				$image->save();
				$data	= array(
					'logo'			=> $imageName,
					'modifiedAt'	=> time()
				);
				if( $company->logo )
					unlink( $imagePath.$company->logo );
				$this->modelCompany->edit( $companyId, $data );
				$this->messenger->noteSuccess( 'Das Bild wurde hochgeladen und gespeichert.' );
			}
		}
		catch( Exception $e ){
			$this->messenger->noteError( 'Fehler: '.$e->getMessage() );
		}
		$this->restart( 'edit/'.$companyId, TRUE );
	}
}
?>
