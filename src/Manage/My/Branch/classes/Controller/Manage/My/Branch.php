<?php

use CeusMedia\Common\Net\HTTP\UploadErrorHandler;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_My_Branch extends Controller
{
	/**
	 *	@param		int|string		$branchId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function activate( int|string $branchId ): void
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$model			= new Model_Branch( $this->env );
		$model->edit( $branchId, ['status' => 1] );
		$branch			= $model->get( $branchId );
		$messenger->noteSuccess( 'Filiale "'.$branch->title.'" aktiviert.' );
		$this->restart( './manage/my/branch' );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		$request		= $this->env->getRequest();
		$session		= $this->env->getSession();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'add' );
		$model			= new Model_Branch( $this->env );
		$data			= $request->getAllFromSource( 'POST' );

		$modelUser		= new Model_User( $this->env );
		$user			= $modelUser->get( (int) $session->get( 'auth_user_id' ) );
		$data['companyId']	= $user->companyId;

		if( $request->get( 'doAdd' ) ){
			if( empty( $data['title'] ) )
				$messenger->noteError( $words->msgNoTitle );
			else if( $model->getAll( ['title' => $data['title']] ) )
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

	/**
	 *	@param		int|string		$branchId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addImage( int|string $branchId ): void
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$image			= $request->get( 'image' );

		if( $image['error'] ){
			$w			= (object) $this->getWords( 'upload-errors', 'main' );

			$messages		= [
				UPLOAD_ERR_INI_SIZE		=> $w->UPLOAD_ERR_INI_SIZE,
				UPLOAD_ERR_FORM_SIZE	=> $w->UPLOAD_ERR_FORM_SIZE,
				UPLOAD_ERR_PARTIAL		=> $w->UPLOAD_ERR_PARTIAL,
				UPLOAD_ERR_NO_FILE		=> $w->UPLOAD_ERR_NO_FILE,
				UPLOAD_ERR_NO_TMP_DIR	=> $w->UPLOAD_ERR_NO_TMP_DIR,
				UPLOAD_ERR_CANT_WRITE	=> $w->UPLOAD_ERR_CANT_WRITE,
				UPLOAD_ERR_EXTENSION	=> $w->UPLOAD_ERR_EXTENSION,
			];
			$handler		= new UploadErrorHandler();
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
		$data	= [
			'branchId'		=> $branchId,
			'filename'		=> $imageName,
			'title'			=> $request->get( 'image_title' ),
			'uploadedAt'	=> time()
		];
		$model->add( $data );
		$messenger->noteSuccess( 'Bild erfolgreich hochgeladen.' );
		$this->restart( './manage/my/branch/edit/'.$branchId );
	}

/*	public function delete( $branchId )
	{
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$model			= new Model_Branch( $this->env );
		$data			= $model->get( $branchId );
		if( !$data ){
			$messenger->noteError( 'Invalid ID: '.$branchId );
			return $this->restart( NULL, TRUE );
		}
		$model->remove( $branchId );
		$messenger->noteSuccess( 'Removed: '.$data['title'] );
		$this->restart( NULL, TRUE );
	}*/

	/**
	 *	@param		int|string		$branchId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function deactivate( int|string $branchId ): void
	{
		$messenger		= $this->env->getMessenger();
		$model			= new Model_Branch( $this->env );
		$model->edit( $branchId, ['status' => -1] );
		$branch			= $model->get( $branchId );
		$messenger->noteSuccess( 'Filiale "'.$branch->title.'" deaktiviert.' );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int|string		$branchId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string $branchId ): void
	{
		$config			= $this->env->getConfig();
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'edit' );
		$data			= $request->getAllFromSource( 'POST' );
		$modelBranch	= new Model_Branch( $this->env );
		$modelImage		= new Model_Branch_Image( $this->env );

		if( $request->get( 'doEdit' ) ){
			if( empty( $data['title'] ) )
				$messenger->noteError( $words->msgNoTitle );
			else if( $modelBranch->getAll( ['title' => $data['title'], 'branchId' => '!= '.$branchId] ) )
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
				$this->restart( NULL, TRUE );
			}
		}
		$branch			= $modelBranch->get( $branchId );
		$branch->images	= $modelImage->getAllByIndex( 'branchId', $branchId );
		if( $config->get( 'module.companies' ) ){
			$modelCompany		= new Model_Company( $this->env );
			$branch->company	= $modelCompany->get( $branch->companyId );
		}
		$this->view->addData( 'branch', $branch	);
	}

	/**
	 *	@param		string		$redirect
	 *	@return		object
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function getCurrentUser( string $redirect = 'auth/logout' ): object
	{
		$modelUser	= new Model_User( $this->env );
		$userId		= (int) $this->env->getSession()->get( 'auth_user_id' );
		$user		= $modelUser->get( $userId );
		if( !$user )
			$this->breakOnFailure( 'userIdInvalid', $redirect );
		return $user;
	}

	/**
	 *	@return		object|NULL
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function getMyCompany(): ?object
	{
		$user		= $this->getCurrentUser();
		$model		= new Model_Company( $this->env );
		return $model->get( $user->companyId );
	}

	/**
	 *	@return		array<object>
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function getMyBranches(): array
	{
		$user		= $this->getCurrentUser();
		$model		= new Model_Branch( $this->env );
		return $model->getAllByIndex( 'companyId', $user->companyId );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index(): void
	{
		$config			= $this->env->getConfig();
		$session		= $this->env->getSession();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'index' );
		$userId			= $session->get( 'auth_user_id' );
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

	/**
	 *	@todo		check ownership of branch
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeImage( $branchId, $imageId ): void
	{
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
		$this->restart( 'edit/'.$branchId, TRUE );
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->js->addUrl( "https://maps.google.com/maps/api/js?sensor=false" );
	}

	protected function breakOnFailure( $messageKey, $redirect = 'manage/my' ): void
	{
		$this->env->getLanguage()->load( 'manage/my' );
		$words		= (object) $this->getWords( 'msg', 'manage/my' );
		$this->env->getMessenger()->noteFailure( $words->$messageKey );
		$this->restart( $redirect );
	}

	/**
	 *	@param		int|string		$branchId
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function isMyBranch( int|string $branchId ): bool
	{
		$user		= $this->getCurrentUser();
		$model		= new Model_Branch( $this->env );
		$conditions	= ['companyId' => $user->companyId, 'branchId' => $branchId];
		return (bool) $model->count( $conditions );
	}

	/**
	 *	@param		int|string		$companyId
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function isMyCompany( int|string $companyId ): bool
	{
		$user		= $this->getCurrentUser();
		$model		= new Model_Company( $this->env );
		$conditions	= ['companyId' => $user->companyId, 'companyId' => $companyId];
		return (bool) $model->count( $conditions );
	}
}
