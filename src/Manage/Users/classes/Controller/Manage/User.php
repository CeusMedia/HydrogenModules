<?php
/**
 *	User Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.Controller.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Validation\Predicates;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

/**
 *	User Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.Controller.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */
class Controller_Manage_User extends Controller
{
	public static string $moduleId		= 'Manage_Users';

	protected HttpRequest $request;
	protected MessengerResource $messenger;
	protected Dictionary $config;
	protected Logic_User $logic;

	protected array $countries;
	protected Dictionary $moduleConfig;

	protected array $filters	= [
		'username',
		'roomId',
		'roleId',
		'status',
		'roleId',
		'activity',
		'order',
		'direction',
		'limit'
	];

	/**
	 *	@param		string		$userId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function accept( string $userId ): void
	{
		$this->setStatus( $userId, Model_User::STATUS_ACTIVE );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		if( $this->request->getMethod()->isPost() ){
			$this->handleAddAction();
			$this->restart( NULL, TRUE );
		}

		$modelUser	= new Model_User( $this->env );
		$modelRole	= new Model_Role( $this->env );
		$input		= $this->env->getRequest();														//  allow preset data via GET parameters
		$user		= new Entity_User();
		$columns	= $modelUser->getColumns();
		foreach( $columns as $column ){
			$value  = $input[$column] ?? '';
			if( in_array( $column, ['status', 'gender'] ) )
				$value  = (int) $value;
			$user->$column	= htmlentities( $value, ENT_COMPAT, 'UTF-8' );
		}

		$this->addData( 'user', $user );
		$this->addData( 'roles', $modelRole->getAll() );
	}

	/**
	 *	@param		int|string		$userId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addToGroup( int|string $userId ): void
	{
		$groupId	= $this->env->getRequest()->get( 'groupId' );
		$logicUser	= new Logic_User( $this->env );
		/** @var Model_Group $modelGroup */
		$modelGroup	= $this->getModel( 'Group' );

