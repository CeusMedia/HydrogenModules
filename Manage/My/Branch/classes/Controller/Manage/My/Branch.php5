<?php
class Controller_Manage_My_Branch extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->env->page->js->addUrl( "https://maps.google.com/maps/api/js?sensor=false" );
	}

	public function activate( $branchId )
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$model			= new Model_Branch( $this->env );
		$model->edit( $branchId, array( 'status' => 1 ) );
		$branch			= $model->get( $branchId );
		$messenger->noteSuccess( 'Filiale "'.$branch->title.'" aktiviert.' );
		$this->restart( './manage/my/branch' );
	}
	
	public function add(){
		$request		= $this->env->getRequest();
		$session		= $this->env->getSession();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'add' );
		$model			= new Model_Branch( $this->env );
		$data			= $request->getAllFromSource( 'POST' )->getAll();

		$modelUser		= new Model_User( $this->env );
		$user			= $modelUser->get( (int) $session->get( 'userId' ) );
		$data['companyId']	= $user->companyId;
		
		
		if( $request->get( 'doAdd' ) ){
			if( empty( $data['title'] ) )
				$messenger->noteError( $words->msgNoTitle );
			else if( $model->getAll( array( 'title' => $data['title'] ) ) )
				$messenger->noteError( $words->msgTitleExisting, $data['title'] );
			if( empty( $data['companyId'] ) )
				$messenger->noteError( $words->msgNoCompany );
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
				$branchId			= $model->add( $data );
				$model->extendWithGeocodes( $branchId );
				$messenger->noteSuccess( 'Added: '.$data['title'] );
				$this->restart( './manage/my/branch' );
			}
		}
		$data	= new stdClass();
		foreach( $model->getColumns() as $column )
			$data->$column	= htmlentities ( $request->get( $column ) );
		$this->view->addData( 'branch', $data );
	}

	public function addImage( $branchId ){
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$image			= $request->get( 'image' );

		if( $image['error'] ){
			$w			= (object) $this->getWords( 'upload-errors', 'main' );
			
			$messages		= array(
				UPLOAD_ERR_INI_SIZE		=> $w->UPLOAD_ERR_INI_SIZE,
				UPLOAD_ERR_FORM_SIZE	=> $w->UPLOAD_ERR_FORM_SIZE,
				UPLOAD_ERR_PARTIAL		=> $w->UPLOAD_ERR_PARTIAL,
				UPLOAD_ERR_NO_FILE		=> $w->UPLOAD_ERR_NO_FILE,
				UPLOAD_ERR_NO_TMP_DIR	=> $w->UPLOAD_ERR_NO_TMP_DIR,
				UPLOAD_ERR_CANT_WRITE	=> $w->UPLOAD_ERR_CANT_WRITE,
				UPLOAD_ERR_EXTENSION	=> $w->UPLOAD_ERR_EXTENSION,
			);
			$handler		= new Net_HTTP_UploadErrorHandler();
			$handler->setMessages( $messages );
			try{
				$handler->handleErrorFromUpload( $image );
			}
			catch( Exception $e ){
				$messenger->noteError( $e->getMessage() );
				$this->restart( './manage/my/branch/edit/'.$branchId );
			}
			
		}
		$model	= new Model_Branch_Image( $this->env );
		
		$imageName	= $branchId.'_'.md5( time() ).'.'.pathinfo( $image['name'], PATHINFO_EXTENSION );
		$imagePath	= './images/branches/';
		if( !@move_uploaded_file( $image['tmp_name'], $imagePath.$imageName ) )
			throw new RuntimeException( 'Bilddatei konnte nicht im Pfad "'.$imagePath.'" gespeichert werden.' );
		$data	= array(
			'branchId'		=> $branchId, 
			'filename'		=> $imageName,
			'title'			=> $request->get( 'image_title' ),
			'uploadedAt'	=> time()
		);
		$model->add( $data );
		$messenger->noteSuccess( 'Bild erfolgreich hochgeladen.' );
		$this->restart( './manage/my/branch/edit/'.$branchId );
	}

	protected function breakOnFailure( $messageKey, $redirect = 'manage/my' ){
		$this->env->getLanguage()->load( 'manage/my' );
		$words		= (object) $this->getWords( 'msg', 'manage/my' );
		$this->env->getMessenger()->noteFailure( $words->$messageKey );
		$this->restart( $redirect );
	}

