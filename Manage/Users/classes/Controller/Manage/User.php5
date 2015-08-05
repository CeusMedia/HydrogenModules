<?php
/**
 *	User Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.Controller.Manage
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	User Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Manage_Users.Controller.Manage
 *	@extends		CMF_Hydrogen_Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
class Controller_Manage_User extends CMF_Hydrogen_Controller {

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

	public function __onInit(){
		$options		= $this->env->getConfig()->getAll( 'module.resource_users.', TRUE );
		$this->setData( array(
			'nameMinLength'		=> $options->get( 'name.length.min' ),
			'nameMaxLength'		=> $options->get( 'name.length.max' ),
			'pwdMinLength'		=> $options->get( 'password.length.min' ),
			'needsEmail'		=> $options->get( 'email.mandatory' ),
			'needsFirstname'	=> $options->get( 'firstname.mandatory' ),
			'needsSurname'		=> $options->get( 'surname.mandatory' ),
			'needsTac'			=> $options->get( 'tac.mandatory' ),
		) );
	}

	public function accept( $userId ) {
		$this->setStatus( $userId, 1 );
	}

	public function add() {
		$config		= $this->env->getConfig();
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'add' );
		$input		= $request->getAllFromSource( 'POST' );
		$modelUser	= new Model_User( $this->env );
		$modelRole	= new Model_Role( $this->env );

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
		$email			= $input->get( 'email' );

		if( $request->getMethod() == 'POST' ){
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
				$userId		= $modelUser->add( array(
					'roleId'		=> $input['roleId'],
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
					'number'		=> $input['number'],
					'phone'			=> $input['phone'],
					'fax'			=> $input['fax'],
					'createdAt'		=> time(),
				) );
				$messenger->noteSuccess( $words->msgSuccess, $input['username'] );
				$this->restart( './manage/user' );
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

	public function ban( $userId ) {
		$this->setStatus( $userId, -1 );
	}

	public function disable( $userId ) {
		$this->setStatus( $userId, -2 );
	}

	public function edit( $userId ) {
		$config		= $this->env->getConfig();
		$request	= $this->env->getRequest();
		$messenger	= $this->env->getMessenger();
		$words		= (object) $this->getWords( 'edit' );
		$input		= $request->getAllFromSource( 'POST' );
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
		$needsEmail		= $options->get( 'email.mandatory' );
		$needsFirstname	= $options->get( 'firstname.mandatory' );
		$needsSurname	= $options->get( 'surname.mandatory' );
		$needsTac		= $options->get( 'tac.mandatory' );
		$status			= (int) $options->get( 'status.register' );
		$passwordSalt	= trim( $options->get( 'password.salt' ) );						//  string to salt password with

		$username	= $input->get( 'username' );
		$password	= $input->get( 'password' );
		$email		= $input->get( 'email' );

		if( $request->getMethod() == 'POST' ){
			if( empty( $username ) )																//  no username given
				$messenger->noteError( $words->msgNoUsername );
			else if( $modelUser->countByIndex( 'username', $username ) )
				if( $modelUser->getByIndex( 'username', $username, 'userId' ) != $userId )			//  username is already used
				$messenger->noteError( $words->msgUsernameExisting, $username );
			if( !empty( $password ) && $pwdMinLength && strlen( $password ) < $pwdMinLength )
				$messenger->noteError( $words->msgPasswordTooShort );

			if( $needsEmail && empty( $email ) )
				$messenger->noteError( $words->msgNoEmail );
			else if( !empty( $email ) )
				if( $modelUser->getByIndices( array( 'email' => $email, 'userId' => '!='.$userId ) ) )
					$messenger->noteError( $words->msgEmailExisting, $email );

			if( $needsFirstname && empty( $input['firstname'] ) )
				$messenger->noteError( $words->msgNoFirstname );
			if( $needsSurname && empty( $input['surname'] ) )
				$messenger->noteError( $words->msgNoSurname );

			if( !$messenger->gotError() ){
				$data	= array(
					'roleId'		=> $input['roleId'],
					'status'		=> $input['status'],
					'username'		=> $username,
					'email'			=> $email,
					'gender'		=> $input['gender'],
					'salutation'	=> $input['salutation'],
					'firstname'		=> $input['firstname'],
					'surname'		=> $input['surname'],
					'postcode'		=> $input['postcode'],
					'city'			=> $input['city'],
					'street'		=> $input['street'],
					'number'		=> $input['number'],
					'phone'			=> $input['phone'],
					'fax'			=> $input['fax'],
					'modifiedAt'	=> time(),
				);
				if( !empty( $password ) )
					$data['password']	= md5( $passwordSalt.$password );
				$modelUser->edit( $userId, $data );
				$messenger->noteSuccess( $words->msgSuccess, $input['username'] );
			}
		}
		$user		= $modelUser->get( $userId );
		$user->role	= $modelRole->get( $user->roleId );

		$config		= $this->env->getConfig();
		$this->addData( 'userId', (int) $userId );
		$this->addData( 'user', $user );
		$this->addData( 'roles', $modelRole->getAll() );
		$this->addData( 'pwdMinLength', (int) $config->get( 'user.password.length.min' ) );
		$this->addData( 'pwdMinStrength', (int) $config->get( 'user.password.strength.min' ) );

		if( $this->env->getModules()->has( 'Manage_Projects' ) ){
			$modelProject	= new Model_Project( $this->env );
			$this->addData( 'projects', $modelProject->getUserProjects( $userId ) );
		}
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

	public function index( $page = NULL ) {
		$session	= $this->env->getSession();
		$limit		= abs( $session->get( 'filter-user-limit' ) );
		$limit		= $limit ? $limit : 15;
		$page		= max( 0, (int) $page );

		if( !$this->env->getAcl()->has( 'manage/user', 'index' ) )
			$this->restart();

//		$limit		= !is_null( $limit ) ? $limit : $session->get( 'filter-user-limit' );	//  get limit from request or session
//		$limit		= ( (int) $limit <= 0 || (int) $limit > 1000 ) ? 10 : (int) $limit;		//  ensure that limit is within bounds
		$offset		= !is_null( $page ) ? abs( $page * $limit ) : 0;						//  get offset from request or reset

		$filters	= array();																//  prepare filters map
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
		$orders	= array();
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
		$roleMap	= array();
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
	}

	public function logout( $userId ) {
		$server		= $this->env->getServer();
		$user		= $server->getdata( 'user', 'get', array( (int) $userId ) );
		$code		= $server->postData( 'auth', 'logout', array( (int) $userId ) );
		$this->handleErrorCode( $code, $user->username );
		$this->restart( './user/edit/'.(int) $userId );
	}

	protected function setStatus( $userId, $status ) {
		$server		= $this->env->getServer();
		$user		= $server->getData( 'user', 'get', array( (int) $userId ) );
		$code		= $server->postData( 'user', 'setStatus', array( (int) $userId, $status ) );
		$this->handleErrorCode( $code, $user->username );
		$this->restart( './user/edit/'.(int) $userId );
	}

	public function remove( $userId ){
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
}
?>
