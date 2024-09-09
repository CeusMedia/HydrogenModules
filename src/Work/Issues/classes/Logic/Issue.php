<?php
/**
 *	@todo		Code Doc
 */

use CeusMedia\HydrogenFramework\Logic;

/**
 *	@todo		Code Doc
 */
class Logic_Issue extends Logic
{
	public const CHANGE_UNKNOWN			= 0;
	public const CHANGE_REPORTER		= 1;
	public const CHANGE_MANAGER			= 2;
	public const CHANGE_PROJECT			= 3;
	public const CHANGE_TYPE			= 4;
	public const CHANGE_SEVERITY		= 5;
	public const CHANGE_PRIORITY		= 6;
	public const CHANGE_STATUS			= 7;
	public const CHANGE_PROGRESS		= 8;
	public const CHANGE_DETAILS			= 9;
	public const CHANGE_NOTE			= 10;
	public const CHANGE_ATTACHMENT		= 11;
	public const CHANGE_PATCH			= 12;

	protected Logic_Project $logicProject;
	protected Model_User $modelUser;
	protected Model_Issue $modelIssue;
	protected Model_Issue_Note $modelIssueNote;
	protected Model_Issue_Change $modelIssueChange;

	/**
	 *	Return issue data object
	 *	@access		public
	 *	@param		string		$issueId		ID of issue
	 *	@param		boolean		$extended		Flag: extend issue by project, notes and changes
	 *	@return		object		Issue data object
	 *	@throws		OutOfRangeException			if issue ID is not valid
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function get( string $issueId, bool $extended = FALSE ): object
	{
		$issue		= $this->modelIssue->get( $issueId );											//  get issue
		if( !$issue )																				//  not found
			throw new OutOfRangeException( 'Invalid issue ID: '.$issueId );							//  quit with exception
		if( $extended ){
			$issue->reporter	= $this->modelUser->get( $issue->reporterId );
			if( $issue->managerId )
				$issue->manager	= $this->modelUser->get( $issue->managerId );
			$issue->project	= $this->logicProject->get( $issue->projectId );
			$issueNotes		= $this->modelIssueNote->getAll( ['issueId' => $issueId] );		//  get issue notes
			$issue->notes	= [];																//  prepare empty note list
			foreach( $issueNotes as $note ){														//  iterate issue notes
				$note->user	= $this->modelUser->get( $note->userId );								//  resolve note user
				$note->changes	= [];															//  prepare empty change list
				$issueChanges	= $this->modelIssueChange->getAll( ['noteId' => $note->issueNoteId] );	//  get issue changes
				foreach( $issueChanges as $change ){												//  iterate issue changes
					$note->changes[]	= $change;													//  note issue change
					$change->user		= $this->modelUser->get( $change->userId );					//  resolve change user
				}
				$issue->notes[]	= $note;															//  note issue note
			}
//			$issue->changes	= $this->modelIssueChange->getAll( ['issueId' => $issueId, 'noteId' => 0], ['timestamp' => 'ASC'] );
		}
		return $issue;																				//  return issue data object
	}

	/**
	 *	Returns map of all users participating on an issue.
	 *	This includes project members, note authors, change authors and former assigned users.
	 *	@access		public
	 *	@param		int|string			$issueId		ID of issue to get participating users for
	 *	@return		array<object>		Map of ordered participating users by ID
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function getParticipatingUsers( int|string $issueId ): array
	{
		$issue		= $this->get( $issueId, TRUE );
		$userIds	= [];																		//  prepare empty list of user IDs
		$usersProject	= $this->getProjectUsers( $issue->projectId );								//  get users of issue project
		foreach( $usersProject as $user )															//  iterate users of issue project
			$userIds[]	= (string) $user->userId;														//  note user ID
		$userIds[]	= (string) $issue->reporterId;														//  note user ID of issue reporter
		$userIds[]	= (string) $issue->managerId;														//  note user ID of issue manager
		foreach( $issue->notes as $note ){															//  iterate issue notes
			$userIds[]	= (string) $note->userId;														//  note user ID of note author
			foreach( $note->changes as $change ){													//  iterate issue changes
				$userIds[]	= (string) $change->userId;												//  note user ID of change author
				if( in_array( $change->type, [														//  issue reporter or manager has been changed
					Model_Issue_Change::TYPE_REPORTER,
					Model_Issue_Change::TYPE_MANAGER
				] ) ){
					$userIds[]	= (string) $change->from;												//  note user ID of old reporter or manager
					$userIds[]	= (string) $change->to;												//  note user ID of new reporter or manager
				}
			}
		}

		$users		= [];																			//  prepare empty result map
		$conditions	= ['userId' => array_unique( $userIds )];										//  reduce to unique user IDs
		$orders		= ['username' => 'ASC'];														//  order by username
		foreach( $this->modelUser->getAll( $conditions, $orders ) as $user ){						//  iterate found users
			$users[$user->userId]	= $user;														//  note user by its ID
			$user->isInProject	= FALSE;															//  set project assignment to false default
			$user->isWorker		= FALSE;															//  set worker status to false default
		}
		foreach( $usersProject as $user )															//  iterate users of issue project
			$users[$user->userId]->isInProject	= TRUE;												//  mark user as assigned to project
		foreach( $issue->notes as $note ){															//  iterate issue notes
			if( isset( $users[$note->userId] ) )
				$users[$note->userId]->isWorker	= TRUE;												//  mark user as worker
			foreach( $note->changes as $change )													//  note user ID of change author
				if( isset( $users[$change->userId] ) )
					$users[$change->userId]->isWorker	= TRUE;										//  mark user as worker
		}
		return $users;
	}

	/**
	 *	...
	 *	@access		public
	 *	@todo		support conditions ot orders
	 */
	public function getProjectUsers( int|string $projectId ): array
	{
		return $this->logicProject->getProjectUsers( $projectId );
	}

