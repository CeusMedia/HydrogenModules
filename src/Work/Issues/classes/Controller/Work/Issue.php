<?php

use CeusMedia\HydrogenFramework\Controller;

/**
 *	@todo		Code Doc
 */
class Controller_Work_Issue extends Controller
{
	protected array $filters	= [
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
	];

	protected Logic_Issue $logic;
	protected array $userProjects;
	protected ?string $userId;

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		if( !$this->userProjects && !$this->env->getRequest()->isAjax() )
			$this->restart( './manage/project/add?from=work/issue/add' );
		$request	= $this->env->getRequest();
		$managerId	= (int) $request->get( 'managerId' );
		// @todo activate after getDefaultProjectManager is implemented
/*		if( !$managerId ){
			$manager	= $this->getDefaultProjectManager( $request->get( 'projectId' ) );
			$managerId	= $manager ? $manager->userId : $managerId;
		}*/

		if( $request->has( 'save' ) ){
			$model		= new Model_Issue( $this->env );
			$data		= [
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
			];
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
		$this->addData( 'title', $request->get( 'title', '' ) );
		$this->addData( 'content', $request->get( 'content', '' ) );
		$this->addData( 'projects', $this->userProjects );
	}


	/**
	 *	@param		int|string		$issueId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string $issueId ): void
	{
		$request	= $this->env->getRequest();
		$issue		= $this->checkIssue( $issueId );
		if( $request->has( 'save' ) ){

//			$this->logic->informAboutChange( 50, $this->userId );

			$data		= [
//				'projectId'		=> (int) $request->get( 'projectId' ),
//				'type'			=> (int) $request->get( 'type' ),
//				'severity'		=> (int) $request->get( 'severity' ),
//				'status'		=> (int) $request->get( 'status' ),
//				'progress'		=> (int) $request->get( 'progress' ),
			//	'format'		=> $request->get( 'format' )							// @todo active on implementing format support
				'title'			=> trim( $request->get( 'title' ) ),
				'content'		=> trim( $request->get( 'content' ) ),
				'modifiedAt'	=> time()
			];
			$modelIssue			= new Model_Issue( $this->env );
			$modelIssue->edit( $issueId, $data, FALSE );								//  save data
//			$this->logic->informAboutChange( $issueId, $this->userId );
			$this->restart( './work/issue/edit/'.$issueId );							//  reload back into edit view
		}
//		$this->logic->informAboutChange( $issueId, $this->userId );
		$this->addData( 'issue', $this->logic->get( $issueId, TRUE ) );
		$this->addData( 'projects', $this->userProjects );
		$this->addData( 'users', $this->logic->getParticipatingUsers( $issueId ) );
	}

	/**
	 *	@param		int|string		$issueId
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function emerge( int|string $issueId ): void
	{
		$request	= $this->env->getRequest();
		$modelIssue		= new Model_Issue( $this->env );
		$modelNote		= new Model_Issue_Note( $this->env );
		$issue			= $modelIssue->get( $issueId );
		if( $request->has( 'save' ) ){
			$this->logic->informAboutChange( $issueId, $this->userId );
			$changeTypes	= [
				'reporterId'	=> Logic_Issue::CHANGE_REPORTER,
				'managerId'		=> Logic_Issue::CHANGE_MANAGER,
				'projectId'		=> Logic_Issue::CHANGE_PROJECT,
				'type'			=> Logic_Issue::CHANGE_TYPE,
				'severity'		=> Logic_Issue::CHANGE_SEVERITY,
				'priority'		=> Logic_Issue::CHANGE_PRIORITY,
				'status'		=> Logic_Issue::CHANGE_STATUS,
				'progress'		=> Logic_Issue::CHANGE_PROGRESS,
			];
			$changes		= [
				'modifiedAt'	=> time()
			];
			foreach( $changeTypes as $changeKey => $changeType ){
				$value	= $request->get( $changeKey );
				if( strlen( $value ) && $value != $issue->$changeKey )
					$changes[$changeKey]	= $value;
			}
			if( count( $changes ) > 1 || $request->get( 'note') ){
				$data	= [
					'issueId'	=> $issueId,
					'userId'	=> $this->userId,
					'note'		=> $request->get( 'note'),
					'timestamp'	=> time(),
				];
				$noteId	= $modelNote->add( $data );
				foreach( $changeTypes as $changeKey => $changeType ){
					$value	= $request->get( $changeKey );
					if( strlen( $value ) && $value != $issue->$changeKey ){
						$this->logic->noteChange( $issueId, $noteId, $changeType, $issue->$changeKey, $value );
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

	public function filter( $mode = NULL, $modeValue = 0 ): void
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

	public function index( $page = 0 ): void
	{
		if( !$this->userProjects && !$this->env->getRequest()->isAjax() )
			$this->restart( './manage/project/add?from=work/issue' );

		$session	= $this->env->getSession();
		$filters	= [];
		$setFilters	= $session->getAll( 'filter-issue-' );
		foreach( $setFilters as $key => $value ){
			if( $key == 'title' )
				$filters[$key] = '%'.str_replace( ' ', '%', trim( $value ) ).'%';
			else if( !in_array( $key, ['order', 'direction', 'limit', 'relation'] ) )
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

		$orders	= [];
		$order	= $session->get( 'filter-issue-order' );
		$dir	= $session->get( 'filter-issue-direction' );
		$limit	= $session->get( 'filter-issue-limit' );
		$limit	= $limit > 0 ? $limit : 15;
		if( $order && $dir )
			$orders	= [$order => $dir];

		$dir	= 'DESC';

		$modelIssue		= new Model_Issue( $this->env );
		$modelNote		= new Model_Issue_Note( $this->env );
		$modelChange	= new Model_Issue_Change( $this->env );
		$modelUser		= new Model_User( $this->env );

		$numberTypes	= [
			0	=> $modelIssue->count( array_merge( $filters, ['type' => Model_Issue::TYPE_ERROR] ) ),
			1	=> $modelIssue->count( array_merge( $filters, ['type' => Model_Issue::TYPE_TASK] ) ),
			2	=> $modelIssue->count( array_merge( $filters, ['type' => Model_Issue::TYPE_REQUEST] ) ),
//			3	=> $modelIssue->count( array_merge( $filters, ['type' => 3] ) ),
		];

		$numberStates	= [
			0	=> $modelIssue->count( array_merge( $filters, ['status'=> Model_Issue::STATUS_NEW] ) ),
			1	=> $modelIssue->count( array_merge( $filters, ['status' => Model_Issue::STATUS_ASSIGNED] ) ),
			2	=> $modelIssue->count( array_merge( $filters, ['status' => Model_Issue::STATUS_ACCEPTED] ) ),
			3	=> $modelIssue->count( array_merge( $filters, ['status' => Model_Issue::STATUS_PROGRESSING] ) ),
			4	=> $modelIssue->count( array_merge( $filters, ['status' => Model_Issue::STATUS_READY] ) ),
			5	=> $modelIssue->count( array_merge( $filters, ['status' => Model_Issue::STATUS_REOPENED] ) ),
			6	=> $modelIssue->count( array_merge( $filters, ['status' => Model_Issue::STATUS_CLOSED] ) ),
		];

		$numberPriorities	= [
			0	=> $modelIssue->count( array_merge( $filters, ['priority' => Model_Issue::PRIORITY_UNKNOWN] ) ),
			1	=> $modelIssue->count( array_merge( $filters, ['priority' => Model_Issue::PRIORITY_NECESSARY] ) ),
			2	=> $modelIssue->count( array_merge( $filters, ['priority' => Model_Issue::PRIORITY_IMPORTANT] ) ),
			3	=> $modelIssue->count( array_merge( $filters, ['priority' => Model_Issue::PRIORITY_NORMAL] ) ),
			4	=> $modelIssue->count( array_merge( $filters, ['priority' => Model_Issue::PRIORITY_DISPENSABLE] ) ),
			5	=> $modelIssue->count( array_merge( $filters, ['priority' => Model_Issue::PRIORITY_FUTILE] ) ),
//			6	=> $modelIssue->count( array_merge( $filters, ['priority' => 6] ) ),
		];

		$numberProjects	= [];
		foreach( $this->userProjects as $project ){
			$count	= $modelIssue->count( array_merge( $filters, ['projectId'	=> $project->projectId] ) );
			$numberProjects[$project->projectId]	= $count;
		}
		$this->addData( 'numberProjects', $numberProjects );

		$userIds	= [];
		$issues		= $modelIssue->getAll( $filters, $orders, [$limit * $page, (int) $limit] );
		foreach( $issues as $issue ){
			$issue->notes = $modelNote->getAllByIndex( 'issueId', $issue->issueId, ['timestamp' => 'ASC'] );
			$issue->changes	= $modelChange->getAllByIndex( 'issueId', $issue->issueId, ['timestamp' => 'ASC'] );
			$userIds[]	= $issue->reporterId;
			$userIds[]	= $issue->managerId;
			foreach( $issue->notes as $note )
				$userIds[]	= $note->userId;
			foreach( $issue->changes as $change )
				$userIds[]	= $change->userId;
		}

		$projects	= $this->userProjects;
/*		foreach( $projects as $project ){
			if( !$modelIssue->count( ['projectId' => $project->projectId] ) )
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

		$users	= [];
		if( $userIds )
			foreach( $modelUser->getAll( ['userId' => array_unique( $userIds )] ) as $user )
				$users[$user->userId]	= $user;

		$this->addData( 'users', $users );
	}


	// --  PROTECTED  --  //

	protected function __onInit(): void
	{
		$this->logic		= new Logic_Issue( $this->env );
		$this->userId		= Logic_Authentication::getInstance( $this->env )->getCurrentUserId();
		$this->userProjects	= $this->logic->getUserProjects();
	}

	/**
	 *	@param		int|string		$issueId
	 *	@param		bool			$strict
	 *	@return		object|NULL
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function checkIssue( int|string $issueId, bool $strict = TRUE ): ?object
	{
		$issue	= $this->logic->get( $issueId, TRUE );
		$users	= $this->logic->getParticipatingUsers( $issueId );
		if( $issue && $users ){
			if( array_key_exists( (string) $this->userId, $users ) )
				return $issue;
		}
		if( $strict )
			throw new RangeException( 'Invalid issue ID' );
		return NULL;
	}
}
