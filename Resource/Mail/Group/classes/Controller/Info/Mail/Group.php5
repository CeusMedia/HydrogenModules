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
		if( $action->status != 0 ){
			$this->messenger->noteError( 'Action already taken.' );
			$this->restart( NULL );
		}
		try{
			$result	= $this->env->getModules()->callHook(
				'MailGroupAction',
				'activateAfterJoin',
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
			$this->modelAction->edit( $action->mailGroupActionId, array(
				'status'		=> Model_Mail_Group_Action::STATUS_HANDLED,
				'modifiedAt'	=> time(),
			) );
		}
		$this->restart( NULL, TRUE );
	}

	protected function handleIfAlreadyMember( $groupId, $email ){
		try{
			$member		= $this->logic->getGroupMemberByAddress( $groupId, $email );
			switch( $member->status ){
				case Model_Mail_Group_Member::STATUS_ACTIVATED:
					$msg	= 'Sie sind bereits an der Gruppe registriert und ihre Mitgliedschaft ist aktiv.';
					break;
				case Model_Mail_Group_Member::STATUS_REGISTERED:
					$msg	= 'Sie haben sich bereits registriert, aber die Zusage steht noch aus.';
					break;
				case Model_Mail_Group_Member::STATUS_DEACTIVATED:
					$msg	= 'Diese Adresse war an der Gruppe bereits registriert, wurde aber deaktiviert.';
					break;
				case Model_Mail_Group_Member::STATUS_UNREGISTERED:
					$msg	= 'Sie waren mit diesee Adresse bereits an der Gruppe registriert, haben Ihre Mitgliedschaft jedoch beendet.';
					break;
				default:
					$msg	= 'Unbekannter Zustand - bitte melden Sie sich bei der Verwaltung!';
			}
			$this->messenger->noteError( $msg );
			return FALSE;
		}
		catch( Exception $e ){}
		return TRUE;
	}

	protected function handleIfNotMember( $groupId, $email ){
		try{
			$member		= $this->logic->getGroupMemberByAddress( $groupId, $email );
			switch( $member->status ){
				case Model_Mail_Group_Member::STATUS_REGISTERED:
				case Model_Mail_Group_Member::STATUS_ACTIVATED:
					return TRUE;
					break;
				case Model_Mail_Group_Member::STATUS_DEACTIVATED:
					$msg	= 'Diese Adresse war an der Gruppe bereits registriert, wurde aber bereits deaktiviert.';
					break;
				case Model_Mail_Group_Member::STATUS_UNREGISTERED:
					$msg	= 'Ihre Mitgliedschaft in dieser Gruppe wurde bereits beendet.';
					break;
			}
			$this->messenger->noteError( $msg );
			return FALSE;
		}
		catch( Exception $e ){

		}
		return TRUE;
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
//			'status'		=> Model_Mail_Group::STATUS_ACTIVATED,
//			'visibility'	=> Model_Mail_Group::VISIBILITY_PUBLIC,
		);
		if( $filterType )
			$conditions['type']	= $filterType;
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
					else if( $member->status == Model_Mail_Group_Member::STATUS_REGISTERED )
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
				$action	= $this->logic->registerMemberAction( 'activateAfterJoin', $groupId, $memberId, $greeting );
				$member	= $this->logic->getGroupMember( $memberId, FALSE );
				$this->logic->sendMailAfterJoin( $group, $member, $action );
				$this->messenger->noteSuccess( 'Okay.' );
				$this->restart( NULL, TRUE );
			}
		}
		$group = (int) $groupId > 0 ? $this->checkId( $groupId ) : NULL;
		$this->addData( 'group', $group );
		$this->addData( 'data', (object) $this->request->getAll() );
	}

	public function register( $groupId = NULL ){
		if( $this->request->has( 'save' ) ){
			$address	= trim( $this->request->get( 'address' ) );
			$greeting	= trim( $this->request->get( 'message' ) );
			$failed		= FALSE;
			$group		= $this->checkGroupByIdOrAddress( $groupId > 0 ? (int) $groupId : $address );
			if( !$group ){
				$this->messenger->noteError( 'Die gewählte Gruppe existent nicht oder nicht mehr.' );
				$failed	= TRUE;
			}
			if( !( $email = trim( $this->request->get( 'email' ) ) ) ){
				$this->messenger->noteError( 'Keine E-Mail-Adresse angegeben.' );
				$failed	= TRUE;
			}
			if( !$failed )
				$failed	= !$this->handleIfAlreadyMember( $group->mailGroupId, $email );
			if( !$failed ){
				$name		= trim( $this->request->get( 'name' ) );
				$message	= trim( $this->request->get( 'message' ) );
				$memberId	= $this->modelMember->add( array(
					'mailGroupId'	=> $group->mailGroupId,
					'roleId'		=> $group->defaultRoleId,
					'status'		=> Model_Mail_Group_Member::STATUS_ACTIVATED,
					'address'		=> $email,
					'title'			=> strlen( $name ) ? $name : NULL,
					'createdAt'		=> time(),
					'modifiedAt'	=> time(),
				) );
				$this->logic->informGroupManagerAboutRegisteredMember( $groupId, $memberId, $greeting );
//				$this->logic->informGroupMembersAboutNewMember( $group->mailGroupId, $memberId, $greeting );
				$this->messenger->noteSuccess( 'Okay.' );
				$this->restart( NULL, TRUE );
			}
		}
		$group = (int) $groupId > 0 ? $this->checkId( $groupId ) : NULL;
		$this->addData( 'group', $group );
		$this->addData( 'data', (object) $this->request->getAll() );
	}

	public function leave( $groupId = NULL ){
		if( $this->request->has( 'save' ) ){
			$address	= trim( $this->request->get( 'address' ) );
			$greeting	= trim( $this->request->get( 'message' ) );
			$failed		= FALSE;
			$group		= $this->checkGroupByIdOrAddress( $groupId > 0 ? (int) $groupId : $address );
			if( !$group ){
				$this->messenger->noteError( 'Die gewählte Gruppe existent nicht oder nicht mehr.' );
				$failed	= TRUE;
			}
			if( !( $email = trim( $this->request->get( 'email' ) ) ) ){
				$this->messenger->noteError( 'Keine E-Mail-Adresse angegeben.' );
				$failed	= TRUE;
			}
			if( !$failed )
				$failed	= !$this->handleIfNotMember( $group->mailGroupId, $email );
			if( !$failed ){
				$name		= trim( $this->request->get( 'name' ) );
				$message	= trim( $this->request->get( 'message' ) );
				$memberId	= $this->modelMember->add( array(
					'mailGroupId'	=> $group->mailGroupId,
					'roleId'		=> $group->defaultRoleId,
					'status'		=> Model_Mail_Group_Member::STATUS_ACTIVATED,
					'address'		=> $email,
					'title'			=> strlen( $name ) ? $name : NULL,
					'createdAt'		=> time(),
					'modifiedAt'	=> time(),
				) );
				$this->logic->informGroupManagerAboutJoinedMember( $groupId, $memberId, $greeting );
				$this->logic->informGroupMembersAboutNewMember( $group->mailGroupId, $memberId, $greeting );
				$this->messenger->noteSuccess( 'Okay.' );
				$this->restart( NULL, TRUE );
			}
		}
		$group = (int) $groupId > 0 ? $this->checkId( $groupId ) : NULL;
		$this->addData( 'group', $group );
		$this->addData( 'data', (object) $this->request->getAll() );


/*

		$group = (int) $groupId > 0 ? $this->checkId( $groupId ) : NULL;
		if( $this->request->has( 'save' ) ){
			if( $group ){
//				...
			}
//			...
//			$this->restart( NULL, TRUE );
		}
		$this->addData( 'group', $group );
*/
/*		$this->checkId( $groupId );
		$addressGroup	= $this->request->get( 'address_group' );
		$addressMember	= $this->request->get( 'address_member' );
		if( $this->request->has( 'save' ) ){
			if( !( $group = $this->modelGroup->getByIndex( 'address', $addressGroup ) ) ){
				$this->messenger->noteError( 'Keine Gruppe mit dieser E-Mail-Adresse gefunden.' );
				$this->restart( 'unregister?address_member='.$addressMember, TRUE );
			}
			$indices	= array(
				'mailGroupId'	=> $group->mailGroupId,
				'address'		=> $addressMember
			);
			if( !( $member = $this->modelMember->getByIndices( $indices ) ) ){
				$this->messenger->noteError( 'Kein Mitglied mit dieser E-Mail-Adresse gefunden.' );
				$this->restart( 'unregister?address_group='.$addressGroup, TRUE );
			}
			$this->modelMember->remove( $member->mailGroupMemberId );
			$this->messenger->noteSuccess( 'Das Mitglied wurde aus der Gruppe entfernt.' );
			$this->restart( 'unregister', TRUE );
		}
		$groups	= $this->logic->getActiveGroups();
		$this->addData( 'groups', $groups );
		$this->addData( 'address_group', $addressGroup );
		$this->addData( 'address_member', $addressMember );*/
		$this->addData( 'data', (object) $this->request->getAll() );
	}
}
