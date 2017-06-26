<?php
/**
 *	@todo		Code Doc
 */
/**
 *	@todo		Code Doc
 */
class Controller_Work_Issue extends CMF_Hydrogen_Controller{

	protected $filters	= array(
		'issueId',
		'reporterId',
		'managerId',
		'projectId',
		'type',
		'severity',
		'priority',
		'status',
		'title',
		'order',
		'direction',
		'limit',
		'relation',
	);

	protected $logic;
	protected $userProjects;
	protected $userId;

	public function __onInit(){
		$this->logic		= new Logic_Issue( $this->env );
		$this->userId		= $this->env->getSession()->get( 'userId' );
		$this->userProjects	= $this->logic->getUserProjects( $this->userId, TRUE );
	}

	static public function ___onRegisterTimerModule( $env, $context, $module, $data = array() ){
		$context->registerModule( (object) array(
			'moduleId'		=> 'Work_Issues',
			'typeLabel'		=> 'Problem',
			'modelClass'	=> 'Model_Issue',
			'linkDetails'	=> 'work/issue/edit/{id}',
		) );
	}

	static public function ___onRegisterDashboardPanels( $env, $context, $module, $data ){
		if( !$env->getAcl()->has( 'work/issue', 'ajaxRenderDashboardPanel' ) )
			return;
		$context->registerPanel( 'work-issues', array(
			'url'			=> 'work/issue/ajaxRenderDashboardPanel',
			'title'			=> 'offene Probleme',
			'heading'		=> 'offene Probleme',
			'icon'			=> 'fa fa-fw fa-exclamation',
			'rank'			=> 20,
		) );
	}

	static public function ___onProjectRemove( $env, $context, $module, $data ){
		$projectId	= $data['projectId'];
		$model		= new Model_Issue( $env );
		$logic		= new Logic_Issue( $env );
		foreach( $model->getAllByIndex( 'projectId', $projectId ) as $issue ){
			$logic->remove( $issue->issueId );
		}
	}

