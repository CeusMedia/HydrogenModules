<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

/**
 *	@todo		localize
 *	@todo		integrate validation from Controller_Admin_User::edit
 *	@todo   	validate email, check against trash mail domains
 */
class Controller_Manage_My_User extends Controller
{
	protected HttpRequest $request;
	protected Dictionary $session;
	protected MessengerResource $messenger;
	protected Logic_Authentication $logicAuth;
	protected Model_User $modelUser;
	protected string $userId;

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 *	@todo		integrate validation from Controller_Admin_User::edit
	 */
	public function edit(): void
	{
		$this->checkConfirmationPassword();

		$words		= (object) $this->getWords( 'edit' );
		/** @var ?Entity_User $user */
		$user		= $this->modelUser->get( $this->userId );

		$options		= $this->env->getConfig()->getAll( 'module.resource_users.', TRUE );
		$needsEmail		= (int) $options->get( 'email.mandatory' );
		$needsFirstname	= (int) $options->get( 'firstname.mandatory' );
		$needsSurname	= (int) $options->get( 'surname.mandatory' );

		if( !trim( $this->request->get( 'password' ) ) ){
			$this->messenger->noteError( $words->msgPasswordMissing );
			$this->restart( NULL, TRUE );
		}
		if( !$this->checkPassword( $user, $this->request->get( 'password' ) ) ){
			$this->messenger->noteError( $words->msgPasswordMismatch );
			$this->restart( NULL, TRUE );
		}

		$data		= $this->request->getAllFromSource( 'POST' );
		$deniedKeys	= ['username', 'email', 'password', 'createdAt', 'modifiedAt', 'roleId', 'companyId', 'saveUser'];
		foreach( $deniedKeys as $deniedKey )
			unset( $data[$deniedKey] );

		if( $needsFirstname && empty( $data['firstname'] ) )
			$this->messenger->noteError( $words->msgNoFirstname );
		if( $needsSurname && empty( $data['surname'] ) )
			$this->messenger->noteError( $words->msgNoSurname );

		/*		if( empty( $data['postcode'] ) )
			$this->messenger->noteError( $words->msgNoPostcode );
		if( empty( $data['city'] ) )
			$this->messenger->noteError( $words->msgNoCity );
		if( empty( $data['street'] ) )
			$this->messenger->noteError( $words->msgNoStreet );
		if( empty( $data['number'] ) )
			$this->messenger->noteError( $words->msgNoNumber );*/

		if( !$this->messenger->gotError() ){
//			if( strlen( $data['country'] ) > 2 ){
//				$countries			= array_flip( $this->env->getLanguage()->getWords( 'countries' ) );
//				if( !isset( $countries[$data['country']] ) )
//				$data['country']	= $countries[$data['country']];
//			}
			$this->modelUser->edit( $this->userId, $data );
			$this->messenger->noteSuccess( $words->msgSuccess );
		}
		$this->restart( './manage/my/user' );
	}

	/**
	 *	@todo		integrate validation from Controller_Admin_User::edit
	 *	@todo		Redesign: Send mail with confirmation before applying new mail address
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function email(): void
	{
		$this->checkConfirmationPassword();

		$options	= $this->env->getConfig()->getAll( 'module.resource_users.', TRUE );
		$words		= (object) $this->getWords( 'email' );
		/** @var ?Entity_User $user */
		$user		= $this->modelUser->get( $this->userId );
		$email		= trim( $this->request->get( 'email' ) );

