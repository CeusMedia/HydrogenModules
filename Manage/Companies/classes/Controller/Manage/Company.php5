<?php
class Controller_Manage_Company extends CMF_Hydrogen_Controller{

	protected $frontend;
	protected $messenger;
	protected $modelCompany;
	protected $request;

	public function __onInit(){
		$this->request			= $this->env->getRequest();
		$this->messenger		= $this->env->getMessenger();
		$this->frontend			= Logic_Frontend::getInstance( $this->env );
		$this->modelCompany		= new Model_Company( $this->env );
	}

	public function activate( $companyId ){
		$company	= $this->checkCompany( $companyId );
		$this->modelCompany->edit( $companyId, array(
			'status'		=> 2,
			'modifiedAt'	=> time()
		) );
		$this->messenger->noteSuccess( 'Unternehmen "'.$company->title.'" aktiviert.' );
		$this->restart( './manage/company' );
	}

	public function add(){
		$words		= (object) $this->getWords( 'add' );
		$data		= $this->request->getAllFromSource( 'POST' )->getAll();

		if( $this->request->has( 'save' ) ){
			if( empty( $data['title'] ) )
				$this->messenger->noteError( $words->msgNoTitle );
			else if( $this->modelCompany->getAll( array( 'title' => $data['title'] ) ) )
				$this->messenger->noteError( $words->msgTitleExisting, $data['title'] );
			if( empty( $data['city'] ) )
				$this->messenger->noteError( $words->msgNoCity );
			if( empty( $data['postcode'] ) )
				$this->messenger->noteError( $words->msgNoPostcode );
			if( empty( $data['street'] ) )
				$this->messenger->noteError( $words->msgNoStreet );
			if( empty( $data['number'] ) )
				$this->messenger->noteError( $words->msgNoNumber );

			if( !$this->messenger->gotError() ){
				$data['createdAt']	= time();
				$companyId	= $this->modelCompany->add( $data );
				$this->messenger->noteSuccess( $words->msgSuccess, $data['title'] );
				$this->restart( 'edit/'.$companyId, TRUE );
			}
		}
		$data	= new stdClass();
		foreach( $this->modelCompany->getColumns() as $column )
			$data->$column	= htmlentities ( $this->request->get( $column ) );
		$this->view->addData( 'company', $data );
	}

	protected function checkCompany( $companyId ){
		$words		= (object) $this->getWords( 'msg' );
		$company	= $this->modelCompany->get( $companyId );
		if( !$company ){
			$this->messenger->noteFailure( $words->errorCompanyInvalid );
			$this->restart( NULL, TRUE );
		}
		return $company;
	}

	public function deactivate( $companyId ){
		$company	= $this->checkCompany( $companyId );
		$this->modelCompany->edit( $companyId, array(
			'status'	=> -2,
			'modifiedAt' => time()
		) );
		$this->messenger->noteSuccess( 'Unternehmen "'.$company->title.'" deaktiviert.' );
		$this->restart( './manage/company' );
	}

	public function edit( $companyId ){
		$title			= $this->request->get( 'title' );
		$words			= (object) $this->getWords( 'edit' );
		$modelUser		= new Model_User( $this->env );
		$data		= $this->request->getAllFromSource( 'POST' )->getAll();
		if( $this->request->has( 'save' ) ){
			if( empty( $data['title'] ) )
				$this->messenger->noteError( $words->msgNoTitle );
			else if( $this->modelCompany->getAll( array( 'title' => $data['title'], 'companyId' => '!='.$companyId ) ) )
				$this->messenger->noteError( $words->msgTitleExisting, $data['title'] );
			if( empty( $data['city'] ) )
				$this->messenger->noteError( $words->msgNoCity );
			if( empty( $data['postcode'] ) )
				$this->messenger->noteError( $words->msgNoPostcode );
			if( empty( $data['street'] ) )
				$this->messenger->noteError( $words->msgNoStreet );
			if( empty( $data['number'] ) )
				$this->messenger->noteError( $words->msgNoNumber );

			if( !$this->messenger->gotError() ){
				$data['modifiedAt']	= time();
				$this->modelCompany->edit( $companyId, $data );
				$this->messenger->noteSuccess( $words->msgSuccess, $data['title'] );
				$this->restart( './manage/company' );
			}
		}
		$company		= $this->modelCompany->get( $companyId );
		$branches		= array();
		$modelBranch	= new Model_Branch( $this->env );
		$branches		= $modelBranch->getAllByIndex( 'companyId', $companyId, array( 'title' => 'ASC' ) );
		$company->branches	= $branches;
		$users		= array();
		if( in_array( 'companyId', $modelUser->getColumns() ) )
			$users	= $modelUser->getAllByIndex( 'companyId', $companyId );
		$company->users		= $users;
		$this->view->addData( 'company', $company );
		$this->view->addData( 'frontend', $this->frontend );
	}

	public function filter(){
		$this->messenger->noteSuccess( "Companies have been filtered." );
		$this->restart( './manage/company' );
	}

	public function index(){
		$this->addData( 'companies', $this->modelCompany->getAll() );
	}

	public function reject( $companyId ){
		$company	= $this->checkCompany( $companyId );
		$this->modelCompany->edit( $companyId, array(
			'status'	=> -1,
			'modifiedAt' => time()
		) );
		$this->messenger->noteSuccess( 'Unternehmen "'.$company->title.'" abgelehnt.' );
		$this->restart( './manage/company' );
	}

	public function remove( $companyId ){
		$company	= $this->checkCompany( $companyId );
		$this->env->getCaptain()->callHook( 'Company', 'remove', $this, array(
			'companyId' => $companyId
		) );
		if( $company->logo )
			@unlink( $this->frontend->getPath().'images/companies/'.$company->logo );
		$modelUser		= new Model_Company_User( $this->env );
		$modelUser->removeByIndex( 'companyId', $companyId );
		$this->modelCompany->remove( $companyId );
		$this->messenger->noteSuccess( 'Removed: '.$company->title );
		$this->restart( './manage/company' );
	}

	public function uploadLogo( $companyId ){
		$company	= $this->checkCompany( $companyId );
		$image		= $this->request->get( 'image' );
		try{
			$imagePath	= $this->frontend->getPath().'images/companies/';		//  @todo to configuration
			FS_Folder_Editor::createFolder( $imagePath, 0777 );
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