	/**
	 *	...
	 *	@access		public
	 */
	public function getUserProjects(): array
	{
		$userId		= Logic_Authentication::getInstance( $this->env )->getCurrentUserId();
		return $this->logicProject->getUserProjects( $userId, TRUE );
	}

	/**
	 *	@param		int|string		$issueId
	 *	@param		int|string		$currentUserId
	 *	@return		array
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function informAboutNew( int|string $issueId, int|string $currentUserId ): array
	{
		$users		= $this->getParticipatingUsers( $issueId );
		$issue		= $this->get( $issueId, TRUE );
		if( $issue->reporterId )
			$issue->reporter	= $users[$issue->reporterId];
		if( $issue->managerId )
			$issue->manager		= $users[$issue->managerId];

		if( array_key_exists( (string) $currentUserId, $users ) )
			unset( $users[$currentUserId] );

		if( count( $users ) ){
			/** @var Logic_Mail $logicMail */
			$logicMail		= Logic_Mail::getInstance( $this->env );
			if( $issue->projectId ){
				$userProjects		= $this->getUserProjects();
				$issue->project		= $userProjects[$issue->projectId];
			}
			foreach( $users as $user ){
				$mail		= new Mail_Work_Issue_New( $this->env, [
					'issue'		=> $issue,
					'user'		=> $user,
				] );
				$logicMail->handleMail( $mail, $user, 'de' );
			}
		}
		return $users;
	}

	/**
	 *	@param		int|string		$issueId
	 *	@param		int|string		$currentUserId
	 *	@return		array
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function informAboutChange( int|string $issueId, int|string $currentUserId ): array
	{
		$users		= $this->getParticipatingUsers( $issueId );
		$issue		= $this->get( $issueId, TRUE );
		if( $issue->reporterId )
			$issue->reporter	= $users[$issue->reporterId];
		if( $issue->managerId )
			$issue->manager		= $users[$issue->managerId];
		if( array_key_exists( (string) $currentUserId, $users ) )
			unset( $users[$currentUserId] );
		if( count( $users ) ){
			/** @var Logic_Mail $logicMail */
			$logicMail		= Logic_Mail::getInstance( $this->env );
			if( $issue->projectId ){
				$userProjects		= $this->getUserProjects();
				$issue->project		= $userProjects[$issue->projectId];
			}
			foreach( $users as $user ){
				$mail		= new Mail_Work_Issue_Change( $this->env, [
					'issue'		=> $issue,
					'user'		=> $user,
				] );
				$logicMail->handleMail( $mail, $user, 'de' );
			}
		}
		return $users;
	}

	/**
	 *	@param		int|string		$issueId
	 *	@param		int|string		$noteId
	 *	@param		$type
	 *	@param		$from
	 *	@param		$to
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function noteChange( int|string $issueId, int|string $noteId, $type, $from, $to ): string
	{
		$data	= [
			'issueId'	=> $issueId,
			'userId'	=> Logic_Authentication::getInstance( $this->env )->getCurrentUserId(),
			'noteId'	=> $noteId,
			'type'		=> $type,
			'from'		=> $from,
			'to'		=> $to,
			'timestamp'	=> time(),
		];
		return $this->modelIssueChange->add( $data );
	}

	/**
	 *	@param		int|string		$issueId
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( int|string $issueId ): bool
	{
		$this->modelIssueNote->removeByIndex( 'issueId', $issueId );
		$this->modelIssueChange->removeByIndex( 'issueId', $issueId );
		return $this->modelIssue->remove( $issueId );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->logicProject		= Logic_Project::getInstance( $this->env );
		$this->modelUser		= new Model_User( $this->env );										//  get model of users
		$this->modelIssue		= new Model_Issue( $this->env );									//  get model of issues
		$this->modelIssueNote	= new Model_Issue_Note( $this->env );								//  get model of issue notes
		$this->modelIssueChange	= new Model_Issue_Change( $this->env );								//  get model of issue changes
	}
}
