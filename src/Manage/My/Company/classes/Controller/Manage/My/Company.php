<?php

use CeusMedia\Common\FS\Folder\Editor as FolderEditor;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\UI\Image;
use CeusMedia\Common\UI\Image\Processing as ImageProcessing;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_My_Company extends Controller
{
	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Model_Branch $modelBranch;
	protected Model_Company $modelCompany;
	protected Model_Company_User $modelCompanyUser;
	protected Model_User $modelUser;
	protected ?string $userId			= NULL;
	protected array $companies;

	/**
	 *	@param		int|string		$companyId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string $companyId ): void
	{
		$words		= (object) $this->getWords( 'msg' );
		$company	= $this->checkCompany( $companyId );

		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAllFromSource( 'POST' );
			if( empty( $data['title'] ) )
				$this->messenger->noteError( $words->errorTitleMissing );
			else if( $this->modelCompany->getAll( ['title' => $data['title'], 'companyId' => '!= '.$companyId] ) )
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
		/** @var ?Entity_User $user */
		$user				= $this->modelUser->get( $this->userId );
		$modelRole			= new Model_Role( $this->env );
		$user->role			= $modelRole->get( $user->roleId );
		$user->company		= $this->modelCompany->get( $companyId );
		$company->branches	= $this->modelBranch->getAllByIndex( 'companyId', $companyId, ['title' => 'ASC'] );
		$company->users		= [];
		$relations	= $this->modelCompanyUser->getAllByIndex( 'companyId', $companyId );
		foreach( $relations as $relation )
			$company->users[$relation->userId]	= $this->modelUser->get( $relation->userId );

		$this->view->addData( 'company', $company );
	}

	public function index(): void
	{

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

	/**
	 *	@param		int|string		$companyId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function uploadLogo( int|string $companyId ): void
	{
		$company	= $this->checkCompany( $companyId );
		$image		= $this->request->get( 'image' );
		try{
			$imagePath	= 'images/companies/';									//  @todo to configuration
			FolderEditor::createFolder( $imagePath, 0777 );
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
				$image		= new Image( $imagePath.$imageName );
				$processor	= new ImageProcessing( $image );
				$size		= min( $image->getWidth(), $image->getHeight() );
				$offsetX	= (int) floor( ( $image->getWidth() - $size ) / 2 );
				$offsetY	= (int) floor( ( $image->getHeight() - $size ) / 2 );
				$processor->crop( $offsetX, $offsetY, $size, $size );
				$processor->scaleDownToLimit( 512, 512 );
				$image->save();
				$data	= [
					'logo'			=> $imageName,
					'modifiedAt'	=> time()
				];
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

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->messenger		= $this->env->getMessenger();
		$this->modelBranch		= new Model_Branch( $this->env );
		$this->modelCompany		= new Model_Company( $this->env );
		$this->modelCompanyUser	= new Model_Company_User( $this->env );
		$this->modelUser		= new Model_User( $this->env );
		$this->userId			= $this->env->getSession()->get( 'auth_user_id' );
		$this->companies		= $this->getMyCompanies();
	}

	/**
	 *	@param		int|string		$companyId
	 *	@return		object
	 */
	protected function checkCompany( int|string $companyId ): object
	{
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

	/**
	 *	@param		string		$sortByColumn
	 *	@return		array
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function getMyCompanies( string $sortByColumn = 'companyId' ): array
	{
		$list		= [];
		$relations	= $this->modelCompanyUser->getAllByIndex( 'userId', $this->userId );
		foreach( $relations as $relation ){
			$company	= $this->modelCompany->get( $relation->companyId );
			$list[$company->{$sortByColumn}]	= $company;
		}
		ksort( $list );
		return $list;
	}

	/**
	 *	@param		int|string		$companyId
	 *	@return		bool
	 */
	protected function isMyCompany( int|string $companyId ): bool
	{
		$indices	= ['companyId' => $companyId, 'userId' => $this->userId];
		return 0 !== $this->modelCompanyUser->countByIndices( $indices );
	}
}
