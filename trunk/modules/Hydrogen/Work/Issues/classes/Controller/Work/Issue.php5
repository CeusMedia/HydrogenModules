<?php
class Controller_Work_Issue extends CMF_Hydrogen_Controller{

	const CHANGE_DETAILS		= 1;
	const CHANGE_MANAGER		= 2;
	const CHANGE_DEVELOPER		= 3;
	const CHANGE_TYPE			= 4;
	const CHANGE_SEVERITY		= 5;
	const CHANGE_PRIORITY		= 6;
	const CHANGE_STATUS			= 7;
	const CHANGE_PROGRESS		= 8;
	const CHANGE_NOTE			= 9;
	const CHANGE_ATTACHMENT		= 10;
	const CHANGE_PATCH			= 11;

	protected $filters	= array(
		'issueId',
		'projectId',
		'type',
		'severity',
		'priority',
		'status',
		'title',
		'order',
		'direction',
		'limit'
	);
	protected $useProjects	= FALSE;

	public function __onInit(){
		$this->useProjects	= $this->env->getModules()->has( 'Manage_Projects' );
	}

	protected function getUserProjects(){
		if( !$this->useProjects )
			return array();
		$userId			= $this->env->getSession()->get( 'userId' );
		$modelProject	= new Model_Project( $this->env );
		if( $this->env->getAcl()->hasFullAccess( $this->env->getSession()->get( 'roleId' ) ) )
			return $modelProject->getAll();
		return $modelProject->getUserProjects( $userId );
	}
	
	public function add(){
		$request	= $this->env->request;
		if( $request->has( 'save' ) ){
			$model		= new Model_Issue( $this->env );
			$data		= array(
				'reporterId'	=> $this->env->getSession()->get( 'userId' ),
				'projectId'		=> (int) $request->get( 'projectId' ),
				'type'			=> (int) $request->get( 'type' ),
				'severity'		=> (int) $request->get( 'severity' ),
				'status'		=> 0,
				'title'			=> $request->get( 'title' ),
				'content'		=> $request->get( 'content' ),
				'createdAt'		=> time(),
			);
			if( empty( $data['title'] ) )
				$this->env->getMessenger()->noteError( 'Der Titel fehlt.' );
			if( !$this->env->getMessenger()->gotError() ){
				$issueId	= $model->add( $data, FALSE );
				if( $issueId )
					$this->restart( './work/issue/edit/'.$issueId );
			}
		}

		$this->addData( 'type', $request->get( 'type' ) );
		$this->addData( 'priority', $request->get( 'priority' ) );
		$this->addData( 'projectId', $request->get( 'projectId' ) );
		$this->addData( 'title', $request->get( 'title' ) );
		$this->addData( 'content', $request->get( 'content' ) );
		$this->addData( 'projects', $this->getUserProjects() );
	}
	
	public function edit( $issueId ){
		$request	= $this->env->request;

		$modelIssue		= new Model_Issue( $this->env );
		$modelIssueNote	= new Model_Issue_Note( $this->env );
		$modelIssueChange	= new Model_Issue_Change( $this->env );
		$modelUser		= new Model_User( $this->env );

#		$users	= array();
#		foreach( $modelUser->getAll() as $user )
#			$users[$user->userId]	= $user;
#		$this->addData( 'users', $users );

		if( $request->has( 'save' ) ){
			$data		= array(
				'projectId'		=> (int) $request->get( 'projectId' ),
				'type'			=> (int) $request->get( 'type' ),
//				'severity'		=> (int) $request->get( 'severity' ),
//				'status'		=> (int) $request->get( 'status' ),
//				'progress'		=> (int) $request->get( 'progress' ),
				'title'			=> $request->get( 'title' ),
				'content'		=> $request->get( 'content' ),
				'modifiedAt'	=> time()
			);
			$modelIssue->edit( $issueId, $data, FALSE );
//			$this->restart( './work/issue' );
		}
		$issue			= $modelIssue->get( $issueId );

		$notes		= $modelIssueNote->getAllByIndex( 'issueId', $issueId, array( 'timestamp' => 'ASC' ) );
		foreach( $notes as $nr => $note ){
			$changes	= $modelIssueChange->getAllByIndex( 'noteId', $note->issueNoteId, array( 'type' => 'ASC' ) );
			$notes[$nr]->user		= $modelUser->get( $note->userId );
			$notes[$nr]->changes	= $changes;
			foreach( $changes as $nr => $change )
				$changes[$nr]->user	= $modelUser->get( $change->userId );
		}
		$issue->notes		= $notes;
		$issue->changes		= $modelIssueChange->getAll( array( 'issueId' => $issueId, 'noteId' => 0 ), array( 'timestamp' => 'ASC' ) );

		$issue->reporter	= $modelUser->get( $issue->reporterId );
		if( $issue->managerId )
			$issue->manager	= $modelUser->get( $issue->managerId );
		$this->addData( 'issue', $issue );
		$this->addData( 'projects', $this->getUserProjects() );
	}
	
