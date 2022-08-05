<?php
/**
 *	User Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.Controller.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 */

use CeusMedia\HydrogenFramework\Controller;

/**
 *	User Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.Controller.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 */
class Controller_Manage_User extends Controller
{
	public static $moduleId		= 'Manage_Users';

	protected $countries;

	protected $filters	= array(
		'username',
		'roomId',
		'roleId',
		'status',
		'roleId',
		'activity',
		'order',
		'direction',
		'limit'
	);

	public function accept( $userId )
	{
		$this->setStatus( $userId, Model_User::STATUS_ACTIVE );
	}

	public function add()
	{
		$config		= $this->env->getConfig();
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'add' );
		$input		= $request->getAllFromSource( 'POST', TRUE );
		$modelUser	= $this->getModel( 'User' );
		$modelRole	= $this->getModel( 'Role' );

		$options		= $this->env->getConfig()->getAll( 'module.resource_users.', TRUE );
		$nameMinLength	= $options->get( 'name.length.min' );
		$nameMaxLength	= $options->get( 'name.length.max' );
		$nameRegExp		= $options->get( 'name.preg' );
		$pwdMinLength	= $options->get( 'password.length.min' );
		$needsEmail		= $options->get( 'email.mandatory' );
		$needsFirstname	= $options->get( 'firstname.mandatory' );
		$needsSurname	= $options->get( 'surname.mandatory' );
		$needsTac		= $options->get( 'tac.mandatory' );
		$passwordSalt	= trim( $options->get( 'password.salt' ) );									//  string to salt password with

		$username		= $input->get( 'username' );
		$password		= $input->get( 'password' );
		$email			= strtolower( trim( $input->get( 'email' ) ) );

		if( $request->getMethod()->isPost() ){
			if( empty( $username ) )																//  no username given
				$messenger->noteError( $words->msgNoUsername );
			else if( $modelUser->countByIndex( 'username', $username ) )							//  username is already used
				$messenger->noteError( $words->msgUsernameExisting, $username );
			else if( $nameRegExp )
				if( !Alg_Validation_Predicates::isPreg( $username, $nameRegExp ) )
					$messenger->noteError( $words->msgUsernameInvalid, $username, $nameRegExp );
			if( empty( $password ) )
				$messenger->noteError( $words->msgNoPassword );
			else if( $pwdMinLength && strlen( $password ) < $pwdMinLength )
				$messenger->noteError( $words->msgPasswordTooShort, $pwdMinLength );
			if( $needsEmail && empty( $email ) )
				$messenger->noteError( $words->msgNoEmail );
			else if( !empty( $email ) && $modelUser->countByIndex( 'email', $email ) )
				$messenger->noteError( $words->msgEmailExisting, $email );

			if( $needsFirstname && empty( $input['firstname'] ) )
				$messenger->noteError( $words->msgNoFirstname );
			if( $needsSurname && empty( $input['surname'] ) )
				$messenger->noteError( $words->msgNoSurname );

			if( !$messenger->gotError() ){
				$data	= array(
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
				);
				if( strlen( $data['country'] ) > 2 ){
					$countries			= array_flip( $this->countries );
					$data['country']	= $countries[$data['country']];
				}
				if( class_exists( 'Logic_UserPassword' ) ){											//  @todo  remove whole block if old user password support decays
					$data['password'] = '';
				}
				$userId		= $modelUser->add( $data );
				if( class_exists( 'Logic_UserPassword' ) ){											//  @todo  remove line if old user password support decays
					$logic			= Logic_UserPassword::getInstance( $this->env );
					$userPasswordId	= $logic->addPassword( $userId, $password );
					$logic->activatePassword( $userPasswordId );
				}
				$messenger->noteSuccess( $words->msgSuccess, $input['username'] );
				$this->restart( NULL, TRUE );
			}
		}
		$input		= $this->env->getRequest();														//  allow preset data via GET parameters
		$user		= (object) array();
		$columns	= $modelUser->getColumns();
		foreach( $columns as $column )
			$user->$column	= htmlentities( $input[$column], ENT_COMPAT, 'UTF-8' );

