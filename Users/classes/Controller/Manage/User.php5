<?php
/**
 *	User Controller.
 *	@category		cmApps
 *	@package		Chat.Admin.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: User.php 1765 2010-11-28 08:05:15Z christian.wuerker $
 */
/**
 *	User Controller.
 *	@category		cmApps
 *	@package		Chat.Admin.Controller
 *	@extends		CMF_Hydrogen_Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: User.php 1765 2010-11-28 08:05:15Z christian.wuerker $
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

	public function accept( $userId ) {
		$this->setStatus( $userId, 1 );
	}

	public function add() {
		$words		= $this->env->getLanguage()->getWords( 'manage/user' );
		$request	= $this->env->getRequest();
		if( $request->getMethod() == 'POST' ){
			$data		= $request->getAllFromSource( 'POST' );
			$data['password']	= md5( $data['password'] );
			$data['created']	= time();
			$modelUser	= new Model_User( $this->env );
			$modelUser->add( $data );
			$this->restart( './manage/user' );
		}
		$modelRole	= new Model_Role( $this->env );
		;

		$config		= $this->env->getConfig();
		$this->addData( 'username', $request->get( 'username' ),'data' );
		$this->addData( 'email', $request->get( 'email' ),'data' );
		$this->addData( 'status', $request->get( 'status' ),'data' );
		$this->addData( 'roleId', $request->get( 'roleId' ),'data' );
		$this->addData( 'roles', $modelRole->getAll() );
		$this->addData( 'words', $words );
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
		$words		= $this->env->getLanguage()->getWords( 'manage/user' );
		$modelUser	= new Model_User( $this->env );
		$modelRole	= new Model_Role( $this->env );

		if( $this->env->getRequest()->getMethod() == 'POST' )
		{
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
		$this->addData( 'words', $words );
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
}
?>