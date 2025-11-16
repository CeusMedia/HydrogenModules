<?php

use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Member extends Controller
{
	protected $request;
	protected $session;
	protected MessengerResource $messenger;
	protected Logic_Member $logicMember;
	protected Logic_Mail $logicMail;
	protected Logic_User $logicUser;
	protected Model_User $modelUser;
	protected Model_User_Relation $modelRelation;
	protected ?string $userId;

	/**
	 *	@param		int|string		$userRelationId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function accept( int|string $userRelationId ): void
	{
		$words		= (object) $this->getWords( 'msg' );
		$relation	= $this->modelRelation->get( $userRelationId );
		if( !$relation ){
			$this->messenger->noteError( $words->errorRelationIdInvalid );
			$from	= $this->getReferrer();
			$this->restart( $from, $from ? FALSE : TRUE );
		}
		try{
			$language	= $this->env->getLanguage()->getLanguage();
			$receiver	= $this->logicUser->getUser( $relation->fromUserId );
			$mail		= new Mail_Member_Accept( $this->env, [
				'sender'	=> $this->logicUser->getUser( $this->userId ),
				'receiver'	=> $receiver,
			] );
			$this->logicMail->handleMail( $mail, $receiver, $language );
			$this->modelRelation->edit( $relation->userRelationId, [
				'status'	=> 2,
			] );
			$this->messenger->noteSuccess( $words->successAccepted );
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $words->failureMail );
			$payload	= ['exception' => $e];
			$this->callHook( 'Env', 'logException', $this, $payload );
		}
		$url	= 'view/'.$relation->fromUserId;
		if( $relation->fromUserId == $this->userId )
			$url	= 'view/'.$relation->toUserId;
		$this->restart( $url, TRUE );
	}

	public function filter( $reset = NULL ): void
	{
		if( $reset ){
			foreach( $this->session->getAll( 'filter_member_' ) as $key => $value ){
				$this->session->remove( 'filter_member_'.$key );
			}
		}
		else if( $this->request->has( 'filter' ) ){
			$this->session->set( 'filter_member_query', $this->request->get( 'query' ) );
			$this->session->set( 'filter_member_relation', $this->request->get( 'relation' ) );
//			$this->session->set( 'filter_member_limit', $this->request->get( 'limit' ) );
//			$this->session->set( 'filter_member_order', $this->request->get( 'order' ) );
//			$this->session->set( 'filter_member_direction', $this->request->get( 'direction' ) );
		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int		$page
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index( int $page = 0 ): void
	{
		$limit		= $this->session->get( 'filter_member_limit' );
		$offset		= $page * $limit;
		$userIds	= $this->logicMember->getRelatedUserIds( $this->userId, 2 );
		$users		= $this->logicMember->getUsersWithRelations( $this->userId, $userIds, $limit, $offset );
		$total		= count( $userIds );

		$incoming	= $this->modelRelation->getAllByIndices( [
			'toUserId'	=> $this->userId,
			'status'	=> 1,
		] );
		foreach( $incoming as $relation )
			$relation->user	= $this->logicUser->getUser( $relation->fromUserId );

		$outgoing	= $this->modelRelation->getAllByIndices( [
			'fromUserId'	=> $this->userId,
			'status'		=> 1,
		] );
		foreach( $outgoing as $relation )
			$relation->user	= $this->logicUser->getUser( $relation->toUserId );

		$this->addData( 'incoming', $incoming );
		$this->addData( 'outgoing', $outgoing );
		$this->addData( 'users', $users );
		$this->addData( 'total', $total );
		$this->addData( 'page', $page );
		$this->addData( 'pages', ceil( $total / $limit ) );

		$this->addData( 'filterQuery', $this->session->get( 'filter_member_query' ) );
		$this->addData( 'filterRelation', $this->session->get( 'filter_member_relation' ) );
	}

	/**
	 *	@param		int|string		$userRelationId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function reject( int|string $userRelationId ): void
	{
		$words		= (object) $this->getWords( 'msg' );
		$relation	= $this->modelRelation->get( $userRelationId );
		if( !$relation ){
			$this->messenger->noteError( 'Invalid user relation ID.' );
			$this->restart( NULL, TRUE );
		}
		try{
			$language	= $this->env->getLanguage()->getLanguage();
			$receiver	= $this->logicUser->getUser( $relation->fromUserId );
			$mail		= new Mail_Member_Reject( $this->env, [
				'sender'	=> $this->logicUser->getUser( $this->userId ),
				'receiver'	=> $receiver,
			] );
			$this->logicMail->handleMail( $mail, $receiver, $language );

			$this->modelRelation->edit( $relation->userRelationId, [
				'status'	=> -1,
			] );
			$this->messenger->noteSuccess( $words->successRejected );
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $words->failureMail );
			$payload	= ['exception' => $e];
			$this->callHook( 'Env', 'logException', $this, $payload );
		}
		$url	= 'view/'.$relation->fromUserId;
		if( $relation->fromUserId == $this->userId )
			$url	= 'view/'.$relation->toUserId;
		$this->restart( $url, TRUE );
	}

	/**
	 *	@param		int|string		$userRelationId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function release( int|string $userRelationId ): void
	{
		$words		= (object) $this->getWords( 'msg' );
		$relation	= $this->modelRelation->get( $userRelationId );
		if( !$relation ){
			$this->messenger->noteError( 'Invalid user relation ID.' );
			$this->restart( NULL, TRUE );
		}
		try{
			$toUserId	= $relation->toUserId;
			if( $relation->toUserId == $this->userId )
				$toUserId	= $relation->fromUserId;

			$language	= $this->env->getLanguage()->getLanguage();
			$receiver	= $this->logicUser->getUser( $toUserId );
			$mail		= new Mail_Member_Revoke( $this->env, [
				'sender'	=> $this->logicUser->getUser( $this->userId ),
				'receiver'	=> $receiver,
			] );
			$this->logicMail->handleMail( $mail, $receiver, $language );
			$this->modelRelation->remove( $relation->userRelationId );
			$this->messenger->noteSuccess( $words->successReleased );
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $words->failureMail );
			$payload	= ['exception' => $e];
			$this->callHook( 'Env', 'logException', $this, $payload );
		}
		$url	= 'view/'.$relation->fromUserId;
		if( $relation->fromUserId == $this->userId )
			$url	= 'view/'.$relation->toUserId;
		$this->restart( $url, TRUE );
	}

	/**
	 *	@param		int|string		$userId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function request( int|string $userId ): void
	{
		$words		= (object) $this->getWords( 'msg' );
		$relation	= $this->modelRelation->getByIndices( [
			'fromUserId'	=> $this->userId,
			'toUserId'		=> $userId,
		] );
		if( $relation ){
			if( $relation->status == 2 ){
				$this->messenger->noteError( $words->errorAlreadyAccepted );
				$this->restart( 'view/'.$userId.'?from='.$this->getReferrer(), TRUE );
			}
			if( $relation->status == 1 ){
				$this->messenger->noteError( $words->errorAlreadyRequested );
				$this->restart( 'view/'.$userId.'?from='.$this->getReferrer(), TRUE );
			}
		}
		try{
			$language	= $this->env->getLanguage()->getLanguage();
			$receiver	= $this->logicUser->getUser( $userId );
			$mail		= new Mail_Member_Request( $this->env, [
				'sender'	=> $this->logicUser->getUser( $this->userId ),
				'receiver'	=> $receiver,
			] );
			$this->logicMail->handleMail( $mail, $receiver, $language );
			$data	= [
				'fromUserId'	=> $this->userId,
				'toUserId'		=> $userId,
	//			'type'			=> 1,
				'status'		=> 1,
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			];
			$this->modelRelation->add( $data );
			$this->messenger->noteSuccess( $words->successRequested );
		}
		catch( Throwable $e ){
			$this->messenger->noteFailure( $words->failureMail );
			$payload	= ['exception' => $e];
			$this->callHook( 'Env', 'logException', $this, $payload );
		}
		$this->restart( 'view/'.$userId.'?from='.$this->getReferrer(), TRUE );
	}

	public function search(): void
	{
		$query		= trim( $this->request->get( 'username' ) );
		$users		= [];
		if( $query ){
			$userIds	= $this->logicMember->getUserIdsByQuery( $query );
			$key		= array_search( $this->userId, $userIds );
			if( $key !== FALSE )
				unset( $userIds[$key] );
			$knownUsers	= $this->logicMember->getRelatedUserIds( $this->userId, 2 );
			foreach( $knownUsers as $userId )
				if( ( $key = array_search( $userId, $userIds ) ) !== FALSE )
					unset( $userIds[$key] );
			if( $userIds ){
				/** @var array<Entity_User> $users */
				$users		= $this->modelUser->getAllByIndex( 'userId', $userIds );
				foreach( $users as $user )
					$user->relation	= $this->modelRelation->getByIndex( 'fromUserId', $this->userId );
			}
		}
		$this->addData( 'username', $query );
		$this->addData( 'users', $users );
	}

	/**
	 *	@param		int|string		$userId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function view( int|string $userId ): void
	{
		$words		= (object) $this->getWords( 'msg' );
		/** @var ?Entity_User $user */
		$user = $this->logicUser->getUser( $userId );
		if( !$user ){
			$this->messenger->noteError( $words->errorUserIdInvalid );
			$this->restart( NULL, TRUE );
		}
		$relation	= $this->logicMember->getUserRelation( $this->userId, $userId );
		$role		= Logic_Role::getInstance( $this->env )->get( $user->roleId );
		$this->addData( 'user', $user );
		$this->addData( 'role', $role );
		$this->addData( 'from', $this->getReferrer() );
		$this->addData( 'relation', $relation );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->messenger		= $this->env->getMessenger();
		$this->logicMember		= Logic_Member::getInstance( $this->env );
		$this->logicMail		= Logic_Mail::getInstance( $this->env );
		$this->logicUser		= Logic_User::getInstance( $this->env );
		$this->modelUser		= new Model_User( $this->env );
		$this->modelRelation	= new Model_User_Relation( $this->env );
		$this->userId			= $this->env->getSession()->get( Logic_Authentication::$sessionKeyAuthUserId );
		$this->addData( 'currentUserId', $this->userId );
		if( !$this->session->get( 'filter_member_limit' ) )
			$this->session->set( 'filter_member_limit', 9 );
	}

	protected function getReferrer(/* $encoded = FALSE */)
	{
		if( $this->request->has( 'from' )  )
			return $this->request->get( 'from' );
		$from		= '';
		$regex		= "/^".preg_quote( $this->env->url, "/" )."/";
		$referer	= preg_replace( $regex, "", getEnv( 'HTTP_REFERER' ) );
		if( $referer && !str_contains( $referer, 'member/view' ) )
			return $referer;
	}
}