		/** @var Entity_Group $group */
		$group		= $modelGroup->get( $groupId );
		$logicUser->addUserToGroup( $logicUser->checkId( $userId ), $group );
		$this->restart( 'edit/'.$userId, TRUE );
	}

	/**
	 *	@param		int|string		$userId
	 *	@param		int|string		$groupId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function removeFromGroup( int|string $userId, int|string $groupId ): void
	{
		$logicUser	= new Logic_User( $this->env );
		/** @var Model_Group $modelGroup */
		$modelGroup	= $this->getModel( 'Group' );

		/** @var Entity_Group $group */
		$group		= $modelGroup->get( $groupId );
		$logicUser->removeUserFromGroup( $logicUser->checkId( $userId ), $group );
		$this->restart( 'edit/'.$userId, TRUE );
	}

	/**
	 *	@param		string		$userId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function ban( string $userId ): void
	{
		$this->setStatus( $userId, Model_User::STATUS_BANNED );
	}

	/**
	 *	@param		string		$userId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function disable( string $userId ): void
	{
		$this->setStatus( $userId, Model_User::STATUS_DISABLED );
	}

	/**
	 *	@param		string		$userId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( string $userId ): void
	{
		$modelRole	= new Model_Role( $this->env );

		/** @var Entity_User $user */
		$user	= $this->logic->checkId( $userId, Logic_User::EXTEND_GROUPS | Logic_User::EXTEND_ROLE );
		if( NULL === $user ){
			$this->messenger->noteError( 'Invalid user ID' );
			$this->restart( NULL, TRUE );
		}

		$options		= $this->env->getConfig()->getAll( 'module.resource_users.', TRUE );
		$pwdMinLength	= $options->get( 'password.length.min' );
		$pwdMinStrength	= $options->get( 'password.strength.min' );

		if( $this->request->getMethod()->isPost() ){
			$this->handleEditAction( $user );
		}

		if( empty( $user->country ) )
			$user->country	= strtoupper( $this->env->getLanguage()->getLanguage() );
		$user->country	= $this->countries[$user->country];
		$user->role		= $modelRole->get( $user->roleId );

		$this->addData( 'userId', (int) $userId );
		$this->addData( 'user', $user );
		$this->addData( 'from', $this->request->get( 'from' ) );
		$this->addData( 'roles', $modelRole->getAll() );
		$this->addData( 'pwdMinLength', $pwdMinLength );
		$this->addData( 'pwdMinStrength', $pwdMinStrength );

		if( $this->env->getModules()->has( 'Manage_Projects' ) ){
			$modelProject	= new Model_Project( $this->env );
			$this->addData( 'projects', $modelProject->getUserProjects( $userId ) );
		}

		$modelPassword	= new Model_User_Password( $this->env );
		$passwords		= $modelPassword->getAll( ['userId' => $userId] );
		$this->addData( 'passwords', $passwords );

		$this->addData( 'groups', $this->logic->getGroups( [], ['title' => 'ASC'] ) );
	}

	/**
	 *	@param		string		$userId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function password( string $userId ): void
	{
		$words			= (object) $this->getWords( 'editPassword' );
		$input			= $this->request->getAllFromSource( 'POST', TRUE );

		if( !$this->request->getMethod()->isPost() ){
			$this->messenger->noteError( 'Access denied' );
			$this->restart( 'edit/'.$userId, TRUE );
		}

		$user		= $this->logic->checkId( $userId );
		if( NULL === $user ){
			$this->messenger->noteError( 'Invalid user ID' );
			$this->restart( NULL, TRUE );
		}

		$passwordNew	= $input->get( 'passwordNew' );
		if( strlen( trim( $passwordNew ) ) === 0 ){
			$this->messenger->noteError( $words->msgPasswordNewMissing );
			$this->restart( 'edit/'.$userId, TRUE );
		}

		$passwordConfirm	= $input->get( 'passwordConfirm' );
		if( strlen( trim( $passwordConfirm ) ) === 0 ){
			$this->messenger->noteError( $words->msgPasswordNewMissing );
			$this->restart( 'edit/'.$userId, TRUE );
		}
		if( $passwordNew !== $passwordConfirm ){
			$this->messenger->noteError( $words->msgPasswordConfirmMismatch );
			$this->restart( 'edit/'.$userId, TRUE );
		}

		$logicPassword	= Logic_UserPassword::getInstance( $this->env );
		if( $logicPassword->validateUserPassword( $user, $passwordNew, FALSE ) ){
			$this->messenger->noteError( $words->msgPasswordNewSame );
			$this->restart( 'edit/'.$userId, TRUE );
		}

		$options		= $this->config->getAll( 'module.resource_users.', TRUE );
		$pwdMinLength	= $options->get( 'password.length.min' );
		if( $pwdMinLength > 0 && strlen( $passwordNew ) < $pwdMinLength ){
			$this->messenger->noteError( $words->msgPasswordNewTooShort );
			$this->restart( 'edit/'.$userId, TRUE );
		}

		// @todo implement strength check
/*		$pwdMinStrength	= $options->get( 'password.strength.min' );
		$pwdStrength	= todoDoTheMathHere();
		if( $pwdMinStrength > 0 && $pwdStrength < $pwdMinStrength ){
			$this->messenger->noteError( $words->msgPasswordTooWeak );
			$this->restart( 'edit/'.$userId, TRUE );
		}*/

		$userPassword	= $logicPassword->addPassword( $user, $passwordNew );
		$logicPassword->activatePassword( $userPassword );
		$this->messenger->noteSuccess( $words->msgSuccess, $user->username );
		$this->restart( 'edit/'.$userId, TRUE );
	}

	public function filter( $mode = NULL ): void
	{
		$session	= $this->env->getSession();
		switch( $mode )
		{
			case 'reset':
				foreach( $this->filters as $filter )
					$session->remove( 'filter-user-'.$filter );
				break;
			default:
				foreach( $this->filters as $filter )
				{
					$value	= $this->request->get( $filter );
					$session->remove( 'filter-user-'.$filter );
					if( strlen( $value ) )
						$session->set( 'filter-user-'.$filter, $value );
				}
		}
		$this->restart( NULL, TRUE );
	}

	public function index( $page = NULL ): void
	{
		$session	= $this->env->getSession();
		$limit		= abs( $session->get( 'filter-user-limit', 0 ) );
		$limit		= 0 !== $limit ?: 15;
		$page		= max( 0, (int) $page );

		if( !$this->env->getAcl()->has( 'manage/user' ) )
			$this->restart();

//		$limit		= !is_null( $limit ) ? $limit : $session->get( 'filter-user-limit' );	//  get limit from request or session
//		$limit		= ( (int) $limit <= 0 || (int) $limit > 1000 ) ? 10 : (int) $limit;		//  ensure that limit is within bounds
		$offset		= $page * $limit;						//  get offset from request or reset

		$filters	= [];																//  prepare filters map
		foreach( $session->getAll() as $key => $value ){									//  iterate session settings
			if( str_starts_with( $key, 'filter-user-' ) ){									//  if setting is users filter
				$column	= preg_replace( '/^filter-user-/', '', $key );						//  extract database module column
				if( !in_array( $column, ['order', 'direction', 'limit'] ) ){			// 	filter is within list of allowed filters
					if( $column === 'username' )											//  filter is username
						$value = preg_replace( "/\*/", "%", $value );						//  transform for SQL: * -> %
					$filters[$column] = $value;												//  enlist filter
				}
			}
		}
		$orders	= [];
		$order	= $session->get( 'filter-user-order' );
		$dir	= $session->get( 'filter-user-direction' );
		if( $order && $dir )
			$orders	= [$order => $dir];
/*		$data	= [
			'filters'	=> $filters,
			'orders'	=> $orders
		];*/

		$modelUser	= new Model_User( $this->env );
		$modelRole	= new Model_Role( $this->env );
		$roleMap	= [];
		foreach( $modelRole->getAll() as $role )
			$roleMap[$role->roleId]	= $role;

		$all		= $modelUser->count();
		$total		= $modelUser->count( $filters );
		$list		= $modelUser->getAll( $filters, $orders, [$offset, $limit] );

		$this->addData( 'username', $session->get( 'filter-user-username' ) );
		$this->addData( 'roles', $roleMap );
#		$this->addData( 'rooms', $server->getData( 'room', 'index' ) );
		$this->addData( 'all', $all );
		$this->addData( 'total', $total );
		$this->addData( 'users', $list );
		$this->addData( 'page', $page );
		$this->addData( 'limit', $limit );
		$this->addData( 'hasRightToAdd', $this->env->getAcl()->has( 'manage_user', 'add' ) );
		$this->addData( 'hasRightToEdit', $this->env->getAcl()->has( 'manage_user', 'edit' ) );
	}