		$config		= $this->env->getConfig();
		$this->addData( 'user', $user );
		$this->addData( 'roles', $modelRole->getAll() );
		$this->addData( 'pwdMinLength', (int) $config->get( 'user.password.length.min' ) );
		$this->addData( 'pwdMinStrength', (int) $config->get( 'user.password.strength.min' ) );
	}

	public function ban( $userId )
	{
		$this->setStatus( $userId, Model_User::STATUS_BANNED );
	}

	public function disable( $userId )
	{
		$this->setStatus( $userId, Model_User::STATUS_DISABLED );
	}

	public function edit( $userId )
	{
/*		$acl		= $this->env->getAcl();
		$modules	= $this->env->getModules();
		$canEdit	= $acl->has( 'manage/user', 'edit' );
		if( !$canEdit && $modules->has( 'Members' ) )
			$this->restart( './member/view/'.$userId );*/

		$config		= $this->env->getConfig();
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'edit' );
		$input		= $request->getAllFromSource( 'POST', TRUE );
		$modelUser	= new Model_User( $this->env );
		$modelRole	= new Model_Role( $this->env );

		if( !$modelUser->get( $userId ) ){
			$messenger->noteError( 'Invalid user ID' );
			$this->restart( NULL, TRUE );
		}

		$options		= $this->env->getConfig()->getAll( 'module.resource_users.', TRUE );
		$nameMinLength	= $options->get( 'name.length.min' );
		$nameMaxLength	= $options->get( 'name.length.max' );
		$nameRegExp		= $options->get( 'name.preg' );
		$pwdMinLength	= $options->get( 'password.length.min' );
		$pwdMinStrength	= $options->get( 'password.strength.min' );
		$needsEmail		= $options->get( 'email.mandatory' );
		$needsFirstname	= $options->get( 'firstname.mandatory' );
		$needsSurname	= $options->get( 'surname.mandatory' );
		$needsTac		= $options->get( 'tac.mandatory' );
		$status			= (int) $options->get( 'status.register' );
		$passwordSalt	= trim( $options->get( 'password.salt' ) );						//  string to salt password with

		$username	= $input->get( 'username' );
		$password	= $input->get( 'password' );
		$email		= strtolower( trim( $input->get( 'email' ) ) );

		if( $request->getMethod()->isPost() ){
			if( empty( $username ) ){																//  no username given
				$messenger->noteError( $words->msgNoUsername );
				$this->restart( 'edit/'.$userId, TRUE );
			}
			if( $modelUser->countByIndex( 'username', $username ) ){
				$foundUser	= $modelUser->getByIndex( 'username', $username );
				if( $foundUser->userId != $userId ){													//  username is already used
					$messenger->noteError( $words->msgUsernameExisting, $username );
					$this->restart( 'edit/'.$userId, TRUE );
				}
			}
			if( !empty( $password ) && $pwdMinLength && strlen( $password ) < $pwdMinLength ){
				$messenger->noteError( $words->msgPasswordTooShort );
				$this->restart( 'edit/'.$userId, TRUE );
			}
			if( $needsEmail && empty( $email ) ){
				$messenger->noteError( $words->msgNoEmail );
				$this->restart( 'edit/'.$userId, TRUE );
			}
			if( !empty( $email ) ){
				$foundUser	= $modelUser->getByIndex( 'email', $email );
				if( $foundUser && $foundUser->userId != $userId ){
					$messenger->noteError( $words->msgEmailExisting, $email );
					$this->restart( 'edit/'.$userId, TRUE );
				}
			}
			if( $needsFirstname && empty( $input['firstname'] ) ){
				$messenger->noteError( $words->msgNoFirstname );
				$this->restart( 'edit/'.$userId, TRUE );
			}
			if( $needsSurname && empty( $input['surname'] ) ){
				$messenger->noteError( $words->msgNoSurname );
				$this->restart( 'edit/'.$userId, TRUE );
			}

			$data	= array(
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
			);
			if( !empty( $password ) ){
				$data['password']	= md5( $passwordSalt.$password );

				if( class_exists( 'Logic_UserPassword' ) ){										//  @todo  remove whole block if old user password support decays
					unset( $data['password'] );
				}
				if( class_exists( 'Logic_UserPassword' ) ){										//  @todo  remove line if old user password support decays
					$logic			= Logic_UserPassword::getInstance( $this->env );
					$userPasswordId	= $logic->addPassword( $userId, $password );
					$logic->activatePassword( $userPasswordId );
				}
			}
			if( strlen( $data['country'] ) > 2 ){
				$countries			= array_flip( $this->countries );
				$data['country']	= $countries[$data['country']];
			}
			$modelUser->edit( $userId, $data );
			$messenger->noteSuccess( $words->msgSuccess, $input['username'] );
			$this->restart( 'edit/'.$userId, TRUE );
		}
		$user			= $modelUser->get( $userId );
		if( empty( $user->country ) )
			$user->country	= strtoupper( $this->env->getLanguage()->getLanguage() );
		$user->country	= $this->countries[$user->country];
		$user->role		= $modelRole->get( $user->roleId );

		$config		= $this->env->getConfig();
		$this->addData( 'userId', (int) $userId );
		$this->addData( 'user', $user );
		$this->addData( 'from', $request->get( 'from' ) );
		$this->addData( 'roles', $modelRole->getAll() );
		$this->addData( 'pwdMinLength', $pwdMinLength );
		$this->addData( 'pwdMinStrength', $pwdMinStrength );

		if( $this->env->getModules()->has( 'Manage_Projects' ) ){
			$modelProject	= new Model_Project( $this->env );
			$this->addData( 'projects', $modelProject->getUserProjects( $userId ) );
		}

		$modelPassword	= new Model_User_Password( $this->env );
		$passwords		= $modelPassword->getAll( array( 'userId' => $userId ) );
		$this->addData( 'passwords', $passwords );
	}

	public function password( $userId )
	{
		$config			= $this->env->getConfig();
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= (object) $this->getWords( 'editPassword' );
		$input			= $request->getAllFromSource( 'POST', TRUE );
		$modelUser		= new Model_User( $this->env );
		$modelRole		= new Model_Role( $this->env );

		if( !$request->getMethod()->isPost() ){
			$messenger->noteError( 'Access denied' );
			$this->restart( 'edit/'.$userId, TRUE );
		}

		$user		= $modelUser->get( $userId );
		if( !$user ){
			$messenger->noteError( 'Invalid user ID' );
			$this->restart( NULL, TRUE );
		}

		$passwordNew	= $input->get( 'passwordNew' );
		if( strlen( trim( $passwordNew ) ) === 0 ){
			$messenger->noteError( $words->msgPasswordNewMissing );
			$this->restart( 'edit/'.$userId, TRUE );
		}

		$passwordConfirm	= $input->get( 'passwordConfirm' );
		if( strlen( trim( $passwordConfirm ) ) === 0 ){
			$messenger->noteError( $words->msgPasswordNewMissing );
			$this->restart( 'edit/'.$userId, TRUE );
		}
		if( $passwordNew !== $passwordConfirm ){
			$messenger->noteError( $words->msgPasswordConfirmMismatch );
			$this->restart( 'edit/'.$userId, TRUE );
		}

		$logicPassword	= Logic_UserPassword::getInstance( $this->env );
		if( $logicPassword->validateUserPassword( $userId, $passwordNew, FALSE ) ){
			$messenger->noteError( $words->msgPasswordNewSame );
			$this->restart( 'edit/'.$userId, TRUE );
		}

		$options		= $config->getAll( 'module.resource_users.', TRUE );
		$pwdMinLength	= $options->get( 'password.length.min' );
		if( $pwdMinLength > 0 && strlen( $passwordNew ) < $pwdMinLength ){
			$messenger->noteError( $words->msgPasswordNewTooShort );
			$this->restart( 'edit/'.$userId, TRUE );
		}

		// @todo implement strength check
/*		$pwdMinStrength	= $options->get( 'password.strength.min' );
		$pwdStrength	= todoDoTheMathHere();
		if( $pwdMinStrength > 0 && $pwdStrength < $pwdMinStrength ){
			$messenger->noteError( $words->msgPasswordTooWeak );
			$this->restart( 'edit/'.$userId, TRUE );
		}*/

		$userPasswordId	= $logicPassword->addPassword( $userId, $passwordNew );
		$logicPassword->activatePassword( $userPasswordId );
		$messenger->noteSuccess( $words->msgSuccess, $user->username );
		$this->restart( 'edit/'.$userId, TRUE );
	}

	public function filter( $mode = NULL )
	{
		$session	= $this->env->getSession();
		switch( $mode )
		{
			case 'reset':
				foreach( $this->filters as $filter )
					$session->remove( 'filter-user-'.$filter );
				break;
			default:
				$request	= $this->env->getRequest();
				foreach( $this->filters as $filter )
				{
					$value	= $request->get( $filter );
					$session->remove( 'filter-user-'.$filter );
					if( strlen( $value ) )
						$session->set( 'filter-user-'.$filter, $value );
				}
		}
		$this->restart( NULL, TRUE );
	}

	public function index( $page = NULL )
	{
		$session	= $this->env->getSession();
		$limit		= abs( $session->get( 'filter-user-limit' ) );
		$limit		= $limit ? $limit : 15;
		$page		= max( 0, (int) $page );

		if( !$this->env->getAcl()->has( 'manage/user', 'index' ) )
			$this->restart();

//		$limit		= !is_null( $limit ) ? $limit : $session->get( 'filter-user-limit' );	//  get limit from request or session
//		$limit		= ( (int) $limit <= 0 || (int) $limit > 1000 ) ? 10 : (int) $limit;		//  ensure that limit is within bounds
		$offset		= !is_null( $page ) ? abs( $page * $limit ) : 0;						//  get offset from request or reset

		$filters	= [];																//  prepare filters map
		foreach( $session->getAll() as $key => $value ){									//  iterate session settings
			if( preg_match( '/^filter-user-/', $key ) ){									//  if setting is users filter
				$column	= preg_replace( '/^filter-user-/', '', $key );						//  extract database module column
				if( !in_array( $column, array( 'order', 'direction', 'limit' ) ) ){			// 	filter is within list of allowed filters
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
			$orders	= array( $order => $dir );
		$data	= array(
			'filters'	=> $filters,
			'orders'	=> $orders
		);

		$modelUser	= new Model_User( $this->env );
		$modelRole	= new Model_Role( $this->env );
		$roleMap	= [];
		foreach( $modelRole->getAll() as $role )
			$roleMap[$role->roleId]	= $role;

		$all		= $modelUser->count();
		$total		= $modelUser->count( $filters );
		$list		= $modelUser->getAll( $filters, $orders, array( $offset, $limit ) );

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
		$user		= $server->getdata( 'user', 'get', array( (int) $userId ) );
		$code		= $server->postData( 'auth', 'logout', array( (int) $userId ) );
		$this->handleErrorCode( $code, $user->username );
		$this->restart( './manage/user/edit/'.(int) $userId );
	}*/

	protected function setStatus( int $userId, int $status )
	{
		$model		= new Model_User( $this->env );
		$user		= $model->get( $userId );
		if( !$user )
			throw new DomainException( 'Invalid user ID' );
		if( !in_array( (int) $status, Model_User::STATUSES, TRUE ) )
			throw new RangeException( 'Invalid status' );
		if( !in_array( (int) $status, Model_User::STATUS_TRANSITIONS[(int) $user->status], TRUE ) )
			throw new RangeException( 'Invalid status transition' );
		$model->edit( $userId, array( 'status' => $status, 'modifiedAt' => time() ) );
/*		$server		= $this->env->getServer();
		$user		= $server->getData( 'user', 'get', array( (int) $userId ) );
		$code		= $server->postData( 'user', 'setStatus', array( (int) $userId, $status ) );
		$this->handleErrorCode( $code, $user->username );
*/		$this->restart( 'edit/'.(int) $userId, TRUE );
	}

	public function remove( int $userId )
	{
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'remove' );
		$model		= new Model_User( $this->env );
		$user		= $model->get( $userId );
		if( !$user ){
			$messenger->noteError( $words->msgInvalidUserId );
			$this->restart( NULL, TRUE );
		}
		$model->remove( $userId );
		$messenger->noteSuccess( $words->msgSuccess, $user->username );
		$this->restart( NULL, TRUE );
	}

	protected function __onInit()
	{
		$options			= $this->env->getConfig()->getAll( 'module.resource_users.', TRUE );
		$this->countries	= $this->env->getLanguage()->getWords( 'countries' );
		$this->setData( array(
			'nameMinLength'		=> $options->get( 'name.length.min' ),
			'nameMaxLength'		=> $options->get( 'name.length.max' ),
			'pwdMinLength'		=> $options->get( 'password.length.min' ),
			'pwdMinStrength'	=> $options->get( 'password.strength.min' ),
			'needsEmail'		=> $options->get( 'email.mandatory' ),
			'needsFirstname'	=> $options->get( 'firstname.mandatory' ),
			'needsSurname'		=> $options->get( 'surname.mandatory' ),
			'needsTac'			=> $options->get( 'tac.mandatory' ),
			'countries'			=> $this->countries,
		) );
	}
}