	static public function ___onListProjectRelations( $env, $context, $module, $data ){
		$modelProject	= new Model_Project( $env );
		if( empty( $data->projectId ) ){
			$message	= 'Hook "Work_Issues::___onListProjectRelations" is missing project ID in data.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		if( !( $project = $modelProject->get( $data->projectId ) ) ){
			$message	= 'Hook "Work_Issues::___onListProjectRelations": Invalid project ID.';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$data->activeOnly	= isset( $data->activeOnly ) ? $data->activeOnly : FALSE;
		$data->linkable		= isset( $data->linkable ) ? $data->linkable : FALSE;
		$language		= $env->getLanguage();
		$statusesActive	= array( 0, 1, 2, 3, 4, 5 );
		$list			= array();
		$modelIssue		= new Model_Issue( $env );
		$indices		= array( 'projectId' => $data->projectId );
		if( $data->activeOnly )
			$indices['status']	= $statusesActive;
		$orders			= array( 'type' => 'ASC', 'title' => 'ASC' );
		$issues			= $modelIssue->getAllByIndices( $indices, $orders );	//  ...
		$icons			= array(
			UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-exclamation', 'title' => 'Fehler' ) ),
			UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-wrench', 'title' => 'Aufgabe' ) ),
			UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-lightbulb-o', 'title' => 'Wunsch/Idee' ) ),
		);
		$words		= $language->getWords( 'work/issue' );
		foreach( $issues as $issue ){
			$icon		= $icons[$issue->type];
			$isOpen		= in_array( $issue->status, $statusesActive );
			$status		= '('.$words['states'][$issue->status].')';
			$status		= UI_HTML_Tag::create( 'small', $status, array( 'class' => 'muted' ) );
			$title		= $isOpen ? $issue->title : UI_HTML_Tag::create( 'del', $issue->title );
			$label		= $icon.'&nbsp;'.$title.'&nbsp;'.$status;
			$list[]		= (object) array(
				'id'		=> $data->linkable ? $issue->issueId : NULL,
				'label'		=> $label,
			);
		}
		View_Helper_ItemRelationLister::enqueueRelations(
			$data,																					//  hook content data
			$module,																				//  module called by hook
			'entity',																				//  relation type: entity or relation
			$list,																					//  list of related items
			$words['hook-relations']['label'],														//  label of type of related items
			'Work_Issue',																			//  controller of entity
			'edit'																					//  action to view or edit entity
		);
	}

	public function add(){
		if( !$this->userProjects && !$this->env->getRequest()->isAjax() )
			$this->restart( './manage/project/add?from=work/issue/add' );
		$request	= $this->env->request;
		$managerId	= (int) $request->get( 'managerId' );
		// @todo activate after getDefaultProjectManager is implemented
/*		if( !$managerId ){
			$manager	= $this->getDefaultProjectManager( $request->get( 'projectId' ) );
			$managerId	= $manager ? $manager->userId : $managerId;
		}*/

		if( $request->has( 'save' ) ){
			$model		= new Model_Issue( $this->env );
			$data		= array(
				'reporterId'	=> $this->userId,
				'managerId'		=> $managerId,
				'projectId'		=> (int) $request->get( 'projectId' ),
				'type'			=> (int) $request->get( 'type' ),
				'priority'		=> (int) $request->get( 'priority' ),
				'severity'		=> (int) $request->get( 'severity' ),
				'status'		=> 0,
			//	'format'		=> $request->get( 'format' )							// @todo active on implementing format support
				'title'			=> $request->get( 'title' ),
				'content'		=> trim( $request->get( 'content' ) ),
				'createdAt'		=> time(),
			);
			if( empty( $data['title'] ) )
				$this->env->getMessenger()->noteError( 'Der Titel fehlt.' );
			if( !$this->env->getMessenger()->gotError() ){
				$issueId	= $model->add( $data, FALSE );
				$this->logic->informAboutNew( $issueId, $this->userId );
				if( $issueId )
					$this->restart( './work/issue/edit/'.$issueId );
			}
		}

		$this->addData( 'type', $request->get( 'type' ) );
		$this->addData( 'priority', $request->get( 'priority' ) );
		$this->addData( 'projectId', $request->get( 'projectId' ) );
		$this->addData( 'title', $request->get( 'title' ) );
		$this->addData( 'content', $request->get( 'content' ) );
		$this->addData( 'projects', $this->userProjects );
	}

	public function ajaxRenderDashboardPanel( $panelId ){
		return UI_HTML_Tag::create( 'div', '...' );
	}

	protected function checkIssue( $issueId, $strict = TRUE ){
		$issue	= $this->logic->get( $issueId, TRUE );
		$users	= $this->logic->getParticitatingUsers( $issueId );
		if( $issue && $users ){
			$logicAuth	= Logic_Authentication::getInstance( $this->env );
			if( array_key_exists( $logicAuth->getCurrentUserId(), $users ) )
				return $issue;
		}
		if( $strict )
			throw new RangeException( 'Invalid issue ID' );
		return NULL;
	}

	public function edit( $issueId ){
		$request	= $this->env->request;
		$issue		= $this->checkIssue( $issueId );
		if( $request->has( 'save' ) ){

//			$this->logic->informAboutChange( 50, $this->userId );

			$data		= array(
//				'projectId'		=> (int) $request->get( 'projectId' ),
//				'type'			=> (int) $request->get( 'type' ),
//				'severity'		=> (int) $request->get( 'severity' ),
//				'status'		=> (int) $request->get( 'status' ),
//				'progress'		=> (int) $request->get( 'progress' ),
			//	'format'		=> $request->get( 'format' )							// @todo active on implementing format support
				'title'			=> trim( $request->get( 'title' ) ),
				'content'		=> trim( $request->get( 'content' ) ),
				'modifiedAt'	=> time()
			);
			$modelIssue			= new Model_Issue( $this->env );
			$modelIssue->edit( $issueId, $data, FALSE );								//  save data
//			$this->logic->informAboutChange( $issueId, $this->userId );
			$this->restart( './work/issue/edit/'.$issueId );							//  reload back into edit view
		}
//		$this->logic->informAboutChange( $issueId, $this->userId );
		$this->addData( 'issue', $this->logic->get( $issueId, TRUE ) );
		$this->addData( 'projects', $this->userProjects );
		$this->addData( 'users', $this->logic->getParticitatingUsers( $issueId ) );
	}

	public function emerge( $issueId ){
		$request	= $this->env->request;
		$modelIssue		= new Model_Issue( $this->env );
		$modelNote		= new Model_Issue_Note( $this->env );
		$issue			= $modelIssue->get( $issueId );
		if( $request->has( 'save' ) ){
			$this->logic->informAboutChange( $issueId, $this->userId );
			$changeTypes	= array(
				'reporterId'	=> Logic_Issue::CHANGE_REPORTER,
				'managerId'		=> Logic_Issue::CHANGE_MANAGER,
				'projectId'		=> Logic_Issue::CHANGE_PROJECT,
				'type'			=> Logic_Issue::CHANGE_TYPE,
				'severity'		=> Logic_Issue::CHANGE_SEVERITY,
				'priority'		=> Logic_Issue::CHANGE_PRIORITY,
				'status'		=> Logic_Issue::CHANGE_STATUS,
				'progress'		=> Logic_Issue::CHANGE_PROGRESS,
			);
			$changes		= array(
				'modifiedAt'	=> time()
			);
			foreach( $changeTypes as $changeKey => $changeType ){
				$value	= $request->get( $changeKey );
				if( strlen( $value ) && $value != $issue->$changeKey )
					$changes[$changeKey]	= $value;
			}
			if( count( $changes ) > 1 || $request->get( 'note') ){
				$data	= array(
					'issueId'	=> $issueId,
					'userId'	=> $this->userId,
					'note'		=> $request->get( 'note'),
					'timestamp'	=> time(),
				);
				$noteId	= $modelNote->add( $data );
				foreach( $changeTypes as $changeKey => $changeType ){
					$value	= $request->get( $changeKey );
					if( strlen( $value ) && $value != $issue->$changeKey ){
						$this->noteChange( $issueId, $noteId, $changeType, $issue->$changeKey, $value );
					}
				}
				$modelIssue->edit( $issueId, $changes, FALSE );
				$this->logic->informAboutChange( $issueId, $this->userId );

				$this->env->getMessenger()->noteSuccess( 'Die Veränderungen wurden gespeichert.' );
			}
			else
				$this->env->getMessenger()->noteError( 'Keine Veränderungen vorgenommen.' );
		}
		$this->restart( './work/issue/edit/'.$issueId );
	}

	public function export( $limit = 10, $offset = 0 ){
		$request	= $this->env->getRequest();
		if( !($filters	= $request->get( 'filters' ) ) )
			$filters	= array(
				'type'		=> 0,
				'status'	=> array( 1, 2, 3, 4, 5 ),
			);
		if( !($orders	= $request->get( 'orders' ) ) )
			$orders	= array(
				'priority'	=> 'ASC',
				'status'	=> 'ASC',
			);
		$modelIssue		= new Model_Issue( $this->env );
		$issues		= $modelIssue->getAll( $filters, $orders, array( $offset, $limit ) );
		print( json_encode( $issues ) );
		exit;
	}

	public function filter( $mode = NULL, $modeValue = 0 ){
		$session	= $this->env->getSession();
		switch( $mode )
		{
			case 'mode':
				$session->set( 'issue-filter-panel-mode', $modeValue );
				break;
			case 'reset':
				foreach( $this->filters as $filter )
					$session->remove( 'filter-issue-'.$filter );
				break;
			default:
				$request	= $this->env->getRequest();
				foreach( $this->filters as $filter )
				{
					$session->remove( 'filter-issue-'.$filter );
					if( ( $value = $this->compactFilterInput( $request->get( $filter ) ) ) )
						$session->set( 'filter-issue-'.$filter, $value );
				}
		}
		$this->restart( './work/issue' );
	}

	public function index( $page = 0 ){
		if( !$this->userProjects && !$this->env->getRequest()->isAjax() )
			$this->restart( './manage/project/add?from=work/issue' );

		$session	= $this->env->getSession();
		$filters	= array();
		$setFilters	= $session->getAll( 'filter-issue-' );
		foreach( $setFilters as $key => $value ){
			if( $key == 'title' )
				$filters[$key] = '%'.str_replace( ' ', '%', trim( $value ) ).'%';
			else if( !in_array( $key, array( 'order', 'direction', 'limit', 'relation' ) ) )
				$filters[$key] = $value;
		}
//		if( !isset( $filters['projectId'] ) || !$filters['projectId'] )
//			$filters['projectId']	= array_keys( $this->userProjects );
		if( isset( $setFilters['relation'] ) && $setFilters['relation'] ){
			if( $setFilters['relation'] == 1 )
				$filters['reporterId']	= $this->userId;
			else if( $setFilters['relation'] == 2 )
				$filters['managerId']	= $this->userId;
		}
		else if( !isset( $filters['projectId'] ) ){
			$filters['projectId']	= array_keys( $this->userProjects );
		}

		$orders	= array();
		$order	= $session->get( 'filter-issue-order' );
		$dir	= $session->get( 'filter-issue-direction' );
		$limit	= $session->get( 'filter-issue-limit' );
		$limit	= $limit > 0 ? $limit : 15;
		if( $order && $dir )
			$orders	= array( $order => $dir );

		$dir	= 'DESC';

		$modelIssue		= new Model_Issue( $this->env );
		$modelNote		= new Model_Issue_Note( $this->env );
		$modelChange	= new Model_Issue_Change( $this->env );
		$modelUser		= new Model_User( $this->env );

		$numberTypes	= array(
			0	=> $modelIssue->count( array_merge( $filters, array( 'type'	=> 0 ) ) ),
			1	=> $modelIssue->count( array_merge( $filters, array( 'type'	=> 1 ) ) ),
			2	=> $modelIssue->count( array_merge( $filters, array( 'type'	=> 2 ) ) ),
			3	=> $modelIssue->count( array_merge( $filters, array( 'type'	=> 3 ) ) ),
		);

		$numberStates	= array(
			0	=> $modelIssue->count( array_merge( $filters, array( 'status'	=> 0 ) ) ),
			1	=> $modelIssue->count( array_merge( $filters, array( 'status'	=> 1 ) ) ),
			2	=> $modelIssue->count( array_merge( $filters, array( 'status'	=> 2 ) ) ),
			3	=> $modelIssue->count( array_merge( $filters, array( 'status'	=> 3 ) ) ),
			4	=> $modelIssue->count( array_merge( $filters, array( 'status'	=> 4 ) ) ),
			5	=> $modelIssue->count( array_merge( $filters, array( 'status'	=> 5 ) ) ),
			6	=> $modelIssue->count( array_merge( $filters, array( 'status'	=> 6 ) ) ),
		);

		$numberPriorities	= array(
			0	=> $modelIssue->count( array_merge( $filters, array( 'priority'	=> 0 ) ) ),
			1	=> $modelIssue->count( array_merge( $filters, array( 'priority'	=> 1 ) ) ),
			2	=> $modelIssue->count( array_merge( $filters, array( 'priority'	=> 2 ) ) ),
			3	=> $modelIssue->count( array_merge( $filters, array( 'priority'	=> 3 ) ) ),
			4	=> $modelIssue->count( array_merge( $filters, array( 'priority'	=> 4 ) ) ),
			5	=> $modelIssue->count( array_merge( $filters, array( 'priority'	=> 5 ) ) ),
			6	=> $modelIssue->count( array_merge( $filters, array( 'priority'	=> 6 ) ) ),
		);

		$numberProjects	= array();
		foreach( $this->userProjects as $project ){
			$count	= $modelIssue->count( array_merge( $filters, array( 'projectId'	=> $project->projectId ) ) );
			$numberProjects[$project->projectId]	= $count;
		}
		$this->addData( 'numberProjects', $numberProjects );

		$userIds	= array();
		$issues		= $modelIssue->getAll( $filters, $orders, array( $limit * $page, $limit ) );
		foreach( $issues as $nr => $issue ){
			$issues[$nr]->notes = $modelNote->getAllByIndex( 'issueId', $issue->issueId, array( 'timestamp' => 'ASC' ) );
			$issues[$nr]->changes	= $modelChange->getAllByIndex( 'issueId', $issue->issueId, array( 'timestamp' => 'ASC' ) );
			$userIds[]	= $issue->reporterId;
			$userIds[]	= $issue->managerId;
			foreach( $issues[$nr]->notes as $note )
				$userIds[]	= $note->userId;
			foreach( $issues[$nr]->changes as $change )
				$userIds[]	= $change->userId;
		}

		$projects	= $this->userProjects;
/*		foreach( $projects as $project ){
			if( !$modelIssue->count( array( 'projectId' => $project->projectId ) ) )
				unset( $projects[$project->projectId] );
		}*/

		$this->addData( 'page', $page );
		$this->addData( 'total', $modelIssue->count() );
		$this->addData( 'number', $modelIssue->count( $filters ) );
		$this->addData( 'numberTypes', $numberTypes );
		$this->addData( 'numberStates', $numberStates );
		$this->addData( 'numberPriorities', $numberPriorities );
		$this->addData( 'numberFilters', count( $filters ) );
		$this->addData( 'issues', $issues );
		$this->addData( 'projects', $projects );

		$users	= array();
		if( $userIds )
			foreach( $modelUser->getAll( array( 'userId' => array_unique( $userIds ) ) ) as $user )
				$users[$user->userId]	= $user;

		$this->addData( 'users', $users );
	}

	protected function noteChange( $issueId, $noteId, $type, $from, $to ){
		return $this->logic->noteChange( $issueId, $noteId, $type, $from, $to );
	}

	public function search(){
		$request	= $this->env->getRequest();
		$terms		= explode( " ", trim( $request->get( 'term' ) ) );
		$modelIssue	= new Model_Issue( $this->env );
		$issues		= array();
		$ids		= array();
		foreach( $terms as $term ){
			$filters	= array( 'title' => '%'.$term.'%' );
			foreach( $modelIssue->getAll( $filters ) as $issue ){
				$issues[$issue->issueId]	= $issue;
				if( empty( $ids[$issue->issueId] ) )
					$ids[$issue->issueId]	= 0;
				$ids[$issue->issueId] ++;
			}
		}
		arsort( $ids );
		$list	= array();
		foreach( $ids as $id => $number )
			if( $number == count( $terms ) )
				$list[]	= $issues[$id];
		print( json_encode( $list ) );
		exit;
	}
}
?>
