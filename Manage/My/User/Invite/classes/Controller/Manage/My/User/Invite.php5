<?php
class Controller_Manage_My_User_Invite extends CMF_Hydrogen_Controller{

	protected $messenger;
	
	/**	@var	Model_User_Invite		$model		Instance of user invite model */
	protected $model;
	protected $request;

	public function __onInit(){
		$this->messenger	= $this->env->getMessenger();
		$this->request		= $this->env->getRequest();
		$this->model		= new Model_User_Invite( $this->env );
	}
	
	public function cancel( $userInviteId ){
		$this->model->setStatus( $userInviteId, -2 );
		$this->restart( NULL, TRUE );
	}
	
	public function index(){
		$config		= $this->env->getConfig();
		$invites	= (object) array(
			'codes'	=> $this->model->getAllByIndices( array( 'type' => 1, 'status' => 0 ) ),
			'open'	=> $this->model->getAllByIndices( array( 'type' => 1, 'status' => 1 ) ),
			'done'	=> $this->model->getAllByIndices( array( 'type' => 1, 'status' => 2 ) ),
			'all'	=> $this->model->getAllByIndices( array( 'type' => 1 ) ),
		);
		$promotes	= (object) array(
			'open'	=> $this->model->getAllByIndices( array( 'type' => 0, 'status' => 1 ) ),
			'done'	=> $this->model->getAllByIndices( array( 'type' => 0, 'status' => 2 ) ),
		);
		$this->addData( 'daysValid', $config->get( 'module.manage_my_user_invite.days.valid' ) );
		$this->addData( 'invites', $invites );
	}

	public function invite(){
		$userId		= $this->env->getSession()->get( 'userId' );
		$words		= (object) $this->getWords( 'invite' );
		if( $this->env->getRequest()->get( 'send' ) ){
			$email		= $this->request->get( 'email' );
			$subject	= $this->request->get( 'subject' );
			$message	= $this->request->get( 'message' );

			do $code	= $this->model->generateInviteCode( $userId );								//  generate invite code
			while( $this->model->countByIndex( 'code', $code ) );									//  until it is unique (not used yet)

			$data	= array(
				'inviterId'	=> $userId,
				'projectId'	=> (int) $this->request->get( 'projectId' ),
				'type'		=> 1,
				'status'	=> 1,
				'code'		=> $code,
				'email'		=> $email,
				'createdAt'	=> time(),
			);
			$userInviteId	= $this->model->add( $data );
			$this->env->getMessenger()->noteSuccess( $words->msgSuccess, $email );
			$this->restart( NULL, TRUE );
		}
		$modelUser	= new Model_User( $this->env );
		$this->addData( 'user', $modelUser->get( $userId ) );
	}

	public function promote(){
		if( $this->env->getRequest()->get( 'send' ) ){
		
			$this->restart( NULL, TRUE );
		}
	}

	protected function generateUserInviteCodes( $userId, $limit = 3 ){
		$invites		= $this->model->getAllByIndex( 'inviterId', $userId );
		$loops			= min( 1, (int) $limit ) - count( $invites );
		for( $i=0; $i<$loops; $i++ ){
			$code	= $this->model->generateInviteCode( $userId );
			$data	= array(
				'inviterId'	=> $userId,
				'type'		=> 1,
				'status'	=> 0,
				'code'		=> $code,
				'createdAt'	=> time(),
			);
			$this->model->add( $data );
		}
		return $loops;
	}
}
?>