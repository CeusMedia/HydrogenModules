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
	
	public function index(){
		$invites	= (object) array(
			'codes'	=> $this->model->getAllByIndices( array( 'type' => 1, 'status' => 0 ) ),
			'open'	=> $this->model->getAllByIndices( array( 'type' => 1, 'status' => 1 ) ),
			'done'	=> $this->model->getAllByIndices( array( 'type' => 1, 'status' => 2 ) ),
		);
		$this->addData( 'invitesCode', $invites );
	}

	public function invite(){
		$words	= (object) $this->getWords( 'invite' );
		if( $this->env->getRequest()->get( 'send' ) ){
			$email		= $this->request->get( 'email' );
			$subject	= $this->request->get( 'subject' );
			$message	= $this->request->get( 'message' );

			$userId	= $this->env->getSession()->get( 'userId' );
			$code	= $this->model->generateInviteCode( $userId );

			$data	= array(
				'inviterId'	=> $userId,
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