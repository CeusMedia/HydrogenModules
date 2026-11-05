<?php

use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\Net\HTTP\PartitionSession as HttpPartitionSession;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Info_Mail_Group extends Controller
{
	protected HttpRequest $request;
	protected HttpPartitionSession $session;
	protected MessengerResource $messenger;
	protected Logic_Mail_Group $logic;
	protected Logic_Mail $logicMail;
	protected Model_Mail_Group $modelGroup;
	protected Model_Mail_Group_Member $modelMember;
	protected Model_Mail_Group_Action $modelAction;
	protected Model_User $modelUser;
	protected string $filterPrefix		= 'filter_info_mail_group_';
	protected int $defaultLimit		= 10;

	/**
	 *	@param		string		$actionId
	 *	@param		string		$hash
	 *	@return		void
	 */
	public function completeMemberAction( string $actionId, string $hash ): void
	{
		$indices	= ['mailGroupActionId' => $actionId, 'uuid' => $hash];
		$action		= $this->modelAction->getByIndices( $indices );
		if( !$action ){
			$this->messenger->noteError( $this->getWords( 'msg' )['errorInvalidAction'] );
			$this->restart( );
		}
		if( $action->status == 1 ){
			$this->messenger->noteError( $this->getWords( 'msg' )['errorLinkExpired'] );
			$this->restart();
		}
		try{
			$payload	= ['action' => $action];
			$result		= $this->env->getModules()->callHookWithPayload(
				'MailGroupAction',
				$action->action,
				$this,
				$payload
			);
			if( $result )
				$this->modelAction->edit( $action->mailGroupActionId, [
					'status'		=> Model_Mail_Group_Action::STATUS_HANDLED,
					'modifiedAt'	=> time(),
				] );
			if( is_string( $result ) )
				$this->restart( $result );
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $e->getMessage() );
			$this->modelAction->edit( $action->mailGroupActionId, [
				'status'		=> Model_Mail_Group_Action::STATUS_FAILED,
				'modifiedAt'	=> time(),
			] );
		}
		$this->restart( NULL, TRUE );
	}

	public function filter( $reset = NULL ): void
	{
		if( $reset ){
			$this->session->remove( $this->filterPrefix.'page' );
			$this->session->remove( $this->filterPrefix.'limit' );
			$this->session->remove( $this->filterPrefix.'type' );
		}
		$this->session->remove( $this->filterPrefix.'page' );
		$this->session->set( $this->filterPrefix.'type', $this->request->get( 'type' ) );
		$this->restart( NULL, TRUE );
	}

	public function index( $page = NULL, $limit = NULL ): void
	{
		if( !is_null( $page ) && $page >= 0 )
			$this->session->set( $this->filterPrefix.'page', (int) $page );
		if( !is_null( $limit ) && $limit >= $this->defaultLimit )
			$this->session->set( $this->filterPrefix.'limit', (int) $limit );
		$page		= $this->session->get( $this->filterPrefix.'page' );
		$limit		= $this->session->get( $this->filterPrefix.'limit' );
		$filterType	= $this->session->get( $this->filterPrefix.'type' );

		$conditions	= [
			'status'		=> Model_Mail_Group::STATUS_ACTIVATED,
			'visibility'	=> Model_Mail_Group::VISIBILITY_PUBLIC,
		];
		if( $filterType )
			$conditions['type']	= $filterType;
		$orders	= ['title' => 'ASC'];
		$limits	= [$limit, $page * $limit];
		$total	= $this->modelGroup->count( $conditions );
		$groups	= $this->modelGroup->getAll( $conditions, $orders, $limits );
		foreach( $groups as $group ){
			$group->members		= $this->logic->countGroupMembers( $group->mailGroupId );
			$group->messages	= $this->logic->countGroupMessages( $group->mailGroupId );
		}
		$this->addData( 'groups', $groups );
		$this->addData( 'filterLimit', $limit );
		$this->addData( 'filterPage', $page );
		$this->addData( 'filterPages', ceil( $total / $limit ) );
	}

	/**
	 *	@param		string|NULL		$groupId
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function join( string $groupId = NULL ): void
	{
		if( $this->request->has( 'save' ) ){
			$address	= trim( $this->request->get( 'address' ) );
			$email		= trim( $this->request->get( 'email' ) );
			$name		= trim( $this->request->get( 'name' ) );
			$greeting	= trim( $this->request->get( 'message' ) );
			$registered	= FALSE;

			if( $groupId )
				$group		= $this->logic->getGroup( $groupId, TRUE, FALSE );
			else if( $address )
				$group		= $this->logic->getMailGroupFromAddress( $address, TRUE, FALSE );

			if( !isset( $group ) )
				$this->messenger->noteError( $this->getWords( 'msg' )['errorGroupNotFound'] );
			else{
				$groupId 	= $group->mailGroupId;
				$member		= $this->logic->getGroupMemberByAddress( $group->mailGroupId, $email, FALSE, FALSE );
				if( !$member ){
					$memberId	= $this->modelMember->add( array(
						'mailGroupId'	=> $group->mailGroupId,
						'roleId'		=> $group->defaultRoleId,
						'status'		=> Model_Mail_Group_Member::STATUS_REGISTERED,
						'address'		=> $email,
						'title'			=> strlen( $name ) ? $name : NULL,
						'createdAt'		=> time(),
						'modifiedAt'	=> time(),
					) );
					$registered	= TRUE;
				}
				else{
					$memberId	= $member->mailGroupMemberId;
					if( $member->status == Model_Mail_Group_Member::STATUS_ACTIVATED )
						$this->messenger->noteError( $this->getWords( 'msg' )['errorAlreadyActive'] );
					else if( $member->status == Model_Mail_Group_Member::STATUS_CONFIRMED )
						$this->messenger->noteError( $this->getWords( 'msg' )['errorAlreadyRegistered'] );
					else if( $member->status == Model_Mail_Group_Member::STATUS_DEACTIVATED )
						$this->messenger->noteError( $this->getWords( 'msg' )['errorAddressDeactivated'] );
					else if( $member->status == Model_Mail_Group_Member::STATUS_UNREGISTERED ){
						$this->modelMember->edit( $memberId, [
							'roleId'		=> $group->defaultRoleId,
							'status'		=> Model_Mail_Group_Member::STATUS_REGISTERED,
							'title'			=> strlen( $name ) ? $name : NULL,
							'modifiedAt'	=> time(),
						] );
						$registered	= TRUE;
					}
				}
				if( $registered ){
					$action	= $this->logic->registerMemberAction( 'confirmAfterJoin', $groupId, $memberId, $greeting );

					$member	= $this->logic->getGroupMember( $memberId );
					$mail	= new Mail_Info_Mail_Group_Member_Joining( $this->env, [
						'member'	=> $member,
						'group'		=> $group,
						'action'	=> $action,
					] );
					$receiver	= (object) ['email' => $member->address];
					$language	= $this->env->getLanguage()->getLanguage();
					$this->logicMail->appendRegisteredAttachments( $mail, $language );
					$this->logicMail->sendMail( $mail, $receiver );
					$this->messenger->noteSuccess( $this->getWords( 'msg' )['successJoinRequested'] );
					$this->restart( 'joined/'.$groupId.'/'.$memberId, TRUE );
				}
			}
		}
		$group = (int) $groupId > 0 ? $this->checkId( $groupId ) : NULL;
		$this->addData( 'group', $group );
		$this->addData( 'data', (object) $this->request->getAll() );
	}

	/**
	 *	@param		string		$groupId
	 *	@param		string		$memberId
	 *	@return		void
	 */
	public function joined( string $groupId, string $memberId ): void
	{
		$group = $this->checkId( (int) $groupId );
		$member = $this->logic->checkMemberId( (int) $memberId );
		$this->addData( 'group', $group );
		$this->addData( 'member', $member );
	}

	/**
	 *	@param		string|NULL		$groupId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function leave( ?string $groupId = NULL ): void
	{
		if( $this->request->has( 'save' ) ){
			$address	= trim( $this->request->get( 'address', '' ) );
			$email		= trim( $this->request->get( 'email', '' ) );
			$name		= trim( $this->request->get( 'name', '' ) );
			$greeting	= trim( $this->request->get( 'message', '' ) );
			$registered	= FALSE;

			if( $groupId )
				$group		= $this->logic->getGroup( $groupId, TRUE, FALSE );
			else if( $address )
				$group		= $this->logic->getMailGroupFromAddress( $address, TRUE, FALSE );

			if( !isset( $group ) )
				$this->messenger->noteError( $this->getWords( 'msg' )['errorGroupNotFound'] );
			else{
				$groupId 	= $group->mailGroupId;
				$member		= $this->logic->getGroupMemberByAddress( $group->mailGroupId, $email, FALSE, FALSE );
				if( !$member ){
					$this->messenger->noteError( $this->getWords( 'msg' )['errorNoMembership'] );
				}
				else{
					$memberId	= $member->mailGroupMemberId;
					if( $member->status == Model_Mail_Group_Member::STATUS_DEACTIVATED )
						$this->messenger->noteError( $this->getWords( 'msg' )['errorAddressDeactivated'] );
					else if( $member->status == Model_Mail_Group_Member::STATUS_UNREGISTERED )
						$this->messenger->noteError( $this->getWords( 'msg' )['errorAddressLeft'] );
					else{
						$action	= $this->logic->registerMemberAction( 'deactivateAfterLeaving', $groupId, $memberId, $greeting );

						$mail	= new Mail_Info_Mail_Group_Member_Leaving( $this->env, [
							'member'	=> $member,
							'group'		=> $group,
							'action'	=> $action,
						] );
						$receiver	= (object) [
							'email'		=> $member->address,
							'username'	=> $member->title
						];
						$language	= $this->env->getLanguage()->getLanguage();
						$this->logicMail->appendRegisteredAttachments( $mail, $language );
						$this->logicMail->handleMail( $mail, $receiver, $language );
						$this->messenger->noteSuccess( $this->getWords( 'msg' )['successLeaveRequested'] );
						$this->restart( NULL, TRUE );
					}
				}
			}
		}
		$group = (int) $groupId > 0 ? $this->checkId( $groupId ) : NULL;
		$this->addData( 'group', $group );
		$this->addData( 'groupId', (int) $groupId );
		$this->addData( 'data', (object) $this->request->getAll() );
	}

	/**
	 *	@param		string		$groupId
	 *	@return		void
	 */
	public function view( string $groupId ): void
	{
		$this->addData( 'group', $this->checkId( $groupId ) );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->logic		= new Logic_Mail_Group( $this->env );
		$this->logicMail	= Logic_Mail::getInstance( $this->env );
		$this->modelGroup	= new Model_Mail_Group( $this->env );
		$this->modelMember	= new Model_Mail_Group_Member( $this->env );
		$this->modelAction	= new Model_Mail_Group_Action( $this->env );
		$this->modelUser	= new Model_User( $this->env );
		if( $this->session->get( $this->filterPrefix.'limit' ) < $this->defaultLimit )
			$this->session->set( $this->filterPrefix.'limit', $this->defaultLimit );
	}

	/**
	 *	@param		int|string		$groupId
	 *	@param		bool			$restart
	 *	@return		object|FALSE
	 */
	protected function checkId( int|string $groupId, bool $restart = TRUE ): object|FALSE
	{
		try{
 			$group				= $this->logic->getGroup( $groupId, TRUE );
			$group->members		= $this->logic->countGroupMembers( $groupId );					//  does not scale very well
			$group->messages	= $this->logic->countGroupMessages( $groupId );					//  does not scale very well
			return $group;
		}
		catch( Exception $e ){
			$this->messenger->noteError( $this->getWords( 'msg' )['errorGroupUnavailable'] );
			if( $restart )
				$this->restart( NULL, TRUE );
		}
		return FALSE;
	}

	/**
	 *	@param		int|string|object		$idOrAddress
	 *	@param		bool					$strict
	 *	@return		Object|FALSE
	 */
	protected function checkGroupByIdOrAddress( int|string|object $idOrAddress, bool $strict = TRUE ): object|FALSE
	{
		if( is_int( $idOrAddress ) )
			return $this->checkId( $idOrAddress, $strict );
		if( ( $group = $this->logic->getMailGroupFromAddress( $idOrAddress, TRUE ) ) )
			return $group;
		$this->messenger->noteError( $this->getWords( 'msg' )['errorGroupNotFound'] );
		return FALSE;
	}
}