		if( $email === $user->email ){
			$this->messenger->noteNotice( $words->msgNoticeNoChanges );
			$this->restart( NULL, TRUE );
		}
		if( !strlen( $email ) ){
			if( $options->get( 'email.mandatory' ) ){
				$this->messenger->noteError( $words->msgEmailMissing );
				$this->restart( NULL, TRUE );
			}
		}
		else{
			$indices	= [
				'email'		=> $email,
				'userId'	=> '!= '.$this->userId,
//				'status'	=> '>= -1',																//  disabled for integrity
			];
			if( $this->modelUser->getByIndices( $indices ) ){
				$this->messenger->noteError( $words->msgEmailExisting, $email );
				$this->restart( NULL, TRUE );
			}
		}
		$this->modelUser->edit( $this->userId, ['email' => $email] );
		$this->messenger->noteSuccess( $words->msgSuccess );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index(): void
	{
		$options	= $this->env->getConfig()->getAll( 'module.resource_users.', TRUE );
		$roleId		= $this->session->get( 'auth_role_id' );
		$modelRole	= new Model_Role( $this->env );

		if( !$this->userId ){
			$this->messenger->noteFailure( 'Nicht eingeloggt. Zugriff verweigert.' );
			$this->restart( './' );
		}
		/** @var ?Entity_User $user */
		$user		= $this->modelUser->get( $this->userId );
		$user->role	= $modelRole->get( $user->roleId );
		if( class_exists( 'Model_Company' ) ){
			$modelCompany	= new Model_Company( $this->env );
			$user->company	= $modelCompany->get( $user->companyId );
		}

		$modelPassword	= new Model_User_Password( $this->env );
		$passwords		= $modelPassword->getAll( ['userId' => $this->userId] );

		$this->addData( 'currentUserId', $this->userId );
		$this->addData( 'user', $user );
		$this->addData( 'passwords', $passwords );
		$this->addData( 'pwdMinLength', (int) $options->get( 'password.length.min' ) );
		$this->addData( 'pwdMinStrength', (int) $options->get( 'password.strength.min' ) );
		$this->addData( 'mandatoryEmail', $options->get( 'email.mandatory' ) );
		$this->addData( 'mandatoryFirstname', $options->get( 'firstname.mandatory' ) );
		$this->addData( 'mandatorySurname', $options->get( 'surname.mandatory' ) );
		$this->addData( 'mandatoryAddress', $options->get( 'address.mandatory' ) );
		$this->addData( 'countries', $this->env->getLanguage()->getWords( 'countries' ) );
	}

	/**
	 *	@todo		integrate validation from Controller_Admin_User::edit
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function password(): void
	{
		$words		= (object) $this->getWords( 'password' );
		/** @var ?Entity_User $user */
		$user		= $this->modelUser->get( $this->userId );

		$options		= $this->env->getConfig()->getAll( 'module.resource_users.', TRUE );
		$pwdMinLength	= (int) $options->get( 'password.length.min' );
		$pwdMinStrength	= (int) $options->get( 'password.strength.min' );
		$passwordPepper	= trim( $options->get( 'password.pepper' ) );								//  string to pepper password with

		$data				= $this->request->getAllFromSource( 'POST', TRUE );
		$passwordOld		= $data->get( 'passwordOld', '' );
		$passwordNew		= $data->get( 'passwordNew', '' );
		$passwordConfirm	= trim( $data->get( 'passwordConfirm', '' ) );

