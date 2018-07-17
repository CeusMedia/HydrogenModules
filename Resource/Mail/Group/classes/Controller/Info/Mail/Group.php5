<?php
class Controller_Info_Mail_Group extends CMF_Hydrogen_Controller{

	protected $request;
	protected $session;
	protected $messenger;
	protected $logic;
	protected $logicMail;
	protected $modelGroup;
	protected $modelMember;
	protected $modelAction;
	protected $modelUser;
	protected $filterPrefix		= 'filter_info_mail_group_';
	protected $defaultLimit		= 10;

	public function __onInit(){
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

	protected function checkId( $groupId, $restart = TRUE ){
		try{
 			$group				= $this->logic->getGroup( $groupId, TRUE );
			$group->members		= $this->logic->countGroupMembers( $groupId );					//  does not scale very vell
			$group->messages	= $this->logic->countGroupMessages( $groupId );					//  does not scale very vell
			return $group;
		}
		catch( Exception $e ){
			$this->messenger->noteError( 'Die angesteuerte Gruppe existent nicht, nicht mehr oder ist nicht mehr sichtbar.<br/>Weiterleitung zur Übersicht.' );
			if( $restart )
				$this->restart( NULL, TRUE );
		}
	}

	protected function checkGroupByIdOrAddress( $idOrAddress, $strict = TRUE ){
		if( is_int( $idOrAddress ) )
			return $this->checkId( $idOrAddress, $strict );
		if( ( $group = $this->logic->getMailGroupFromAddress( $address, TRUE ) ) )
			return $group;
		$this->messenger->noteError( 'Die gewählte Gruppe existent nicht oder nicht mehr.' );
		return FALSE;
	}

	public function completeMemberAction( $actionId, $hash ){
		$indices	= array( 'mailGroupActionId' => $actionId, 'uuid' => $hash );
		$action		= $this->modelAction->getByIndices( $indices );
		if( !$action ){
			$this->messenger->noteError( 'Invalid action.' );
			$this->restart( NULL );
		}
		if( $action->status == 1 ){
			$this->messenger->noteError( 'Der Bestätigungslink ist nicht mehr gültig.' );
			$this->restart( NULL );
		}
		try{
			$result	= $this->env->getModules()->callHook(
				'MailGroupAction',
				$action->action,
				$this,
				array( 'action' => $action )
			);
			if( $result )
				$this->modelAction->edit( $action->mailGroupActionId, array(
					'status'		=> Model_Mail_Group_Action::STATUS_HANDLED,
					'modifiedAt'	=> time(),
				) );
			if( is_string( $result ) )
				$this->restart( $result );
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $e->getMessage() );
			$this->modelAction->edit( $action->mailGroupActionId, array(
				'status'		=> Model_Mail_Group_Action::STATUS_FAILED,
				'modifiedAt'	=> time(),
			) );
		}
		$this->restart( NULL, TRUE );
	}

	public function filter( $reset = NULL ){
		if( $reset ){
			$this->session->remove( $this->filterPrefix.'page' );
			$this->session->remove( $this->filterPrefix.'limit' );
			$this->session->remove( $this->filterPrefix.'type' );
		}
		$this->session->remove( $this->filterPrefix.'page' );
		$this->session->set( $this->filterPrefix.'type', $this->request->get( 'type' ) );
		$this->restart( NULL, TRUE );
	}

