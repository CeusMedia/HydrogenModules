<?php
/**
 *	User Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Controller.Admin
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	User Controller.
 *	@category		cmFrameworks.Hydrogen.Module
 *	@package		Users.Controller.Admin
 *	@extends		CMF_Hydrogen_Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2012 Ceus Media
 *	@version		$Id$
 */
class Controller_Admin_User extends CMF_Hydrogen_Controller {

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

		$nameMinLength	= $config->get( 'module.users.name.length.min' );
		$nameMaxLength	= $config->get( 'module.users.name.length.max' );
		$nameRegExp		= $config->get( 'module.users.name.preg' );
		$pwdMinLength	= $config->get( 'module.users.password.length.min' );
		$needsEmail		= $config->get( 'module.users.email.mandatory' );
		$needsFirstname	= $config->get( 'module.users.firstname.mandatory' );
		$needsSurname	= $config->get( 'module.users.surname.mandatory' );
		$needsTac		= $config->get( 'module.users.tac.mandatory' );
		$passwordSalt	= trim( $config->get( 'module.users.password.salt' ) );						//  string to salt password with

		$username	= $input->get( 'username' );
		$password	= $input->get( 'password' );
		$email		= $input->get( 'email' );
	
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

			if( $needsFirstname && empty( $data['firstname'] ) )
				$messenger->noteError( $words->msgNoFirstname );
			if( $needsSurname && empty( $data['surname'] ) )
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
				$this->restart( './admin/user' );
			}
		}
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

		$nameMinLength	= $config->get( 'module.users.name.length.min' );
		$nameMaxLength	= $config->get( 'module.users.name.length.max' );
		$nameRegExp		= $config->get( 'module.users.name.preg' );
		$pwdMinLength	= $config->get( 'module.users.password.length.min' );
		$needsEmail		= $config->get( 'module.users.email.mandatory' );
		$needsFirstname	= $config->get( 'module.users.firstname.mandatory' );
		$needsSurname	= $config->get( 'module.users.surname.mandatory' );
		$needsTac		= $config->get( 'module.users.tac.mandatory' );
		$status			= (int) $config->get( 'module.users.status.register' );
		$passwordSalt	= trim( $config->get( 'module.users.password.salt' ) );						//  string to salt password with

		$username	= $input->get( 'username' );
		$password	= $input->get( 'password' );
		$email		= $input->get( 'email' );
		
		if( $request->getMethod() == 'POST' ){
			if( empty( $username ) )																//  no username given
				$messenger->noteError( $words->msgNoUsername );
			else if( $modelUser->getByIndex( 'username', $username, 'userId' ) != $userId )			//  username is already used
				$messenger->noteError( $words->msgUsernameExisting, $username );
			if( !empty( $password ) && $pwdMinLength && strlen( $password ) < $pwdMinLength )
				$messenger->noteError( $words->msgPasswordTooShort );
			if( $config->get( 'module.users.email.mandatory') && empty( $input['email'] ) )
				$messenger->noteError( $words->msgNoEmail );

			if( $needsEmail && empty( $email ) )
				$messenger->noteError( $words->msgNoEmail );
			else if( !empty( $email ) )
				if( $modelUser->getByIndex( 'email', $email, 'userId' ) != $userId )
					$messenger->noteError( $words->msgEmailExisting, $email );
			
			if( $needsFirstname && empty( $data['firstname'] ) )
				$messenger->noteError( $words->msgNoFirstname );
			if( $needsSurname && empty( $data['surname'] ) )
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

	public function index( $limit = NULL, $offset = NULL ) {
		$session	= $this->env->getSession();

		if( !$limit )
			$limit		= $session->get( 'filter-user-limit' );
		if( !$limit )
			$limit		= 10;
		$offset		= is_null( $offset ) ? 0 : abs( $offset );
		$limits		= array( $limit, $offset );

		$filters	= array();
		foreach( $session->getAll() as $key => $value )
			if( preg_match( '/^filter-user-/', $key ) ){
				$column	= preg_replace( '/^filter-user-/', '', $key );
				if( !in_array( $column, array( 'order', 'direction', 'limit' ) ) )
					$filters[$column] = $value;
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
		$all		= $modelUser->count();
		$total		= $modelUser->count( $filters );
		$list		= $modelUser->getAll( $filters, $orders, $limits );
		$this->addData( 'username', $session->get( 'filter-user-username' ) );
		$this->addData( 'roles', $modelRole->getAll() );
#		$this->addData( 'rooms', $server->getData( 'room', 'index' ) );
		$this->addData( 'all', $all );
		$this->addData( 'total', $total );
		$this->addData( 'users', $list );
		$this->addData( 'offset', $offset );
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
		$messenger->noteError( $words->msgSuccess, $user->username );
		$this->restart( NULL, TRUE );
	}
}
?>