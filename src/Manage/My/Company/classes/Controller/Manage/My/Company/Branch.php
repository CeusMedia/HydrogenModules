<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_My_Company_Branch extends Controller
{
	protected $userId;
	protected $messenger;
	protected $request;
	protected $modelBranch;
	protected $modelBranchImage;
	protected $modelCompany;
	protected $modelCompanyUser;
	protected $modelUser;
	protected $branches;
	protected $companies;
	protected $user;

	public function add( $companyId = NULL )
	{
		$words		= (object) $this->getWords( 'msg' );
		$data		= $this->request->getAllFromSource( 'POST' );

		if( $this->request->get( 'save' ) ){
			if( empty( $data['title'] ) )
				$this->messenger->noteError( $words->errorTitleMissing );
			else if( $model->getAll( ['title' => $data['title']] ) )
				$this->messenger->noteError( $words->errorTitleExisting, $data['title'] );
			if( empty( $data['companyId'] ) )
				$this->messenger->noteError( $words->errorCompanyMissing );
			if( !$this->isMyCompany( $data['companyId'] ) )
				$this->messenger->noteError( $words->errorCompanyNotOwned );
			if( empty( $data['city'] ) )
				$this->messenger->noteError( $words->errorCityMissing );
			if( empty( $data['postcode'] ) )
				$this->messenger->noteError( $words->errorPostcodeMissing );
			if( empty( $data['street'] ) )
				$this->messenger->noteError( $words->errorStreetMissing );
			if( empty( $data['number'] ) )
				$this->messenger->noteError( $words->errorNumberMissing );

			if( !$this->messenger->gotError() ){
				$data['createdAt']	= time();
				$branchId			= $this->modelBranch->add( $data );
				$this->modelBranch->extendWithGeocodes( $branchId );
				$this->messenger->noteSuccess( $words->successCreated, $data['title'] );
				$this->restart( NULL, TRUE );
			}
		}
		$data	= new stdClass();
		if( $companyId ){
			//  @todo check ownage of company!
			foreach( $this->modelCompany->get( $companyId ) as $key => $value )
				$data->$key	= $value;
		}
		else{
			foreach( $this->modelBranch->getColumns() as $column )
				$data->$column	= htmlentities ( $this->request->get( $column ) );
		}
		$this->view->addData( 'branch', $data );
		$this->view->addData( 'companies', $this->companies );
	}

	public function addImage( $branchId )
	{
		$this->checkBranch( $branchId );
		$image		= $this->request->get( 'image' );
		try{
			$upload		= new Logic_Upload( $this->env );
			$upload->setUpload( image );										//  @todo handle upload errors before
			if( !$upload->checkIsImage() )
				$this->messenger->noteError( 'Das ist kein Bild.' );
			else if( !$upload->checkSize( 1048576 ) )							//  @todo to configuration
				$this->messenger->noteError( 'Das Bild ist zu groß.' );
			else{
				$extension	= pathinfo( $image['name'], PATHINFO_EXTENSION );
				$imageName	= $branchId.'_'.md5( time() ).'.'.$extension;
				$imagePath	= './images/branches/';								//  @todo to configuration + mkdir path
				$upload->saveTo( $imagePath.$imageName );
				$data	= array(
					'branchId'		=> $branchId,
					'filename'		=> $imageName,
					'title'			=> $this->request->get( 'image_title' ),
					'uploadedAt'	=> time()
				);
				$this->modelBranchImage->add( $data );
				$this->messenger->noteSuccess( 'Bild erfolgreich hochgeladen.' );
			}
		}
		catch( Exception $e ){
			$this->messenger->noteError( 'Fehler: '.$e->getMessage() );
		}
		$this->restart( 'edit/'.$branchId, TRUE );
	}

/*	public function delete( $branchId ){
		$data			= $this->modelBranch->get( $branchId );
		if( !$data ){
			$this->messenger->noteError( 'Invalid ID: '.$branchId );
			return $this->restart( NULL, TRUE );
		}
		$model->remove( $branchId );
		$this->messenger->noteSuccess( 'Removed: '.$data['title'] );
		$this->restart( NULL, TRUE );
	}*/

	public function edit( $branchId )
	{
		$words			= (object) $this->getWords( 'msg' );
		$branch			= $this->checkBranch( $branchId );

		if( $this->request->get( 'save' ) ){
			$data	= $this->request->getAllFromSource( 'POST' );
			if( empty( $data['title'] ) )
				$this->messenger->noteError( $words->errorTitleMissing );
			else if( $this->modelBranch->getAll( ['title' => $data['title'], 'branchId' => '!= '.$branchId] ) )
				$this->messenger->noteError( $words->errorTitleExisting, $data['title'] );
			if( empty( $data['companyId'] ) )
				$this->messenger->noteError( $words->errorCompanyMissing );
			if( !$this->isMyCompany( $data['companyId'] ) )
				$this->messenger->noteError( $words->errorCompanyNotOwned );
			if( empty( $data['city'] ) )
				$this->messenger->noteError( $words->errorCityMissing );
			if( empty( $data['postcode'] ) )
				$this->messenger->noteError( $words->errorPostcodeMissing );
			if( empty( $data['street'] ) )
				$this->messenger->noteError( $words->errorStreetMissing );
			if( empty( $data['number'] ) )
				$this->messenger->noteError( $words->errorNumberMissing );

			if( !$this->messenger->gotError() ){
				$data['modifiedAt']	= time();
				$this->modelBranch->edit( $branchId, $data );
				$this->messenger->noteSuccess( $words->successModified, $data['title'] );
#				if( !$modelBranch->get( $branchId )->x )
					$this->modelBranch->extendWithGeocodes( $branchId );
				$this->restart( 'edit/'.$branchId, TRUE );
			}
		}
		$branch->images		= $this->modelBranchImage->getAllByIndex( 'branchId', $branchId );
		$branch->company	= $this->companies[$branch->companyId];
		$this->view->addData( 'branch', $branch	);
		$this->view->addData( 'companies', $this->companies );
	}


	public function index()
	{
		$words		= (object) $this->getWords( 'index' );
		$branches	= $this->getMyBranches( 'title' );
		if( count( $branches ) === 1 ){
			$this->restart( 'edit/'.$branches[0]->branchId, TRUE );
		}
		$this->view->addData( 'branches', $branches );
	}

	/**
	 *	@todo		check ownership of branch
	 */
	public function removeImage( $branchId, $imageId )
	{
		$model			= new Model_Branch_Image( $this->env );
		$words			= (object) $this->getWords( 'msg' );

		$image			= $model->get( $imageId );
		if( !$image )
			$this->messenger->noteFailure( $words->errorImageIdInvalid );
		if( !$this->isMyBranch( $image->branchId ) )
			$this->messenger->noteFailure( $words->errorImageNotOwned );
		if( !$this->messenger->gotError() ){
			@unlink( './images/branches/'.$image->filename);
			$model->remove( $imageId );
			$this->messenger->noteSuccess( $words->successImageRemoved, $image->title );
		}
		$this->restart( 'edit/'.$branchId, TRUE );
	}

	protected function __onInit(): void
	{
		$this->messenger		= $this->env->getMessenger();
		$this->request			= $this->env->getRequest();
		$this->modelBranch		= new Model_Branch( $this->env );
		$this->modelBranchImage	= new Model_Branch_Image( $this->env );
		$this->modelCompany		= new Model_Company( $this->env );
		$this->modelCompanyUser	= new Model_Company_User( $this->env );
		$this->modelUser		= new Model_User( $this->env );
		$this->userId			= $this->env->getSession()->get( 'auth_user_id' );
		$this->user				= $this->checkCurrentUser();
		$this->companies		= $this->getMyCompanies();
		$this->branches			= $this->getMyBranches();
	}

	protected function checkBranch( $branchId )
	{
		if( !$this->modelBranch->get( $branchId ) ){
			$words	= (object) $this->getWords( 'msg' );
			$this->messenger->noteError( $words->errorBranchInvalid );
			$this->restart( NULL, TRUE );
		}
		if( !$this->isMyBranch( $branchId ) ){
			$this->messenger->noteError( $words->errorBranchNotOwned );
			$this->restart( NULL, TRUE );
		}
		return $this->branches[$branchId];
	}

	protected function checkCurrentUser()
	{
		$user		= $this->modelUser->get( $this->userId );
		if( !$user )
			$this->restart();
		return $user;
	}

	protected function getMyBranches( $sortByColumn = 'branchId' ): array
	{
		$list		= [];
		foreach( $this->getMyCompanies() as $company ){
			$branches	= $this->modelBranch->getAllByIndex( 'companyId', $company->companyId );
			foreach( $branches as $branch ){
				$branch->company				= $company;
				$list[$branch->{$sortByColumn}]	= $branch;
			}
		}
		ksort( $list );
		return $list;
	}

	protected function getMyCompanies( $sortByColumn = 'companyId' ): array
	{
		$list		= [];
		$relations	= $this->modelCompanyUser->getAllByIndex( 'userId', $this->userId );
		foreach( $relations as $relation ){
			$company	= $this->modelCompany->get( $relation->companyId );
			$list[$company->{$sortByColumn}]	= $company;
		}
		return $list;
	}

	protected function isMyBranch( $branchId ): bool
	{
		return array_key_exists( $branchId, $this->branches );
	}

	protected function isMyCompany( $companyId ): bool
	{
		return array_key_exists( $companyId, $this->companies );
	}
}
