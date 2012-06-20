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
		'type',
		'severity',
		'priority',
		'status',
		'title',
		'order',
		'direction',
		'limit'
	);

	public function add(){
		$request	= $this->env->request;
		if( $request->has( 'save' ) ){
			$model		= new Model_Issue( $this->env );
			$data		= array(
				'reporterId'	=> $this->env->getSession()->get( 'userId' ),
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
				$issueId	= $model->add( $data );
				if( $issueId )
					$this->restart( './work/issue/edit/'.$issueId );
			}
		}
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
				$modelIssue->edit( $issueId, $changes );
				$this->env->getMessenger()->noteSuccess( 'Die Veränderungen wurden gespeichert.' );
			}
			else
				$this->env->getMessenger()->noteError( 'Keine Veränderungen vorgenommen.' );
		}
		$this->restart( './work/issue/edit/'.$issueId );
	}
	
	public function edit( $issueId ){
		$request	= $this->env->request;

		$modelIssue		= new Model_Issue( $this->env );
		$modelIssueNote	= new Model_Issue_Note( $this->env );
		$modelIssueChange	= new Model_Issue_Change( $this->env );
		$modelUser		= new Model_User( $this->env );

		$users	= array();
		foreach( $modelUser->getAll() as $user )
			$users[$user->userId]	= $user;

		$this->addData( 'users', $users );

		if( $request->has( 'save' ) ){
			$data		= array(
				'type'		=> $request->get( 'type' ),
				'severity'	=> $request->get( 'severity' ),
				'status'	=> $request->get( 'status' ),
//				'progress'	=> $request->get( 'progress' ),
				'title'		=> $request->get( 'title' ),
				'content'	=> $request->get( 'content' ),
			);
			$modelIssue->edit( $issueId, $data );
//			$this->restart( './work/issue' );
		}
		$issue			= $modelIssue->get( $issueId );

		$notes		= $modelIssueNote->getAllByIndex( 'issueId', $issueId, array( 'timestamp' => 'ASC' ) );
		foreach( $notes as $nr => $note ){
			$changes	= $modelIssueChange->getAllByIndex( 'noteId', $note->issueNoteId, array( 'type' => 'ASC' ) );
			$notes[$nr]->user	= $users[$note->userId];
			$notes[$nr]->changes	= $changes;
			foreach( $changes as $nr => $change )
				$changes[$nr]->user	= $users[$change->userId];
		}
		$issue->notes		= $notes;
		$issue->changes	= $modelIssueChange->getAll( array( 'issueId' => $issueId, 'noteId' => 0 ), array( 'timestamp' => 'ASC' ) );

		$issue->reporter	= $users[$issue->reporterId];
		if( $issue->managerId )
			$issue->manager	= $users[$issue->managerId];
		$this->addData( 'issue', $issue );
	}

	public function filter( $mode = NULL, $modeValue = 0 )
	{
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

	public function index(){
		$session	= $this->env->getSession();
		$filters	= array();
		foreach( $session->getAll() as $key => $value )
			if( preg_match( '/^filter-issue-/', $key ) ){
				$column	= preg_replace( '/^filter-issue-/', '', $key );
				if( !in_array( $column, array( 'order', 'direction', 'limit' ) ) )
					$filters[$column] = $value;
			}

		$orders	= array();
		$order	= $session->get( 'filter-issue-order' );
		$dir	= $session->get( 'filter-issue-direction' );
		$limit	= $session->get( 'filter-issue-limit' );
		$limit	= $limit > 0 ? $limit : 10;
		if( $order && $dir )
			$orders	= array( $order => $dir );

		$modelIssue		= new Model_Issue( $this->env );
		$modelNote		= new Model_Issue_Note( $this->env );
		$modelChange	= new Model_Issue_Change( $this->env );
		$modelUser		= new Model_User( $this->env );

		$issues		= $modelIssue->getAll( $filters, $orders, array( 0, $limit ) );
		foreach( $issues as $nr => $issue ){
			$issues[$nr]->notes = $modelNote->getAllByIndex( 'issueId', $issue->issueId, array( 'timestamp' => 'ASC' ) );
			$issues[$nr]->changes	= $modelChange->getAllByIndex( 'issueId', $issue->issueId, array( 'timestamp' => 'ASC' ) );
		}
		$this->addData( 'issues', $issues );	


		$users	= array();
		foreach( $modelUser->getAll() as $user )
			$users[$user->userId]	= $user;

		$this->addData( 'users', $users );
	}
}
?>
