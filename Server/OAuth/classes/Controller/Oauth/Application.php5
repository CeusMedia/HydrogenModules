<?php
/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 */
/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014 Ceus Media
 *	@version		$Id$
 *	@todo			code doc
 */
class Controller_Oauth_Application extends CMF_Hydrogen_Controller{

	/**	@var		Model_Oauth_Application		$model		Application storage model */
	protected $model;

	public function __onInit(){
		$this->model		= new Model_Oauth_Application( $this->env );
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->userId		= (int) $this->env->getSession()->get( 'userId' );
	}
	
	public function add(){
		$words		= (object) $this->getWords( 'msg' );
		if( $this->request->has( 'save' ) ){
			$clientId		= $this->model->getNewClientId( $this->userId );
			$clientSecret	= $this->model->getNewClientSecret( $clientId, $this->userId );
			$data			= array(
				'userId'		=> $this->userId,
				'type'			=> $this->request->get( 'type' ),
				'status'		=> 0,
				'clientId'		=> $clientId,
				'clientSecret'	=> $clientSecret,
				'title'			=> $this->request->get( 'title' ),
				'description'	=> $this->request->get( 'description' ),
				'url'			=> $this->request->get( 'url' ),
				'createdAt'		=> time(),
			);
			$applicationId	= $this->model->add( $data );
			$this->messenger->noteSuccess( $words->successAdded );
			$this->restart( 'edit/'.$applicationId, TRUE );
		}
		$this->addData( 'application', $this->request );
	}

	protected function checkAccess( $applicationId ){
		$words		= (object) $this->getWords( 'msg' );
		if( !$this->isUserApplication( $applicationId ) ){
			$this->messenger->noteError( $words->errorAccessDenied );
			$this->restart( NULL, TRUE );
		}
	}

	public function edit( $applicationId ){
		$words		= (object) $this->getWords( 'msg' );
		$this->checkAccess( $applicationId );
		if( $this->request->has( 'save' ) ){
			$data	= array(
				'type'			=> $this->request->get( 'type' ),
				'clientSecret'	=> $this->request->get( 'clientSecret' ),
				'title'			=> $this->request->get( 'title' ),
				'description'	=> $this->request->get( 'description' ),
				'url'			=> $this->request->get( 'url' ),
				'modifiedAt'	=> time(),
			);
			if( strlen( trim( $this->request->get( 'status' ) ) ) )
				$data['status']	= $this->request->get( 'status' );
			$applicationId	= $this->model->edit( $applicationId, $data );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'applicationId', $applicationId );
		$this->addData( 'application', $this->model->get( $applicationId ) );
	}

	public function index( $page = 0, $limit = 10 ){
		$orders			= array( 'title' => 'ASC' );
		$limits			= array( abs( $page ) * abs( $limit ), abs( $limit ) );
		$conditions		= array( 'userId' => (int) $this->userId );
		$this->addData( 'applications', $this->model->getAll( $conditions, $orders, $limits ) );
	}

	protected function isUserApplication( $applicationId ){
		$application	= $this->model->get( $applicationId );
		if( $application && (int) $application->userId === (int) $this->userId )
			return TRUE;
		return FALSE;
	}

	public function view( $applicationId ){
		$this->checkAccess( $applicationId );
		$this->addData( 'application', $this->model->get( $applicationId ) );
		
		$modelAccess	= new Model_Oauth_AccessToken( $this->env );
		$modelCode		= new Model_Oauth_Code( $this->env );
		$modelRefresh	= new Model_Oauth_RefreshToken( $this->env );

		$accessTokens	= $modelAccess->getAllByIndex( 'oauthApplicationId', $applicationId );
		$authCodes		= $modelCode->getAllByIndex( 'oauthApplicationId', $applicationId );
		$refreshTokens	= $modelRefresh->getAllByIndex( 'oauthApplicationId', $applicationId );

		$this->addData( 'accessTokens', $accessTokens );
		$this->addData( 'authCodes', $authCodes );
		$this->addData( 'refreshTokens', $refreshTokens );
	}
	
	public function remove( $applicationId, $removeMode = NULL, $modeResourceId = NULL ){
		$this->checkAccess( $applicationId );
		if( !empty( $removeMode ) ){
			switch( $removeMode ){
				case 'access':
					$model	= new Model_Oauth_AccessToken( $this->env );
					$model->remove( $modeResourceId );
					break;
				case 'code':
					$model	= new Model_Oauth_Code( $this->env );
					$model->remove( $modeResourceId );
					break;
				case 'refresh':
					$model	= new Model_Oauth_RefreshToken( $this->env );
					$model->remove( $modeResourceId );
					break;
				default:
					$this->messenger->noteError( 'Invalid remove mode' );
					break;
			}
		}
		else{
			$this->checkAccess( $applicationId );
			$this->model->remove( $applicationId );
			$this->messenger->noteSuccess( $words->successRemoved );
		}
		$this->restart( NULL, TRUE );
	}

	public function enable( $applicationId ){
		$words		= (object) $this->getWords( 'msg' );
		$this->checkAccess( $applicationId );
		$this->model->edit( $applicationId, array( 'status' => 1, 'modifiedAt' => time() ) );	
		$this->messenger->noteSuccess( $words->successEnabled );
		$this->restart( 'edit/'.$applicationId, TRUE );
	}

	public function disable( $applicationId ){
		$words		= (object) $this->getWords( 'msg' );
		$this->checkAccess( $applicationId );
		$this->model->edit( $applicationId, array( 'status' => 0, 'modifiedAt' => time() ) );	
		$this->messenger->noteSuccess( $words->successDisabled );
		$this->restart( 'edit/'.$applicationId, TRUE );
	}
}
?>