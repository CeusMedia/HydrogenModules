<?php
/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2014-2024 Ceus Media (https://ceusmedia.de/)
 *	@version		$Id$
 */
class Controller_Oauth_Application extends Controller
{
	/**	@var		Model_Oauth_Application		$model		Application storage model */
	protected Model_Oauth_Application $model;

	protected HttpRequest $request;
	protected MessengerResource $messenger;

	protected ?string $userId;

	public function add()
	{
		$words		= (object) $this->getWords( 'msg' );
		if( $this->request->has( 'save' ) ){
			$clientId		= $this->model->getNewClientId( $this->userId );
			$clientSecret	= $this->model->getNewClientSecret( $clientId, $this->userId );
			$data			= [
				'userId'		=> $this->userId,
				'type'			=> $this->request->get( 'type' ),
				'status'		=> 0,
				'clientId'		=> $clientId,
				'clientSecret'	=> $clientSecret,
				'title'			=> $this->request->get( 'title' ),
				'description'	=> $this->request->get( 'description' ),
				'url'			=> $this->request->get( 'url' ),
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			];
			$applicationId	= $this->model->add( $data );
			$this->messenger->noteSuccess( $words->successAdded );
			$this->restart( 'edit/'.$applicationId, TRUE );
		}
		$this->addData( 'application', $this->request );
	}

	/**
	 *	@param		string		$applicationId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function edit( string $applicationId ): void
	{
		$words		= (object) $this->getWords( 'msg' );
		$this->checkAccess( $applicationId );
		if( $this->request->has( 'save' ) ){
			$data	= [
				'type'			=> $this->request->get( 'type' ),
				'clientSecret'	=> $this->request->get( 'clientSecret' ),
				'title'			=> $this->request->get( 'title' ),
				'description'	=> $this->request->get( 'description' ),
				'url'			=> $this->request->get( 'url' ),
				'modifiedAt'	=> time(),
			];
			if( strlen( trim( $this->request->get( 'status' ) ) ) )
				$data['status']	= $this->request->get( 'status' );
			$applicationId	= $this->model->edit( $applicationId, $data );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'applicationId', $applicationId );
		$this->addData( 'application', $this->model->get( $applicationId ) );
	}

	/**
	 *	@todo 		 think about the fullAccess code below - is it needed?
	 */
	public function index( $page = 0, $limit = 10 )
	{
		$orders			= ['title' => 'ASC'];
		$limits			= [abs( $page ) * abs( $limit ), abs( $limit )];

		$conditions		= [];
//		if( !Logic_Authentication::getInstance( $this->env )->hasFullAccess() )
//			$conditions		= array( 'userId' => (int) $this->userId );

		$this->addData( 'applications', $this->model->getAll( $conditions, $orders, $limits ) );
	}

	/**
	 *	@param		string		$applicationId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function view( string $applicationId )
	{
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

	/**
	 *	@param		string			$applicationId
	 *	@param		string|NULL		$removeMode
	 *	@param		string|NULL		$modeResourceId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function remove( string $applicationId, ?string $removeMode = NULL, ?string $modeResourceId = NULL )
	{
		$words		= (object) $this->getWords( 'msg' );
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

	/**
	 *	@param		string		$applicationId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function enable( string $applicationId ): void
	{
		$words		= (object) $this->getWords( 'msg' );
		$this->checkAccess( $applicationId );
		$this->model->edit( $applicationId, array( 'status' => 1, 'modifiedAt' => time() ) );
		$this->messenger->noteSuccess( $words->successEnabled );
		$this->restart( 'edit/'.$applicationId, TRUE );
	}

	/**
	 *	@param		string		$applicationId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function disable( string $applicationId ): void
	{
		$words		= (object) $this->getWords( 'msg' );
		$this->checkAccess( $applicationId );
		$this->model->edit( $applicationId, ['status' => 0, 'modifiedAt' => time()] );
		$this->messenger->noteSuccess( $words->successDisabled );
		$this->restart( 'edit/'.$applicationId, TRUE );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->model		= new Model_Oauth_Application( $this->env );
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->userId		= (int) $this->env->getSession()->get( 'auth_user_id' );
	}

	/**
	 *	@param		string		$applicationId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function checkAccess( string $applicationId )
	{
		$words		= (object) $this->getWords( 'msg' );
		if( !$this->isUserApplication( $applicationId ) ){
			$this->messenger->noteError( $words->errorAccessDenied );
			$this->restart( NULL, TRUE );
		}
	}

	/**
	 *	@param		string		$applicationId
	 *	@return		bool
	 *	@throws		ReflectionException
	 */
	protected function isUserApplication( string $applicationId ): bool
	{
		if( Logic_Authentication::getInstance( $this->env )->hasFullAccess() )
			return TRUE;
		$application	= $this->model->get( $applicationId );
		if( $application && (int) $application->userId === (int) $this->userId )
			return TRUE;
		return FALSE;
	}
}
