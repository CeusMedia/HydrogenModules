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
		$words		= $this->getWords( 'add' );
		$input		= $request->getAllFromSource( 'POST' );
		$modelUser	= new Model_User( $this->env );
		$modelRole	= new Model_Role( $this->env );

		if( $request->getMethod() == 'POST' ){
			if( empty( $input['username'] ) )
				$messenger->noteError( $words->msgNoUsername );
			if( empty( $input['password'] ) )
				$messenger->noteError( $words->msgNoPassword );
			if( $config->get( 'module.users.email.mandatory') && empty( $input['email'] ) )
				$messenger->noteError( $words->msgNoEmail );
			if( !$messenger->gotError() ){
				$userId		= $modelUser->add( array(
					'roleId'	=> $input['roleId'],
					'status'	=> $input['status'],
					'username'	=> $input['username'],
					'password'	=> md5( $input['password'] ),
					'email'		=> $input['email'],
					'firstname'	=> $input['firstname'],
					'surname'	=> $input['surname'],
					'createdAt'	=> time(),
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
		$words		= $this->getWords( 'edit' );
		$input		= $request->getAllFromSource( 'POST' );
		$modelUser	= new Model_User( $this->env );
		$modelRole	= new Model_Role( $this->env );

		if( $request->getMethod() == 'POST' ){
			if( empty( $input['username'] ) )
				$messenger->noteError( $words->msgNoUsername );
			if( empty( $input['password'] ) )
				$messenger->noteError( $words->msgNoPassword );
			if( $config->get( 'module.users.email.mandatory') && empty( $input['email'] ) )
				$messenger->noteError( $words->msgNoEmail );
			if( !$messenger->gotError() ){
				$userId		= $modelUser->edit( $userId, array(
					'roleId'		=> $input['roleId'],
					'status'		=> $input['status'],
					'username'		=> $input['username'],
					'password'		=> md5( $input['password'] ),
					'email'			=> $input['email'],
					'firstname'		=> $input['firstname'],
					'surname'		=> $input['surname'],
					'modifiedAt'	=> time(),
				) );
				$messenger->noteSuccess( $words->msgSuccess, $input['username'] );
			}
			
			$user		= $modelUser->get( $userId );
			$data		= $this->env->request->getAllFromSource( 'POST' )->getAll();
			$data['password']	= md5( $data['password'] );
			$data['modifiedAt']	= time();
			$result		= $modelUser->edit( $userId, $data );
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
		$this->redirect( 'user' );
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
		$words		= $this->getWords( 'remove' );
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