<?php
class Controller_Manage_Project extends CMF_Hydrogen_Controller{

	protected $logic;
	protected $logicMail;
	protected $messenger;
	protected $session;
	protected $modelProject;
	protected $modelProjectUser;
	protected $modelUser;
	protected $useMissions	= FALSE;
	protected $useCompanies	= FALSE;
	protected $useCustomers	= FALSE;
	protected $userId;

	public function __onInit(){
		$this->session			= $this->env->getSession();
		$this->messenger		= $this->env->getMessenger();
		$this->useMissions		= $this->env->getModules()->has( 'Work_Missions' );
		$this->useCompanies		= $this->env->getModules()->has( 'Manage_Projects_Companies' );
		$this->useCustomers		= $this->env->getModules()->has( 'Manage_Customers' );
		$this->userId			= $this->session->get( 'userId' );
		$this->roleId			= $this->session->get( 'roleId' );
		$this->logic			= new Logic_Project( $this->env );
		$this->logicMail		= new Logic_Mail( $this->env );
		$this->modelProject		= new Model_Project( $this->env );
		$this->modelProjectUser	= new Model_Project_User( $this->env );
		$this->modelUser		= new Model_User( $this->env );

		if( !$this->session->get( 'filter_manage_project_limit' ) )
			$this->session->set( 'filter_manage_project_limit', 10 );
	}

	static public function ___onUpdate( $env, $context, $module, $data = array() ){
		if( empty( $data['projectId'] ) )
			throw new InvalidArgumentException( 'Missing project ID' );
		$model	= new Model_Project( $env );
		$model->edit( $data['projectId'], array( 'modifiedAt' => time() ) );
	}