	public function index( $page = NULL, $limit = NULL ){
		if( !is_null( $page ) && $page >= 0 )
			$this->session->set( $this->filterPrefix.'page', (int) $page );
		if( !is_null( $limit ) && $limit >= $this->defaultLimit )
			$this->session->set( $this->filterPrefix.'limit', (int) $limit );
		$page		= $this->session->get( $this->filterPrefix.'page' );
		$limit		= $this->session->get( $this->filterPrefix.'limit' );
		$filterType	= $this->session->get( $this->filterPrefix.'type' );

		$conditions	= array(
			'status'		=> Model_Mail_Group::STATUS_ACTIVATED,
			'visibility'	=> Model_Mail_Group::VISIBILITY_PUBLIC,
		);
		if( $filterType )
			$conditions['type']	= $filterType;
		$orders	= array( 'title' => 'ASC' );
		$limits	= array( $limit, $page * $limit );
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

	public function join( $groupId = 0 ){
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
			if( !$group )
				$this->messenger->noteError( 'Die gewählte Gruppe existent nicht oder nicht mehr.' );
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
						$this->messenger->noteError( 'Sie sind bereits an der Gruppe registriert und ihre Mitgliedschaft ist aktiv.' );
					else if( $member->status == Model_Mail_Group_Member::STATUS_CONFIRMED )
						$this->messenger->noteError( 'Sie haben sich bereits registriert, aber die Zusage steht noch aus.' );
					else if( $member->status == Model_Mail_Group_Member::STATUS_DEACTIVATED )
						$this->messenger->noteError( 'Diese Adresse war an der Gruppe bereits registriert, wurde aber deaktiviert.' );
					else if( $member->status == Model_Mail_Group_Member::STATUS_UNREGISTERED ){
						$this->modelMember->edit( $memberId, array(
							'roleId'		=> $group->defaultRoleId,
							'status'		=> Model_Mail_Group_Member::STATUS_REGISTERED,
							'title'			=> strlen( $name ) ? $name : NULL,
							'modifiedAt'	=> time(),
						) );
						$registered	= TRUE;
					}
				}
			}
			if( $registered ){
				$action	= $this->logic->registerMemberAction( 'confirmAfterJoin', $groupId, $memberId, $greeting );

				$member	= $this->logic->getGroupMember( $memberId, FALSE );
				$mail	= new Mail_Info_Mail_Group_Member_Joining( $this->env, array(
					'member'	=> $member,
					'group'		=> $group,
					'action'	=> $action,
				) );
				$receiver	= (object) array( 'email' => $member->address );
				$language	= $this->env->getLanguage()->getLanguage();
				$this->logicMail->appendRegisteredAttachments( $mail, $language );
				$this->logicMail->handleMail( $mail, $receiver, $language );
				$this->messenger->noteSuccess( 'Der Beitritt zur Gruppe wurde beantragt. Bitte jetzt im Postfach nach der Bestätigungs-E-Mail schauen!' );
				$this->restart( 'joined/'.$groupId.'/'.$memberId, TRUE );
			}
		}
		$group = (int) $groupId > 0 ? $this->checkId( $groupId ) : NULL;
		$this->addData( 'group', $group );
		$this->addData( 'data', (object) $this->request->getAll() );
	}

	public function joined( $groupId, $memberId ){
		$group = $this->checkId( (int) $groupId );
		$member = $this->logic->checkMemberId( (int) $memberId );
		$this->addData( 'group', $group );
		$this->addData( 'member', $member );
	}

	public function leave( $groupId = NULL ){
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
			if( !$group )
				$this->messenger->noteError( 'Die gewählte Gruppe existent nicht oder nicht mehr.' );
			else{
				$groupId 	= $group->mailGroupId;
				$member		= $this->logic->getGroupMemberByAddress( $group->mailGroupId, $email, FALSE, FALSE );
				if( !$member ){
					$this->messenger->noteError( 'Zu dieser Adresse gibt es bei dieser Gruppe keine Mitgliedschaft.' );
				}
				else{
					$memberId	= $member->mailGroupMemberId;
					if( $member->status == Model_Mail_Group_Member::STATUS_DEACTIVATED )
						$this->messenger->noteError( 'Diese Adresse war an der Gruppe bereits registriert, wurde aber deaktiviert.' );
					else if( $member->status == Model_Mail_Group_Member::STATUS_UNREGISTERED )
						$this->messenger->noteError( 'Diese Adresse war an der Gruppe bereits registriert, wurde aber bereits abgemeldet.' );
					else{
						$action	= $this->logic->registerMemberAction( 'deactivateAfterLeaving', $groupId, $memberId, $greeting );

						$mail	= new Mail_Info_Mail_Group_Member_Leaving( $this->env, array(
							'member'	=> $member,
							'group'		=> $group,
							'action'	=> $action,
						) );
						$receiver	= (object) array(
							'email'		=> $member->address,
							'username'	=> $member->title
						);
						$language	= $this->env->getLanguage()->getLanguage();
						$this->logicMail->appendRegisteredAttachments( $mail, $language );
						$this->logicMail->handleMail( $mail, $receiver, $language );
						$this->messenger->noteSuccess( 'Okay.' );
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

	public function view( $groupId ){
		$this->addData( 'group', $this->checkId( $groupId ) );
	}
}
