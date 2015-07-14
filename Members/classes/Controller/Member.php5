<?php
class Controller_Member extends CMF_Hydrogen_Controller{

	protected function __onInit(){
		$this->messenger		= $this->env->getMessenger();
		$this->modelUser		= new Model_User( $this->env );
		$this->modelRelation	= new Model_User_Relation( $this->env );
		$this->userId			= $this->env->getSession()->get( 'userId' );
	}

	protected function getReferrer(/* $encoded = FALSE */){
		if( $this->env->getRequest()->has( 'from' )  )
			return $this->env->getRequest()->get( 'from' );
		$from		= '';
		$referer	= getEnv( 'HTTP_REFERER' );
		$regex		= "/^".preg_quote( $this->env->url, "/" )."/";
		if( $referer ){
			if( preg_match( $regex, $referer ) )
				return './'.preg_replace( $regex, "", $referer );
			return $referer;
		}
	}

	public function index(){
		$relations	= $this->modelRelation->getAllByIndex( 'fromUserId', $this->userId );
		$this->addData( 'relations', $relations );
	}

	public function search(){
		$users		= $this->modelUser->getAll( array( 'status' => '>=0' ), array( 'username' => 'ASC' ) );
		$this->addData( 'users', $users );
	}

	public function view( $userId ){
		$user = $this->modelUser->get( $userId );
		if( !$user ){
			$this->messenger->noteError( 'Invalid user ID' );
			$this->restart( NULL, TRUE );
		}
		$modelRole	= new Model_Role( $this->env );
		$role		= $modelRole->get( $user->roleId );
		$this->addData( 'currentUserId', $this->userId );
		$this->addData( 'user', $user );
		$this->addData( 'role', $role );
		$this->addData( 'from', $this->getReferrer() );
		$this->addData( 'relation', $this->modelRelation->getByIndex( 'fromUserId', $this->userId ) );
	}

	public function abc(){
	}
}
