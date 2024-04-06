<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;

class Controller_Manage_My_User_Invite extends Controller
{
	protected Messenger $messenger;

	/**	@var	Model_User_Invite		$model		Instance of user invite model */
	protected Model_User_Invite $model;
	protected Dictionary $request;

	public function cancel( string $userInviteId ): void
	{
		$this->model->setStatus( $userInviteId, -2 );
		$this->restart( NULL, TRUE );
	}

	public function index(): void
	{
		$config		= $this->env->getConfig();
		$invites	= (object) [
			'codes'	=> $this->model->getAllByIndices( ['type' => 1, 'status' => 0] ),
			'open'	=> $this->model->getAllByIndices( ['type' => 1, 'status' => 1] ),
			'done'	=> $this->model->getAllByIndices( ['type' => 1, 'status' => 2] ),
			'all'	=> $this->model->getAllByIndices( ['type' => 1] ),
		];
		$promotes	= (object) [
			'open'	=> $this->model->getAllByIndices( ['type' => 0, 'status' => 1] ),
			'done'	=> $this->model->getAllByIndices( ['type' => 0, 'status' => 2] ),
		];
		$this->addData( 'daysValid', $config->get( 'module.manage_my_user_invite.days.valid' ) );
		$this->addData( 'invites', $invites );
	}

	public function invite(): void
	{
		$userId		= $this->env->getSession()->get( 'auth_user_id' );
		$words		= (object) $this->getWords( 'invite' );
		if( $this->env->getRequest()->get( 'send' ) ){
			$email		= $this->request->get( 'email' );
			$subject	= $this->request->get( 'subject' );
			$message	= $this->request->get( 'message' );

			do $code	= $this->model->generateInviteCode( $userId );								//  generate invite code
			while( $this->model->countByIndex( 'code', $code ) );									//  until it is unique (not used yet)

			$data	= [
				'inviterId'	=> $userId,
				'projectId'	=> $this->request->get( 'projectId', '0' ),
				'type'		=> 1,
				'status'	=> 1,
				'code'		=> $code,
				'email'		=> $email,
				'createdAt'	=> time(),
			];
			$userInviteId	= $this->model->add( $data );
			$this->env->getMessenger()->noteSuccess( $words->msgSuccess, $email );
			$this->restart( NULL, TRUE );
		}
		$modelUser	= new Model_User( $this->env );
		$this->addData( 'user', $modelUser->get( $userId ) );
	}

	public function promote(): void
	{
		if( $this->env->getRequest()->get( 'send' ) ){

			$this->restart( NULL, TRUE );
		}
	}

	protected function __onInit(): void
	{
		$this->messenger	= $this->env->getMessenger();
		$this->request		= $this->env->getRequest();
		$this->model		= new Model_User_Invite( $this->env );
	}

	protected function generateUserInviteCodes( string $userId, int $limit = 3 )
	{
		$invites		= $this->model->getAllByIndex( 'inviterId', $userId );
		$loops			= min( 1, $limit ) - count( $invites );
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