/*	public function delete( $branchId ){
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$model			= new Model_Branch( $this->env );
		$data			= $model->get( $branchId );
		if( !$data ){
			$messenger->noteError( 'Invalid ID: '.$branchId );
			return $this->redirect( 'branch' );
		}
		$model->remove( $branchId );
		$messenger->noteSuccess( 'Removed: '.$data['title'] );
		$this->restart( './manage/branch' );
	}*/

	public function deactivate( $branchId )
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$model			= new Model_Branch( $this->env );
		$model->edit( $branchId, array( 'status' => -1 ) );
		$branch			= $model->get( $branchId );
		$messenger->noteSuccess( 'Filiale "'.$branch->title.'" deaktiviert.' );
		$this->restart( './manage/my/branch' );
	}

	public function edit( $branchId ){
		$config			= $this->env->getConfig();
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'edit' );
		$data			= $request->getAllFromSource( 'POST' )->getAll();
		$modelBranch	= new Model_Branch( $this->env );
		$modelImage		= new Model_Branch_Image( $this->env );

		if( $request->get( 'doEdit' ) ){
			if( empty( $data['title'] ) )
				$messenger->noteError( $words->msgNoTitle );
			else if( $modelBranch->getAll( array( 'title' => $data['title'], 'branchId' => '!='.$branchId ) ) )
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
				$modelBranch->edit( $branchId, $data );
				$messenger->noteSuccess( 'Updated: '.$data['title'] );
#				if( !$modelBranch->get( $branchId )->x )
					$modelBranch->extendWithGeocodes( $branchId );
				$this->restart( './manage/my/branch' );
			}
		}
		$branch			= $modelBranch->get( $branchId );
		$branch->images	= $modelImage->getAllByIndex( 'branchId', $branchId );
		if( $config->get( 'module.companies' ) ){
			$modelCompany		= new Model_Company( $this->env );
			$branch->company	= $modelCompany->get( $branch->companyId );
		}
		$this->view->addData( 'branch', $branch	);

		$coupons	= array();
		if( $this->env->getModules()->has( 'Model_Coupon' ) ){
			$modelCoupon	= new Model_Coupon( $this->env );
			$coupons		= $modelCoupon->getAllByIndex( 'branchId', $branchId );
		}
		$this->view->addData( 'coupons', $coupons );
	}
	
	protected function getCurrentUser( $redirect = 'auth/logout' ){
		$modelUser	= new Model_User( $this->env );
		$userId		= (int) $this->env->getSession()->get( 'userId' );
		$user		= $modelUser->get( $userId );
		if( !$user )
			return $this->breakOnFailure( 'userIdInvalid', $redirect );
		return $user;
	}

	protected function getMyCompany(){
		$user		= $this->getCurrentUser();
		$model		= new Model_Company( $this->env );
		return $model->get( $user->companyId );
	}

	protected function getMyBranches(){
		$user		= $this->getCurrentUser();
		$model		= new Model_Branch( $this->env );
		return $model->getAllByIndex( 'companyId', $user->companyId );
	}

	public function index(){
		$config			= $this->env->getConfig();
		$session		= $this->env->getSession();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'index' );
		$userId			= $session->get( 'userId' );
		$modelBranch	= new Model_Branch( $this->env );
		$modelUser		= new Model_User( $this->env );
		$modelCompany	= new Model_Company( $this->env );

		if( !$userId ){
			$messenger->noteFailure( 'Nicht eingeloggt. Zugriff verweigert.' );
			$this->restart( './' );
		}
		$user		= $modelUser->get( $userId );
		if( !$user ){
			$messenger->noteFailure( 'Ungültiger Benutzer. Zugriff verweigert.' );
			$this->restart( './manage/my' );
		}
		$company	= $modelCompany->get( $user->companyId );
		if( !$company ){
			$messenger->noteFailure( 'Ungültiges Unternehmen. Zugriff verweigert.' );
			$this->restart( './manage/my' );
		}
		
		$branches		= $modelBranch->getAllByIndex( 'companyId', $user->companyId );
		foreach( $branches as $nr => $branch )
			$branches[$nr]->company	= $modelCompany->get( $branch->companyId );
		$this->view->addData( 'branches', $branches );
	}

	protected function isMyBranch( $branchId ){
		$user		= $this->getCurrentUser();
		$model		= new Model_Branch( $this->env );
		$conditions	= array( 'companyId' => $user->companyId, 'branchId' => $branchId );
		return (bool) $model->count( $conditions );
	}

	protected function isMyCompany( $companyId ){
		$user		= $this->getCurrentUser();
		$model		= new Model_Company( $this->env );
		$conditions	= array( 'companyId' => $user->companyId, 'companyId' => $companyId );
		return (bool) $model->count( $conditions );
	}
	
	protected function isMyCoupon( $couponId ){
		$user		= $this->getCurrentUser();
		$model		= new Model_Coupon( $this->env );
		$conditions	= array( 'companyId' => $user->companyId, 'couponId' => $couponId );
		return (bool) $model->count( $conditions );
	}

	/**
	 *	@todo		check ownership of branch
	 */
	public function removeImage( $branchId, $imageId ){
		$messenger		= $this->env->getMessenger();
		$model			= new Model_Branch_Image( $this->env );
		$words			= (object) $this->getWords( 'removeImage' );

		$image			= $model->get( $imageId );
		if( !$image )
			$messenger->noteFailure( $words->msgImageIdInvalid );
		if( !$this->isMyBranch( $image->branchId ) )
			$messenger->noteFailure( $words->msgImageNotOwned ); 
		if( !$messenger->gotError() ){
			@unlink( './images/branches/'.$image->filename);
			$model->remove( $imageId );
			$messenger->noteSuccess( $words->msgSuccess, $image->title );
		}
		$this->restart( './manage/my/branch/edit/'.$branchId );
	}
}
?>