	static public function ___onListRelations( $env, $context, $module, $data ){
		if( empty( $data->projectId ) ){
			$message	= 'Hook "Project::___onListRelations" is missing project ID in data';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$modelProject	= new Model_Project( $env );
		if( !( $project = $modelProject->get( $data->projectId ) ) ){
			$message	= 'Hook "Work_Missions::___onListProjectRelations": Invalid project ID.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}

		$modelUser			= new Model_User( $env );
		$modelProjectUser	= new Model_Project_User( $env );
		$projectUsers		= $modelProjectUser->getAllByIndex( 'projectId', $data->projectId );
		$list				= array();
		$iconUser			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-user' ) );
		foreach( $projectUsers as $projectUser ){
//			if( $projectUser->userId === $env->getSession()->get( 'userId' ) )
//				continue;
			if( ( $user = $modelUser->get( $projectUser->userId ) ) ){
				$fullname	= '('.$user->firstname.' '.$user->surname.')';
				$fullname	= UI_HTML_Tag::create( 'small', $fullname, array( 'class' => 'muted' ) );
				$list[]		= (object) array(
					'id'		=> $projectUser->userId,
					'label'		=> $iconUser.'&nbsp;'.$user->username.'&nbsp;'.$fullname,
				);
			}
		}
		View_Helper_ItemRelationLister::enqueueRelations(
			$data,																					//  hook content data
			$module,																				//  module called by hook
			'relation',																				//  relation type: entity or relation
			$list,																					//  list of related items
			'Projekt-Teilnehmer',																	//  label of type of related items
			'Manage_User',																			//  controller of entity
			'edit'																					//  action to view or edit entity
		);
	}

	public function add(){
		$request		= $this->env->getRequest();
		$words			= (object) $this->getWords( 'add' );

		if( $request->has( 'save') ){
			$title		= $request->get( 'title' );
			if( !strlen( $title ) )
				$this->messenger->noteError( $words->msgTitleMissing );
			if( $this->modelProject->count( array( 'title' => $title ) ) )
				$this->messenger->noteError( $words->msgTitleExisting, $title );
			if( $this->messenger->gotError() )
				return;

			$data				= $request->getAll();
			$data['createdAt']	= time();
			$projectId			= $this->modelProject->add( $data, FALSE );

			if( 1 || !$this->env->getAcl()->hasFullAccess( $this->session->get( 'roleId' ) ) ){
				$this->modelProjectUser->add( array(
					'projectId'		=> $projectId,
					'userId'		=> $this->userId,
				) );
			}
			$this->messenger->noteSuccess( $words->msgSuccess );
			$this->restart( './manage/project/edit/'.$projectId );
		}
//		$this->addData( 'filterStatus', $this->session->get( 'filter_manage_project_status' ) );
//		$this->addData( 'filterOrder', $this->session->get( 'filter_manage_project_order' ) );
//		$this->addData( 'filterDirection', $this->session->get( 'filter_manage_project_direction' ) );
	}

	public function acceptInvite( $projectId ){
		$indices	= array(
			'projectId'	=> $projectId,
			'userId'	=> $this->userId,
			'status'	=> 0,
		);
		$relation	= $this->modelProjectUser->getByIndices( $indices );
		if( !$relation ){
			$this->messenger->noteError( 'Keine Einladung zu diesem Projekt vorhanden.' );
		}
		else{
			$this->modelProjectUser->edit( $relation->projectUserId, array(
				'status'		=> 1,
				'modifiedAt'	=> time(),
			) );
			$this->messenger->noteSuccess( 'Die Einladung wurde zu einer Mitgliedschaft am Projekt umgewandelt.' );
		}
		$this->restart( NULL, TRUE );
	}

	public function declineInvite( $projectId ){
		$indices	= array(
			'projectId'	=> $projectId,
			'userId'	=> $this->userId,
			'status'	=> 0,
		);
		$relation	= $this->modelProjectUser->getByIndices( $indices );
		if( !$relation ){
			$this->messenger->noteError( 'Keine Einladung zu diesem Projekt vorhanden.' );
		}
		else{
			$this->modelProjectUser->edit( $relation->projectUserId, array(
				'status'		=> -1,
				'modifiedAt'	=> time(),
			) );
			$this->messenger->noteSuccess( 'Die Einladung zum Projekt wurde abgelehnt.' );
		}
		$this->restart( NULL, TRUE );
	}

	public function addUser( $projectId, $userId = NULL ){
		$request		= $this->env->getRequest();
		$userId			= $userId ? $userId : $request->get( 'userId' );
		$forwardTo		= $request->get( 'forwardTo' );
		$words			= (object) $this->getWords( 'edit-panel-users' );
		$project		= $this->modelProject->get( (int) $projectId );
		if( !$project ){
			$this->messenger->noteError( $words->msgInvalidProject );
		}
		else if( (int) $userId > 0 ){
			$user		= $this->modelUser->get( $userId );
			if( !$user ){
				$this->messenger->noteError( $words->msgInvalidUser );
			}
			else{
				$this->modelProjectUser->add( array(
					'projectId'		=> (int) $projectId,
					'creatorId'		=> $this->userId,
					'userId'		=> (int) $userId,
					'status'		=> 1,
					'createdAt'		=> time()
				) );
				$this->messenger->noteSuccess( $words->msgUserAdded, $user->username, $project->title );

				$language		= $this->env->getLanguage();
				foreach( $this->logic->getProjectUsers( $projectId ) as $member ){
					if( $member->userId !== $this->userId ){
						$user	= $this->modelUser->get( $member->userId );
						$data	= array( 'project' => $project, 'user' => $user );
						$mail	= new Mail_Manage_Project_Members( $this->env, $data, FALSE );
						$this->logicMail->handleMail( $mail, $user, $language->getLanguage() );
					}
				}

				if( $forwardTo )
					$this->restart( './'.$forwardTo );
			}
		}
		$this->restart( 'edit/'.$projectId, TRUE );
	}

	protected function checkProject( $projectId, $checkMembership = TRUE ){
		$project		= $this->modelProject->get( $projectId );
		if( !$project ){
			$this->messenger->noteError( 'Invalid project. Redirection to index.' );
			$this->restart( NULL, TRUE );
		}
		if( $checkMembership ){
			$fullAccess	= $this->env->get( 'acl' )->hasFullAccess( $this->roleId );
			$isMember	= $this->modelProjectUser->getByIndices( array(
				'projectId'	=> $projectId,
				'userId'	=> $this->userId,
			) );
			if( !$isMember && !$fullAccess ){
				$this->messenger->noteError( 'You cannot access this project. Redirection to index.' );
				$this->restart( NULL, TRUE );
			}
		}
		return $project;
	}

	public function edit( $projectId ){
		$request		= $this->env->getRequest();
		$words			= (object) $this->getWords( 'edit' );
		$project		= $this->checkProject( $projectId );
		if( !$project ){
			$this->messenger->noteError( $words->msgInvalidId );
			$this->restart( NULL, TRUE );
		}
		if( $request->has( 'save') ){
			$title		= $request->get( 'title' );
			if( !strlen( $title ) )
				$this->messenger->noteError( $words->msgTitleMissing );
			$found	= $this->modelProject->getByIndex( 'title', $title );
			if( $found && $found->projectId != $projectId )
				$this->messenger->noteError( $words->msgTitleExisting, $title );
			if( $this->messenger->gotError() )
				return;
			$data				= $request->getAll();
			$data['modifiedAt']	= time();
			$this->modelProject->edit( $projectId, $data , FALSE );
			$this->messenger->noteSuccess( $words->msgSuccess );

			$language		= $this->env->getLanguage();
			foreach( $this->logic->getProjectUsers( $projectId ) as $member ){
				if( $member->userId !== $this->userId ){
					$user	= $this->modelUser->get( $member->userId );
					$data	= array( 'project' => $project, 'user' => $user );
					$mail	= new Mail_Manage_Project_Changed( $this->env, $data, FALSE );
					$this->logicMail->handleMail( $mail, $user, $language->getLanguage() );
				}
			}

			$this->restart( './manage/project/edit/'.$projectId );
		}

		$relations	= $this->modelProjectUser->getAllByIndex( 'projectId', $projectId );

		$users		= array();
		$conditions	= array( 'status' => '>0' );
		$orders		= array( 'username' => 'ASC' );
		foreach( $this->modelUser->getAll( $conditions, $orders ) as $user )
			$users[$user->userId]	= $user;

		$projectUsers	= array();
		foreach( $relations as $relation ){
			if( empty( $users[$relation->userId] ) )
				$this->modelProjectUser->removeByIndices( array(
					'projectId'	=> $projectId,
					'userId'	=> $relation->userId
				) );
			else
				$projectUsers[$relation->userId]	= $users[$relation->userId];
		}

		if( $this->env->getModules()->has( 'Work_Missions' ) ){
			$modelMission	= new Model_Mission( $this->env );
			$missions		= $modelMission->getAllByIndex( 'projectId', $projectId );
			$this->addData( 'missions', $missions );
		}

		$this->addData( 'users', $users );
		$this->addData( 'project', $project );
		$this->addData( 'projectUsers', $projectUsers );
		$this->addData( 'canEdit', $this->env->getAcl()->has( 'manage_project', 'edit' ) );
		$this->addData( 'canRemove', $this->env->getAcl()->has( 'manage_project', 'remove' ) );
		if( $this->useCompanies ){
			$modelCompany			= new Model_Company( $this->env );
			$modelProjectCompany	= new Model_Project_Company( $this->env );
			$this->addData( 'companies', $modelCompanies->getAll() );				//   @todo: order!
			$conditions		= array( 'projectId' => $project->projectId );
			$this->addData( 'projectCompanies', $modelProjectCompanies->get( $conditions ) );	//   @todo: order!
		}
		if( $this->useCustomers ){
			$modelCustomer	= new Model_Customer( $this->env );
			$modelCustomer->getAll( array( 'userId' => $this->userId ), array( 'title' => 'ASC' ) );
		}
//		$this->addData( 'filterStatus', $this->session->get( 'filter_manage_project_status' ) );
//		$this->addData( 'filterOrder', $this->session->get( 'filter_manage_project_order' ) );
//		$this->addData( 'filterDirection', $this->session->get( 'filter_manage_project_direction' ) );
	}

	public function filter( $mode = NULL ){
		$request		= $this->env->getRequest();
		if( $mode === "reset" )
			foreach( array_keys( $this->session->getAll( 'filter_manage_project_' ) ) as $key )
				$this->session->remove( 'filter_manage_project_'.$key );
//		if( $request->has( 'id' ) )
			$this->session->set( 'filter_manage_project_id', $request->get( 'id' ) );
//		if( $request->has( 'query' ) )
			$this->session->set( 'filter_manage_project_query', $request->get( 'query' ) );
//		if( $request->has( 'status' ) )
			$this->session->set( 'filter_manage_project_status', $request->get( 'status' ) );
//		if( $request->has( 'priority' ) )
			$this->session->set( 'filter_manage_project_priority', $request->get( 'priority' ) );
//		if( $request->has( 'user' ) )
			$this->session->set( 'filter_manage_project_user', $request->get( 'user' ) );
//		if( $request->has( 'order' ) )
			$this->session->set( 'filter_manage_project_order', $request->get( 'order' ) );

		if( $request->has( 'direction' ) )
			$this->session->set( 'filter_manage_project_direction', $request->get( 'direction' ) );
		if( $request->has( 'limit' ) )
			$this->session->set( 'filter_manage_project_limit', max( 1, $request->get( 'limit' ) ) );
		if( $this->session->get( 'filter_manage_project_order' ) === NULL )
			$this->session->set( 'filter_manage_project_order', 'title' );
		if( $this->session->get( 'filter_manage_project_direction' ) === NULL )
			$this->session->set( 'filter_manage_project_direction', 'ASC' );
		$this->session->set( 'filter_manage_project_page', 0 );
		$this->restart( NULL, TRUE );
	}

	protected function getWorkersOfMyProjects(){
		return $this->logic->getCoworkers( $this->userId );
	}

	public function index( $page = NULL ){
//		$this->env->getCaptain()->callHook( 'Project', 'update', $this, array( 'projectId' => '43' ) );
		if( $page !== NULL ){																	//  page set as argument
			$this->session->set( 'filter_manage_project_page', $page );							//  store page in session (will be validated later)
			if( $page === "0" )																	//  page was set to 0 explicitly
				$this->restart( NULL, TRUE );													//  redirect to nicer URI
		}
		else{																					//  no page as argument
			if( preg_match( "@manage/project/[0-9]+$@", getEnv( 'HTTP_REFERER' ) ) )			//  last request was index, too
				$this->session->set( 'filter_manage_project_page', 0 );							//  assume first page and store in session
			$page	= (int) $this->session->get( 'filter_manage_project_page' );				//  get page from session
		}
		if( $this->useMissions )
			$modelMission	= new Model_Mission( $this->env );

		if( !$this->modelProjectUser->countByIndex( 'userId', $this->userId ) ){
			if( !$this->env->getAcl()->hasFullAccess( $this->session->get( 'roleId' ) ) ){
				$words		= (object) $this->getWords( 'index' );
				$this->messenger->noteNotice( $words->msgErrorNoProjects );
				$this->restart( 'add', TRUE );
			}
		}

		$filterId			= $this->session->get( 'filter_manage_project_id' );
		$filterQuery		= $this->session->get( 'filter_manage_project_query' );
		$filterStatus		= $this->session->get( 'filter_manage_project_status' );
		$filterPriority		= $this->session->get( 'filter_manage_project_priority' );
		$filterUser			= $this->session->get( 'filter_manage_project_user' );
		$filterOrder		= $this->session->get( 'filter_manage_project_order' );
		$filterDirection	= $this->session->get( 'filter_manage_project_direction' );
		$filterLimit		= $this->session->get( 'filter_manage_project_limit' );
		if( !is_array( $filterStatus ) )
			$filterStatus	= array();
		if( !is_array( $filterPriority ) )
			$filterPriority	= array();
		if( !is_array( $filterUser ) )
			$filterUser		= array();

		$conditions	= array();
		if( !$this->env->getAcl()->hasFullAccess( $this->session->get( 'roleId' ) ) ){
			$projects	= array();
			foreach( $this->modelProjectUser->getAllByIndex( 'userId', $this->userId ) as $relation )
				$projects[$relation->projectId]	= NULL;
			$conditions['projectId']	= array_keys( $projects );
		}

		if( (int) $filterId > 0 )
			$conditions['projectId']	= array( $filterId );
		else{
			if( strlen( trim( $filterQuery ) ) ){
				$projectIds		= array();
				$filters	= array(
					"title LIKE '%".$filterQuery."%'",
					"description LIKE '%".$filterQuery."%'",
				);
				$query	= "SELECT * FROM ".$this->modelProject->getName()." WHERE ".join( " OR ", $filters )." LIMIT 1000";
				foreach( $this->env->getDatabase()->query( $query ) as $result )
					$projectIds[]	= $result['projectId'];
				if( isset( $conditions['projectId'] ) )
					$conditions['projectId']	= array_intersect( $conditions['projectId'], $projectIds );
				else
					$conditions['projectId']	= $projectIds;
			}
			if( $filterUser ){
				$projectIds	= array();
				foreach( $this->modelProjectUser->getAll( array( 'userId' => $filterUser ) ) as $relation )
					$projectIds[]	= $relation->projectId;
				if( isset( $conditions['projectId'] ) )
					$conditions['projectId']	= array_intersect( $conditions['projectId'], $projectIds );
				else
					$conditions['projectId']	= $projectIds;
			}
		}
		if( $filterStatus )
			$conditions['status']	= $filterStatus;
		if( $filterPriority )
			$conditions['priority']	= $filterPriority;
		if( isset( $conditions['projectId'] ) && !$conditions['projectId'] )
			$conditions['projectId'] = array( 0 );

		$orders	= array();
		if( !( $filterOrder && $filterDirection ) ){
			$filterOrder		= "title";
			$filterDirection	= "ASC";
		}
		$orders[$filterOrder]	= $filterDirection;

		$total	= $this->modelProject->count( $conditions );
		if( $page * $filterLimit > $total )
			$this->restart( '0', TRUE );
//		$page	= max( 0, min( floor( $total / $filterLimit ), $page ) );
		$limit	= $this->session->get( 'filter_manage_project_limit' );
		$limits	= array( $page * $filterLimit, $filterLimit );

		$projects	= array();
		foreach( $this->modelProject->getAll( $conditions, $orders, $limits ) as $project ){
			$projects[$project->projectId]	= $project;
			$project->users	= $this->modelProjectUser->getAllByIndex( 'projectId', $project->projectId );
			foreach( $project->users as $nr => $projectUser )
				$project->users[$nr]	= $this->modelUser->get( $projectUser->userId );
			if( $this->useMissions )
				$project->missions	= $modelMission->countByIndex( 'projectId', $project->projectId );
		}
		$this->addData( 'page', $page );
		$this->addData( 'total', $total );
		$this->addData( 'projects', $projects );
		$this->addData( 'users', $this->logic->getCoworkers( $this->userId ) );
		$this->addData( 'filterId', $filterId );
		$this->addData( 'filterQuery', $filterQuery );
		$this->addData( 'filterStatus', $filterStatus );
		$this->addData( 'filterPriority', $filterPriority );
		$this->addData( 'filterUser', $filterUser );
		$this->addData( 'filterOrder', $filterOrder );
		$this->addData( 'filterDirection', $filterDirection );
		$this->addData( 'filterLimit', $filterLimit );
		$this->addData( 'canAdd', $this->env->getAcl()->has( 'manage_project', 'add' ) );
		$this->addData( 'canFilter', $this->env->getAcl()->has( 'manage_project', 'filter' ) );
		$this->addData( 'canEdit', $this->env->getAcl()->has( 'manage_project', 'edit' ) );
	}

	public function removeUser( $projectId, $userId ){
		$project		= $this->checkProject( $projectId );
		$words			= (object) $this->getWords( 'edit-panel-users' );
		$numberUsers	= 0;																//  prepare user counter
		$relations		= $this->modelProjectUser->getAllByIndex( 'projectId', $projectId );			//  get project user relations
		$user			= NULL;
		foreach( $relations as $relation ){													//  iterate relations
			$relatedUser	= $this->modelUser->get( $relation->userId );							//  get user from relation
			$numberUsers	+= ( $relatedUser && $relatedUser->status > 0 ) ? 1 : 0;		//  count only existing and active users
			if( $relatedUser->userId === $userId )
				$user	= $relatedUser;
		}
		if( $numberUsers < 2 ){
			$this->messenger->noteError( $words->msgCannotRemoveLastUser );
		}
		else if( !$user ){
			$this->messenger->noteError( $words->msgInvalidUser );
		}
		else{
			$this->modelProjectUser->removeByIndices( array(
				'projectId'		=> $projectId,
				'userId'		=> $userId
			) );
			$this->messenger->noteSuccess( $words->msgUserRemoved, $user->username, $project->title );

			$language		= $this->env->getLanguage();
			foreach( $this->logic->getProjectUsers( $projectId ) as $member ){
				if( $member->userId !== $this->userId ){
					$user	= $this->modelUser->get( $member->userId );
					$data	= array( 'project' => $project, 'user' => $user );
					$mail	= new Mail_Manage_Project_Members( $this->env, $data, FALSE );
					$this->logicMail->handleMail( $mail, $user, $language->getLanguage() );
				}
			}
		}
		$this->restart( 'edit/'.$projectId, TRUE );
	}

	/**
	 *	@todo		finish: implement hook on other modules and test
	 */
	public function remove( $projectId, $confirmed = NULL ){
		$project	= $this->checkProject( $projectId );

		$this->addData( 'project', $project );
		if( $confirmed && 0 ){
			$dbc	= $this->env->getDatabase();
			try{
				$dbc->beginTransaction();

				$language		= $this->env->getLanguage();
				foreach( $this->logic->getProjectUsers( $projectId ) as $member ){
					if( $member->userId !== $this->userId ){
						$user	= $this->modelUser->get( $member->userId );
						$data	= array( 'project' => $project, 'user' => $user );
						$mail	= new Mail_Manage_Project_Removed( $this->env, $data, FALSE );
						$this->logicMail->handleMail( $mail, $user, $language->getLanguage() );
					}
				}

				$this->env->getCaptain()->callHook( 'Project', 'remove', $this, array( 'projectId' => $projectId ) );
				$this->messenger->noteNotice( 'Will remove: Project User' );
				$this->messenger->noteNotice( 'Will remove: Project' );
//				$this->modelProjectUser->removeByIndex( 'projectId', $projectId );
//				$this->modelProject->remove( $projectId );
				$dbc->commit();
				$this->messenger->noteSuccess( 'Project &quot;%s&quot; has been removed with all relations.' );
				$this->restart( NULL, TRUE );
			}
			catch( Exception $e ){
				$dbc->rollBack();
				$this->messenger->noteFailure( 'Action failed: '.$e->getMessage() );
				$this->restart( 'edit/'.$projectId, TRUE );
			}
		}
	}
}
?>