		if( '' === $passwordOld )
			$this->messenger->noteError( $words->msgPasswordOldMissing );
		else if( '' === $passwordNew )
			$this->messenger->noteError( $words->msgPasswordNewMissing );
		else if( '' === $passwordConfirm )
			$this->messenger->noteError( $words->msgPasswordConfirmMissing );
		else if( $passwordOld === $passwordNew )
			$this->messenger->noteError( $words->msgPasswordNewSame );
		else if( $passwordNew !== $passwordConfirm )
			$this->messenger->noteError( $words->msgPasswordConfirmMismatch );
		else if( !$this->checkPassword( $user, $passwordOld ) )
			$this->messenger->noteError( $words->msgPasswordOldMismatch );
		else if( $pwdMinLength && strlen( $passwordNew ) < $pwdMinLength )
			$this->messenger->noteError( $words->msgPasswordNewTooShort, $pwdMinLength );
//		else if( $pwdMinStrength && ... < $pwdMinStrength )
//			$this->messenger->noteError( $words->msgPasswordNewTooWeek, $pwdMinStrength );
		else{
			if( class_exists( 'Logic_UserPassword' ) ){												//  @todo  remove line if old user password support decays
				$logic			= Logic_UserPassword::getInstance( $this->env );
				$userPassword	= $logic->addPassword( $user, $passwordNew );
				$logic->activatePassword( $userPassword );
			}
			else{
				$this->modelUser->edit( $this->userId, ['password' => md5( $passwordNew.$passwordPepper )] );
			}
			$this->messenger->noteSuccess( $words->msgSuccess );
		}
		$this->restart( './manage/my/user' );
	}

	/**
	 *	@param		$confirmed
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( $confirmed = NULL ): void
	{
		$this->addData( 'userId', $this->userId );
		$this->addData( 'user', $this->modelUser->get( $this->userId ) );
		if( $this->request->getMethod()->isPost() && $confirmed ){
			$this->checkConfirmationPassword( 'manage/my/user/remove' );
			$dbc	= $this->env->getDatabase();
			$dbc->beginTransaction();
			try{
				$payload	= [
					'userId'		=> $this->userId,
					'informOthers'	=> TRUE,
				];
				$this->callHook( 'User', 'remove', $this, $payload );
				$dbc->commit();
				$this->restart( 'auth/logout' );
			}
			catch( Exception $e ){
			//	 @todo handle exception
				$this->messenger->noteError( 'Failed: '.$e->getMessage() );
				$dbc->rollBack();
			}
			$this->restart( 'remove', TRUE );
		}
	}

	/**
	 *	@todo		integrate validation from Controller_Admin_User::edit
	 *	@todo		Redesign: Send mail with confirmation before applying new username
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function username(): void
	{
		$this->checkConfirmationPassword();

		$options	= $this->env->getConfig()->getAll( 'module.resource_users.', TRUE );
		$words		= (object) $this->getWords( 'username' );
		/** @var ?Entity_User $user */
		$user		= $this->modelUser->get( $this->userId );
		$username	= trim( $this->request->get( 'username' ) );

		if( '' === $username ){
			$this->messenger->noteError( $words->msgUsernameMissing );
			$this->restart( NULL, TRUE );
		}
		if( $username === $user->username ){
			$this->messenger->noteNotice( $words->msgNoticeNoChanges );
			$this->restart( NULL, TRUE );
		}
		$indices	= [
			'username'	=> $username,
			'userId'	=> '!= '.$this->userId,
//			'status'	=> '>= -1',																//  disabled for integrity
		];
		if( $this->modelUser->hasByIndices( $indices ) ){
			$this->messenger->noteError( $words->msgUsernameExisting, $username );
			$this->restart( NULL, TRUE );
		}
		$this->modelUser->edit( $this->userId, ['username' => $username] );
		$this->messenger->noteSuccess( $words->msgSuccess );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->logicAuth	= Logic_Authentication::getInstance( $this->env );
		$this->modelUser	= new Model_User( $this->env );

		$msg	= (object) $this->getWords( 'msg' );
		if( !$this->env->getModules()->has( 'Resource_Authentication' ) ){
			$this->messenger->noteFailure( $msg->failureNoAuthentication );
			$this->restart();
		}
		if( !$this->logicAuth->isAuthenticated() ){
//			$this->messenger->noteFailure( $msg->errorNotAuthenticated );
			$this->restart( 'auth/login' );
		}
		$this->userId = $this->logicAuth->getCurrentUserId();
		if( !$this->modelUser->has( $this->userId ) ){
			$this->messenger->noteError( $msg->errorInvalidUser );
			$this->restart();
		}
	}

	/**
	 *	@param		$from
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function checkConfirmationPassword( $from = NULL ): void
	{
		$msg		= (object) $this->getWords( 'msg' );
		$password	= trim( $this->request->get( 'password' ) );
		if( !strlen( $password ) ){
			$this->messenger->noteError( $msg->errorPasswordMissing );
			if( $from )
				$this->restart( $from );
			$this->restart( NULL, TRUE );
		}
		if( !$this->checkPassword( $this->modelUser->get( $this->userId ), $password ) ){
			$this->messenger->noteError( $msg->errorPasswordMismatch );
			if( $from )
				$this->restart( $from );
			$this->restart( NULL, TRUE );
		}
	}

	/**
	 *	Check given user password against old and newer password storage.
	 *	If newer password store is supported and old password has been found, migration will apply.
	 *
	 *	@access		protected
	 *	@param   	object   	$user		User data object
	 *	@param   	string		$password	Password to check on login
	 *	@todo   	clean up if support for old passwort decays
	 *	@todo   	reintegrate cleansed lines into login method (if this makes sense)
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function checkPassword( object $user, string $password ): bool
	{
		if( class_exists( 'Logic_UserPassword' ) ){													//  @todo  remove line if old user password support decays
			$logic			= Logic_UserPassword::getInstance( $this->env );
			if( $logic->validateUserPassword( $user->userId, $password ) )
				return TRUE;
		}
		$pepper		= $this->env->getConfig()->get( 'module.resource_users.password.pepper' );
		if( $user->password === md5( $password.$pepper ) )
			return TRUE;
		return FALSE;
	}
}