	public function emerge( $issueId ){
		$request	= $this->env->request;
		$modelIssue		= new Model_Issue( $this->env );
		$modelNote		= new Model_Issue_Note( $this->env );
		$issue			= $modelIssue->get( $issueId );
		if( $request->has( 'save' ) ){

			$changeTypes	= array(
				'type'		=> self::CHANGE_TYPE,
				'severity'	=> self::CHANGE_SEVERITY,
				'priority'	=> self::CHANGE_PRIORITY,
				'status'	=> self::CHANGE_STATUS,
				'progress'	=> self::CHANGE_PROGRESS,
				'managerId'	=> self::CHANGE_MANAGER,
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
					'userId'	=> $this->env->getSession()->get( 'userId' ),
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
		die;
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
		$session	= $this->env->getSession();
		$filters	= array();
		foreach( $session->getAll() as $key => $value )
			if( preg_match( '/^filter-issue-/', $key ) ){
				$column	= preg_replace( '/^filter-issue-/', '', $key );
				if( $column == 'title' )
					$filters[$column] = '%'.str_replace( ' ', '%', trim( $value ) ).'%';
				else if( !in_array( $column, array( 'order', 'direction', 'limit' ) ) )
					$filters[$column] = $value;
			}

		$orders	= array();
		$order	= $session->get( 'filter-issue-order' );
		$dir	= $session->get( 'filter-issue-direction' );
		$limit	= $session->get( 'filter-issue-limit' );
		$limit	= $limit > 0 ? $limit : 10;
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

		if( $this->useProjects ){
			$numberProjects	= array();
			foreach( $this->getUserProjects() as $project )
				$numberProjects[$project->projectId]	= $modelIssue->count( array_merge( $filters, array( 'projectId'	=> $project->projectId ) ) );
			$this->addData( 'numberProjects', $numberProjects );
		}

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

		$this->addData( 'page', $page );
		$this->addData( 'total', $modelIssue->count() );
		$this->addData( 'number', $modelIssue->count( $filters ) );
		$this->addData( 'numberTypes', $numberTypes );
		$this->addData( 'numberStates', $numberStates );
		$this->addData( 'numberPriorities', $numberPriorities );
		$this->addData( 'numberFilters', count( $filters ) );
		$this->addData( 'issues', $issues );
		$this->addData( 'projects', $this->getUserProjects() );

		$users	= array();
		if( $userIds )
			foreach( $modelUser->getAll( array( 'userId' => array_unique( $userIds ) ) ) as $user )
				$users[$user->userId]	= $user;

		$this->addData( 'users', $users );
	}

	protected function noteChange( $issueId, $noteId, $type, $from, $to ){
		$model		= new Model_Issue_Change( $this->env );
		$data	= array(
			'issueId'	=> $issueId,
			'userId'	=> $this->env->getSession()->get( 'userId' ),
			'noteId'	=> $noteId,
			'type'		=> $type,
			'from'		=> $from,
			'to'		=> $to,
			'timestamp'	=> time(),
		);
		return $model->add( $data );
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
		die;
	}
}
?>
