<?php
class Controller_Member extends CMF_Hydrogen_Controller{

	protected function __onInit(){
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->messenger		= $this->env->getMessenger();
		$this->modelUser		= new Model_User( $this->env );
		$this->modelRelation	= new Model_User_Relation( $this->env );
		$this->userId			= $this->env->getSession()->get( 'userId' );
		$this->addData( 'currentUserId', $this->userId );
		if( !$this->session->get( 'filter_member_limit' ) )
			$this->session->set( 'filter_member_limit', 9 );
		$this->logicMember		= Logic_Member::getInstance( $this->env );
	}

	public function accept( $userRelationId ){
		$relation	= $this->modelRelation->get( $userRelationId );
		if( !$relation ){
			$this->messenger->noteError( 'Invalid user relation ID.' );
			$from	= $this->getReferrer();
			$this->restart( $from, $from ? FALSE : TRUE );
		}
	//  @todo send mail
		$this->modelRelation->edit( $relation->userRelationId, array(
			'status'	=> 2,
		) );
		$this->messenger->noteSuccess( 'Relation request has been accepted. The other user has been informed.' );
		$url	= 'view/'.$relation->fromUserId;
		if( $relation->fromUserId == $this->userId )
			$url	= 'view/'.$relation->toUserId;
		$this->restart( $url, TRUE );
	}

	public function filter( $reset = NULL ){
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

	protected function getReferrer(/* $encoded = FALSE */){
		if( $this->request->has( 'from' )  )
			return $this->request->get( 'from' );
		$from		= '';
		$regex		= "/^".preg_quote( $this->env->url, "/" )."/";
		$referer	= preg_replace( $regex, "", getEnv( 'HTTP_REFERER' ) );
		if( $referer ){
			if( !preg_match( '@member/view@', $referer ) )
				return $referer;
		}
	}

	public function index( $page = 0 ){
		$limit		= $this->session->get( 'filter_member_limit' );
		$offset		= $page * $limit;
		$userIds	= $this->logicMember->getRelatedUserIds( $this->userId, 2 );
		$users		= $this->logicMember->getUsersWithRelations( $this->userId, $userIds, $limit, $offset );
		$total		= count( $userIds );

		$incoming	= $this->modelRelation->getAllByIndices( array(
			'toUserId'	=> $this->userId,
			'status'	=> 1,
		) );
		foreach( $incoming as $relation )
			$relation->user	= $this->modelUser->get( $relation->fromUserId );

		$outgoing	= $this->modelRelation->getAllByIndices( array(
			'fromUserId'	=> $this->userId,
			'status'		=> 1,
		) );
		foreach( $outgoing as $relation )
			$relation->user	= $this->modelUser->get( $relation->toUserId );

		$this->addData( 'incoming', $incoming );
		$this->addData( 'outgoing', $outgoing );
		$this->addData( 'users', $users );
		$this->addData( 'total', $total );
		$this->addData( 'page', $page );
		$this->addData( 'pages', ceil( $total / $limit ) );

		$this->addData( 'filterQuery', $this->session->get( 'filter_member_query' ) );
		$this->addData( 'filterRelation', $this->session->get( 'filter_member_relation' ) );
	}

	public function search(){
		$query		= trim( $this->request->get( 'username' ) );
		$users		= array();
		if( $query ){
			$userIds	= $this->logicMember->getUserIdsByQuery( $query );
			$key		= array_search( $this->userId, $userIds );
			if( $key !== FALSE )
				unset( $userIds[$key] );
			$users		= $this->modelUser->getAllByIndex( 'userId', $userIds );
			foreach( $users as $user )
				$user->relation	= $this->modelRelation->getByIndex( 'fromUserId', $this->userId );
		}
		$this->addData( 'username', $query );
		$this->addData( 'users', $users );
	}

	public function view( $userId ){
		$user = $this->modelUser->get( $userId );
		if( !$user ){
			$this->messenger->noteError( 'Invalid user ID' );
			$this->restart( NULL, TRUE );
		}
		$relation	= $this->logicMember->getUserRelation( $this->userId, $userId );
		$modelRole	= new Model_Role( $this->env );
		$role		= $modelRole->get( $user->roleId );
		$this->addData( 'user', $user );
		$this->addData( 'role', $role );
		$this->addData( 'from', $this->getReferrer() );
		$this->addData( 'relation', $relation );
	}

	public function reject( $userRelationId ){
		$relation	= $this->modelRelation->get( $userRelationId );
		if( !$relation ){
			$this->messenger->noteError( 'Invalid user relation ID.' );
			$this->restart( NULL, TRUE );
		}
	//  @todo send mail
		$this->modelRelation->edit( $relation->userRelationId, array(
			'status'	=> -1,
		) );
		$this->messenger->noteSuccess( 'Relation request has been rejected. The other user has been informed.' );
		$url	= 'view/'.$relation->fromUserId;
		if( $relation->fromUserId == $this->userId )
			$url	= 'view/'.$relation->toUserId;
		$this->restart( $url, TRUE );
	}

	public function release( $userRelationId ){
		$relation	= $this->modelRelation->get( $userRelationId );
		if( !$relation ){
			$this->messenger->noteError( 'Invalid user relation ID.' );
			$this->restart( NULL, TRUE );
		}
	//  @todo send mail
		$this->modelRelation->remove( $relation->userRelationId );
		$this->messenger->noteSuccess( 'Relation has been revoked. The other user has been informed.' );
		$url	= 'view/'.$relation->fromUserId;
		if( $relation->fromUserId == $this->userId )
			$url	= 'view/'.$relation->toUserId;
		$this->restart( $url, TRUE );
	}

	public function request( $userId ){
		$relation	= $this->modelRelation->getByIndices( array(
			'fromUserId'	=> $this->userId,
			'toUserId'		=> $userId,
		) );
		if( $relation ){
			if( $relation->status == 1 ){
				$this->messenger->noteError( 'Relation already requested and confirmed.' );
				$this->restart( 'view/'.$userId.'?from='.$this->getReferrer(), TRUE );
			}
			if( $relation->status == 0 ){
				$this->messenger->noteError( 'Relation already requested. Please wait for confirmation!' );
				$this->restart( 'view/'.$userId.'?from='.$this->getReferrer(), TRUE );
			}
		}
		$data	= array(
			'fromUserId'	=> $this->userId,
			'toUserId'		=> $userId,
//			'type'			=> 1,
			'status'		=> 1,
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		);
	//  @todo send mail
		$this->modelRelation->add( $data );
		$this->messenger->noteSuccess( 'Relation request has been sent. Please wait for confirmation!' );
		$this->restart( 'view/'.$userId.'?from='.$this->getReferrer(), TRUE );
	}
}