/*	public function logout( $userId ) {
		$server		= $this->env->getServer();
		$user		= $server->getData( 'user', 'get', array( (int) $userId ) );
		$code		= $server->postData( 'auth', 'logout', array( (int) $userId ) );
		$this->handleErrorCode( $code, $user->username );
		$this->restart( './manage/user/edit/'.(int) $userId );
	}*/

	/**
	 *	@param		string		$userId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( string $userId ): void
	{
		$words		= (object) $this->getWords( 'remove' );
		$model		= new Model_User( $this->env );
		$user		= $model->get( $userId );
		if( !$user ){
			$this->messenger->noteError( $words->msgInvalidUserId );
			$this->restart( NULL, TRUE );
		}
		$model->remove( $userId );
		$this->messenger->noteSuccess( $words->msgSuccess, $user->username );
		$this->restart( NULL, TRUE );
	}

	protected function __onInit(): void
	{
		$this->config		= $this->env->getConfig();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_users.', TRUE );
		$this->countries	= $this->env->getLanguage()->getWords( 'countries' );
		$this->setData( [
			'nameMinLength'		=> $this->moduleConfig->get( 'name.length.min' ),
			'nameMaxLength'		=> $this->moduleConfig->get( 'name.length.max' ),
			'pwdMinLength'		=> $this->moduleConfig->get( 'password.length.min' ),
			'pwdMinStrength'	=> $this->moduleConfig->get( 'password.strength.min' ),
			'needsEmail'		=> $this->moduleConfig->get( 'email.mandatory' ),
			'needsFirstname'	=> $this->moduleConfig->get( 'firstname.mandatory' ),
			'needsSurname'		=> $this->moduleConfig->get( 'surname.mandatory' ),
			'needsTac'			=> $this->moduleConfig->get( 'tac.mandatory' ),
			'countries'			=> $this->countries,
		] );
		$this->logic	= new Logic_User( $this->env );
	}

	/**
	 *	@return		?Entity_User
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function handleAddAction(): ?Entity_User
	{
		$words		= (object) $this->getWords( 'add' );
		$input		= $this->request->getAllFromSource( 'POST', TRUE );
		$modelUser	= new Model_User( $this->env );

	//	$nameMinLength	= $this->moduleConfig->get( 'name.length.min' );
	//	$nameMaxLength	= $this->moduleConfig->get( 'name.length.max' );
		$nameRegExp		= $this->moduleConfig->get( 'name.preg' );
		$pwdMinLength	= $this->moduleConfig->get( 'password.length.min' );
		$needsEmail		= $this->moduleConfig->get( 'email.mandatory' );
		$needsFirstname	= $this->moduleConfig->get( 'firstname.mandatory' );
		$needsSurname	= $this->moduleConfig->get( 'surname.mandatory' );
	//	$needsTac		= $this->moduleConfig->get( 'tac.mandatory' );
		$passwordSalt	= trim( $this->moduleConfig->get( 'password.salt' ) );									//  string to salt password with

		$username		= $input->get( 'username' );
		$password		= $input->get( 'password' );
		$email			= strtolower( trim( $input->get( 'email' ) ) );

		if( empty( $username ) )																//  no username given
			$this->messenger->noteError( $words->msgNoUsername );
		else if( $modelUser->countByIndex( 'username', $username ) )							//  username is already used
			$this->messenger->noteError( $words->msgUsernameExisting, $username );
		else if( $nameRegExp )
			if( !Predicates::isPreg( $username, $nameRegExp ) )
				$this->messenger->noteError( $words->msgUsernameInvalid, $username, $nameRegExp );
		if( empty( $password ) )
			$this->messenger->noteError( $words->msgNoPassword );
		else if( $pwdMinLength && strlen( $password ) < $pwdMinLength )
			$this->messenger->noteError( $words->msgPasswordTooShort, $pwdMinLength );
		if( $needsEmail && empty( $email ) )
			$this->messenger->noteError( $words->msgNoEmail );
		else if( !empty( $email ) && $modelUser->countByIndex( 'email', $email ) )
			$this->messenger->noteError( $words->msgEmailExisting, $email );

		if( $needsFirstname && empty( $input['firstname'] ) )
			$this->messenger->noteError( $words->msgNoFirstname );
		if( $needsSurname && empty( $input['surname'] ) )
			$this->messenger->noteError( $words->msgNoSurname );

		if( $this->messenger->gotError() )
			return NULL;
		$data	= [
			'roleId'		=> $input['roleId'],
			'companyId'		=> (int) $input->get( 'companyId' ),
			'roomId'		=> 0,
			'status'		=> $input['status'],
			'username'		=> $username,
			'password'		=> md5( $passwordSalt.$password ),
			'email'			=> $email,
			'gender'		=> $input['gender'],
			'salutation'	=> $input['salutation'],
			'firstname'		=> $input['firstname'],
			'surname'		=> $input['surname'],
			'postcode'		=> $input['postcode'],
			'city'			=> $input['city'],
			'street'		=> $input['street'],
			'country'		=> $input['country'],
			'phone'			=> $input['phone'],
			'fax'			=> $input['fax'],
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		];
		if( strlen( $data['country'] ) > 2 ){
			$countries			= array_flip( $this->countries );
			$data['country']	= $countries[$data['country']];
		}
		if( class_exists( 'Logic_UserPassword' ) )											//  @todo  remove whole block if old user password support decays
			$data['password'] = '';

		$userId		= $modelUser->add( $data );
		/** @var Entity_User $user */
		$user		= $modelUser->get( $userId );
		if( class_exists( 'Logic_UserPassword' ) ){											//  @todo  remove line if old user password support decays
			$logic			= Logic_UserPassword::getInstance( $this->env );
			$userPassword	= $logic->addPassword( $user, $password );
			$logic->activatePassword( $userPassword );
		}
		$this->messenger->noteSuccess( $words->msgSuccess, $input['username'] );
		return $user;
	}

	/**
	 *	@param		Entity_User		$user
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function handleEditAction( Entity_User $user ): void
	{
		$words		= (object) $this->getWords( 'edit' );
		$input		= $this->request->getAllFromSource( 'POST', TRUE );
		$modelUser	= new Model_User( $this->env );

	//	$nameMinLength	= $this->moduleConfig->get( 'name.length.min' );
	//	$nameMaxLength	= $this->moduleConfig->get( 'name.length.max' );
	//	$nameRegExp		= $this->moduleConfig->get( 'name.preg' );
		$pwdMinLength	= $this->moduleConfig->get( 'password.length.min' );
	//	$pwdMinStrength	= $this->moduleConfig->get( 'password.strength.min' );
		$needsEmail		= $this->moduleConfig->get( 'email.mandatory' );
		$needsFirstname	= $this->moduleConfig->get( 'firstname.mandatory' );
		$needsSurname	= $this->moduleConfig->get( 'surname.mandatory' );
		//	$needsTac		= $options->get( 'tac.mandatory' );
		//	$status			= (int) $this->moduleConfig->get( 'status.register' );
		$passwordSalt	= trim( $this->moduleConfig->get( 'password.salt' ) );						//  string to salt password with

		$username	= $input->get( 'username' );
		$password	= $input->get( 'password' );
		$email		= strtolower( trim( $input->get( 'email', '' ) ) );

		if( empty( $username ) ){																//  no username given
			$this->messenger->noteError( $words->msgNoUsername );
			$this->restart( 'edit/'.$user->userId, TRUE );
		}
		if( $modelUser->countByIndex( 'username', $username ) ){
			$foundUser	= $modelUser->getByIndex( 'username', $username );
			if( $foundUser->userId != $user->userId ){													//  username is already used
				$this->messenger->noteError( $words->msgUsernameExisting, $username );
				$this->restart( 'edit/'.$user->userId, TRUE );
			}
		}
		if( !empty( $password ) && $pwdMinLength && strlen( $password ) < $pwdMinLength ){
			$this->messenger->noteError( $words->msgPasswordTooShort );
			$this->restart( 'edit/'.$user->userId, TRUE );
		}
		if( $needsEmail && empty( $email ) ){
			$this->messenger->noteError( $words->msgNoEmail );
			$this->restart( 'edit/'.$user->userId, TRUE );
		}
		if( !empty( $email ) ){
			/** @var Entity_User $foundUser */
			$foundUser	= $modelUser->getByIndex( 'email', $email );
			if( $foundUser && $foundUser->userId != $user->userId ){
				$this->messenger->noteError( $words->msgEmailExisting, $email );
				$this->restart( 'edit/'.$user->userId, TRUE );
			}
		}
		if( $needsFirstname && empty( $input['firstname'] ) ){
			$this->messenger->noteError( $words->msgNoFirstname );
			$this->restart( 'edit/'.$user->userId, TRUE );
		}
		if( $needsSurname && empty( $input['surname'] ) ){
			$this->messenger->noteError( $words->msgNoSurname );
			$this->restart( 'edit/'.$user->userId, TRUE );
		}

		$data	= [
			'roleId'		=> $input['roleId'],
//				'status'		=> $input['status'],
			'username'		=> $username,
			'email'			=> strtolower( $email ),
			'gender'		=> $input['gender'],
			'salutation'	=> $input['salutation'],
			'firstname'		=> $input['firstname'],
			'surname'		=> $input['surname'],
			'country'		=> $input['country'],
			'postcode'		=> $input['postcode'],
			'city'			=> $input['city'],
			'street'		=> $input['street'],
			'phone'			=> $input['phone'],
			'fax'			=> $input['fax'],
			'modifiedAt'	=> time(),
		];
		if( !empty( $password ) ){
			$data['password']	= md5( $passwordSalt.$password );

			if( class_exists( 'Logic_UserPassword' ) ){										//  @todo  remove whole block if old user password support decays
				unset( $data['password'] );
			}
			if( class_exists( 'Logic_UserPassword' ) ){										//  @todo  remove line if old user password support decays
				$logic			= Logic_UserPassword::getInstance( $this->env );
				$userPassword	= $logic->addPassword( $user, $password );
				$logic->activatePassword( $userPassword );
			}
		}
		if( strlen( $data['country'] ) > 2 ){
			$countries			= array_flip( $this->countries );
			$data['country']	= $countries[$data['country']];
		}
		$modelUser->edit( $user->userId, $data );
		$this->messenger->noteSuccess( $words->msgSuccess, $input['username'] );
		$this->restart( 'edit/'.$user->userId, TRUE );
	}

	/**
	 *	@param		string		$userId
	 *	@param		int			$status
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function setStatus( string $userId, int $status ): void
	{
		$model		= new Model_User( $this->env );
		$user		= $model->get( $userId );
		if( !$user )
			throw new DomainException( 'Invalid user ID' );
		if( !in_array( $status, Model_User::STATUSES, TRUE ) )
			throw new RangeException( 'Invalid status' );
		if( !in_array( $status, Model_User::STATUS_TRANSITIONS[(int) $user->status], TRUE ) )
			throw new RangeException( 'Invalid status transition' );
		$model->edit( $userId, ['status' => $status, 'modifiedAt' => time()] );
/*		$server		= $this->env->getServer();
		$user		= $server->getData( 'user', 'get', array( (int) $userId ) );
		$code		= $server->postData( 'user', 'setStatus', array( (int) $userId, $status ) );
		$this->handleErrorCode( $code, $user->username );
*/		$this->restart( 'edit/'.$userId, TRUE );
	}
}
