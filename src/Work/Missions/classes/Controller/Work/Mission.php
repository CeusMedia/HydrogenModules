<?php
/**
 *	Controller.
 *	@category		Hydrogen.Module
 *	@package		Work.Missions
 */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\ID;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\Net\HTTP\UploadErrorHandler;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Acl\Abstraction as AclResource;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;
use League\HTMLToMarkdown\HtmlConverter as HtmlToMarkdownConverter;

/**
 *	Controller.
 *	@category		Hydrogen.Module
 *	@package		Work.Missions
 *	@todo			implement
 *	@todo			code documentation
 *	@todo			1300 lines of code !?
 *	@todo			extract AJAX actions to own controller class
 */
class Controller_Work_Mission extends Controller
{
	public function help( $topic = NULL ): void
	{
		$this->addData( 'topic', (string) $topic );
	}

	protected HttpRequest $request;
	protected Dictionary $session;
	protected MessengerResource $messenger;
	protected AclResource $acl;
	protected Logic_Work_Mission $logic;
	protected Logic_Authentication $logicAuth;
	protected ?Logic_Database_Lock $lock	= NULL;
	protected Logic_Project $logicProject;
	protected Model_Mission $model;
	protected array $userProjects;
	/** @var array<int|string,Entity_User> $userMap */
	protected array $userMap;
	protected bool $isEditor;
	protected bool $isViewer;
	protected bool $useIssues;
	protected bool $useTimer;
	protected string $filterKeyPrefix		= 'filter.work.mission.';
	protected string $userId				= '0';
	protected string $userRoleId			= '0';
	protected string $contentFormat;

	protected array $defaultFilterValues	= [
		'mode'		=> 'now',
		'states'	=> [
			Model_Mission::STATUS_NEW,
			Model_Mission::STATUS_ACCEPTED,
			Model_Mission::STATUS_PROGRESS,
			Model_Mission::STATUS_READY
		],
		'priorities'	=> [
			Model_Mission::PRIORITY_NONE,
			Model_Mission::PRIORITY_HIGHEST,
			Model_Mission::PRIORITY_HIGH,
			Model_Mission::PRIORITY_NORMAL,
			Model_Mission::PRIORITY_LOW,
			Model_Mission::PRIORITY_LOWEST
		],
		'types'			=> [
			Model_Mission::TYPE_TASK,
			Model_Mission::TYPE_EVENT
		],
		'order'			=> 'priority',
		'direction'		=> 'ASC',
	];

	/**
	 *	Add a new mission.
	 *	Redirects to index if editor right is missing.
	 *	@access		public
	 *	@param		string|NULL		$copyFromMissionId		ID of mission to copy default values from (optional)
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add( ?string $copyFromMissionId = NULL ): void
	{
		$words			= (object) $this->getWords( 'add' );

		if( !$this->isEditor ){
			$this->messenger->noteError( $words->msgNotEditor );
			$this->restart( NULL, TRUE, 403 );
		}
		if( !$this->userProjects ){																//  @todo user has no project
			$this->messenger->noteNotice( $words->msgNoProjectYet );							//  inform user about redirection
			$this->restart( './manage/project/add?from=work/mission/add' );						//  redirect to create a project
		}

		if( $copyFromMissionId && $mission = $this->model->get( $copyFromMissionId ) ){
			foreach( $mission as $key => $value )
				if( !in_array( $key, ['dayStart', 'dayEnd', 'status', 'createdAt', 'modifiedAt'] ) )
					$this->request->set( $key, $value );
			$this->request->set( 'dayStart', date( 'Y-m-d' ) );
		}

		$title		= $this->request->get( 'title' );
		$status		= $this->request->get( 'status' );
		$dayStart	= !$this->request->get( 'type' ) ? $this->request->get( 'dayWork' ) : $this->request->get( 'dayStart' );
		$dayEnd		= !$this->request->get( 'type' ) ? $this->request->get( 'dayDue' ) : $this->request->get( 'dayEnd' );
		$format		= $this->request->get( 'format' ) ?: $this->contentFormat;

		if( $this->request->has( 'add' ) ){
			if( !$title )
				$this->messenger->noteError( $words->msgNoTitle );
			if( !$this->messenger->gotError() ){
				$type	= (int) $this->request->get( 'type' );
				$data	= [
					'creatorId'			=> (int) $this->userId,
					'modifierId'		=> (int) $this->userId,
					'workerId'			=> (int) $this->request->get( 'workerId' ),
					'projectId'			=> (int) $this->request->get( 'projectId' ),
					'type'				=> $type,
					'priority'			=> (int) $this->request->get( 'priority' ),
					'status'			=> $status,
					'title'				=> $title,
					'content'			=> $this->request->get( 'content' ),
					'dayStart'			=> $this->logic->getDate( $dayStart ),
					'dayEnd'			=> $this->logic->getDate( $dayEnd ),
					'timeStart'			=> $this->request->get( 'timeStart' ),
					'timeEnd'			=> $this->request->get( 'timeEnd' ),
//					'minutesProjected'	=> $this->getMinutesFromInput( $this->request->get( 'minutesProjected' ) ),
					'minutesProjected'	=> round( View_Work_Mission::parseTime( $this->request->get( 'timeProjected' ) ) / 60 ),
					'location'			=> $this->request->get( 'location' ),
					'reference'			=> $this->request->get( 'reference' ),
					'format'			=> $format,
					'createdAt'			=> time(),
				];
				$missionId	= $this->model->add( $data, FALSE );
				$message	= $type == 1 ? $words->msgSuccessEvent : $words->msgSuccessTask;
				$this->messenger->noteSuccess( $message );
				$this->logic->noteChange( 'new', $missionId, NULL, $this->userId );
				$this->restart( 'view/'.$missionId, TRUE );
			}
		}
		$mission	= [];
		foreach( $this->model->getColumns() as $key )
			$mission[$key]	= strlen( $this->request->get( $key ) ) ? $this->request->get( $key ) : NULL;
		if( $mission['priority'] === NULL )
			$mission['priority']	= 3;
		if( $mission['status'] === NULL )
			$mission['status']	= 0;
		if( $mission['projectId'] === NULL )
			$mission['projectId']	= $this->logicProject->getDefaultProject( $this->userId );

		//  --  set current date for all date fields  --  //
		if( !$mission['dayStart'] )
			$mission['dayStart']	= date( 'Y-m-d' );
		if( !$mission['dayEnd'] )
			$mission['dayEnd']		= date( 'Y-m-d' );
		if( !$mission['format'] )
			$mission['format']		= $this->contentFormat;

//		$mission['minutesProjected']	= $this->getMinutesFromInput( $this->request->get( 'minutesProjected' ) );
		$this->addData( 'mission', (object) $mission );
		$this->addData( 'users', $this->userMap );
		$this->addData( 'userId', $this->userId );
		$this->addData( 'day', (int) $this->session->get( $this->filterKeyPrefix.'day' ) );
		$this->addData( 'format', $format );

		$this->addData( 'userProjects', $this->userProjects );
	}

	/**
	 *	@param		int|string		$missionId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function addDocument( int|string $missionId ): void
	{
		$upload		= (object) $this->env->getRequest()->get( 'document' );
		$model		= new Model_Mission_Document( $this->env );

		$path		= 'contents/documents/missions/';
		if( !file_exists( $path ) )
			mkdir( $path, 0777, TRUE );
		/** @var Entity_Mission_Document $document */
		$document	= $model->getByIndices( [
			'missionId'	=> $missionId,
			'filename'	=> $upload->name,
		] );
		$hashname	= $document ? $document->hashname : ID::uuid();
		$logic		= new Logic_Upload( $this->env );
//		$logic->checkMimeType( [] );
//		$logic->checkSize();
		$logic->setUpload( $upload );
		$logic->saveTo( $path.$hashname );

		if( $document ){
			$model->edit( $document->missionDocumentId, [
				'userId'		=> $this->userId,
				'size'			=> $upload->size,
				'modifiedAt'	=> time(),
			] );
		}
		else{
			$model->add( [
				'missionId'		=> $missionId,
				'userId'		=> $this->userId,
				'size'			=> $upload->size,
				'mimeType'		=> $upload->type,
				'filename'		=> $upload->name,
				'hashname'		=> $hashname,
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			] );
		}

//		$from	= $this->env->getRequest()->has( 'from' ) ? $this->env->getRequest()->has( 'from' );
		$this->restart( 'edit/'.$missionId.'#documents', TRUE );
	}

	protected function assignFilters(): void
	{
		$this->addData( 'userId', $this->userId );
		$this->addData( 'viewType', (int) $this->session->get( 'work-mission-view-type' ) );

		$direction	= $this->session->get( $this->filterKeyPrefix.'direction' );
		$order		= $this->session->get( $this->filterKeyPrefix.'order' );

		if( !$order )
			$this->restart( './work/mission/filter?order=priority' );

		$direction	= $direction ?: 'ASC';
		$this->session->set( $this->filterKeyPrefix.'direction', $direction );

		$this->setData( [																		//  assign data t$
			'userProjects'	=> $this->userProjects,													//  add user projec$
			'users'			=> $this->userMap,														//  add user map
		] );

		$this->addData( 'filterTypes', $this->session->get( $this->filterKeyPrefix.'types' ) );
		$this->addData( 'filterPriorities', $this->session->get( $this->filterKeyPrefix.'priorities' ) );
		$this->addData( 'filterStates', $this->session->get( $this->filterKeyPrefix.'states' ) );
		$this->addData( 'filterOrder', $order );
		$this->addData( 'filterProjects', $this->session->get( $this->filterKeyPrefix.'projects' ) );
		$this->addData( 'filterDirection', $direction );
		$this->addData( 'filterMode', $this->session->get( $this->filterKeyPrefix.'mode' ) );
		$this->addData( 'filterQuery', $this->session->get( $this->filterKeyPrefix.'query' ) );
		$this->addData( 'filterWorkers', $this->session->get( $this->filterKeyPrefix.'workers' ) );
		$this->addData( 'defaultFilterValues', $this->defaultFilterValues );
//		$this->addData( 'coworkers', $this->userMap )
		$this->addData( 'wordsFilter', $this->env->getLanguage()->getWords( 'work/mission' ) );
	}

	public function bulk(): never
	{
		$action	= $this->request->get( '__action' );
		$missionIds	= $this->request->get( 'missionIds' );

	print_m( $action );
	print_m( $missionIds );
	die;
	}

	/**
 	 *	Moves a mission by several days or to a given date.
	 *	Receives date or day difference using POST.
	 *	A day difference can be formatted like +2 or -2.
	 *	Moving a task mission will only affect start date but end date will remain unchanged.
	 *	Moving an event mission will affect start and end date.
	 *	If called using AJAX list rendering is triggered.
	 *	@access		public
	 *	@param		int|string		$missionId		ID of mission to move in time
	 *	@return		void
	 *	@throws		DateMalformedStringException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function changeDay( int|string $missionId ): void
	{
		$date		= trim( $this->request->get( 'date', '' ) );
		/** @var Entity_Mission $mission */
		$mission	= $this->model->get( $missionId );
		$data		= [
			'modifierId'	=> $this->userId,
			'modifiedAt'	=> time(),
		];
		$change		= "";

		if( preg_match( "/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]+$/", $date ) ){
			$date	= strtotime( $date );
			$diff	= ( $date - strtotime( $mission->dayStart ) ) / ( 24 * 3600 );
			$sign	= $diff >= 0 ? "+" : "-";
			$number	= round( abs( $diff ) );
			$change	= $sign." ".$number."day";
		}
		else if( preg_match( "/^[+-][0-9]+$/", $date ) ){
			$sign	= substr( $date, 0, 1 );					//  extract direction to move
			$number	= substr( $date, 1 );						//  extract number of days to move
			$change	= $sign." ".$number."day";
		}
		if( $change ){
			/** @noinspection PhpUnhandledExceptionInspection */
			$date	= new DateTime( $mission->dayStart );
			$data['dayStart'] = $date->modify( $change )->format( "Y-m-d" );
			if( $mission->dayEnd ){																//  mission has a duration
				if(  Model_Mission::TYPE_EVENT === $mission->type ){							//  mission is an event, not a task
					/** @noinspection PhpUnhandledExceptionInspection */
					$date	= new  DateTime( $mission->dayEnd );								//  take end timestamp and ...
					$data['dayEnd'] = $date->modify( $change )->format( "Y-m-d" );		//  ... store new moved end date
				}
			}
			$this->model->edit( $missionId, $data );
			$this->logic->noteChange( 'update', $missionId, $mission, $this->userId );
		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int|string		$missionId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function close( int|string $missionId ): void
	{
		$this->checkIsEditor( $missionId );
		$words		= (object) $this->getWords( 'edit' );
		/** @var Entity_Mission $mission */
		$mission	= $this->model->get( $missionId );
		$minutes	= ceil( View_Work_Mission::parseTime( $this->request->get( 'timeRequired' ) ) / 60 );
		$this->model->edit( $missionId, [														//  store in database
			'status'			=> $this->request->get( 'status' ),									//  - new status
//			'hoursRequired'		=> $this->request->get( 'hoursRequired' ),							//  - number of required hours
			'minutesRequired'	=> $minutes,														//  - number of required minutes
			'modifierId'		=> $this->userId,													//  - modifying user id
			'modifiedAt'		=> time(),															//  - modification time
		] );
		$this->logic->noteChange( 'update', $missionId, $mission, $this->userId );
		$this->messenger->noteSuccess( $words->msgSuccessClosed );
		$this->restart( NULL, TRUE );
	}

	public function convertToIssue( int|string $missionId ): never
	{
		die( "Not implemented yet" );
	}

	/**
	 *	@param		int|string		$missionId
	 *	@param		string			$format
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function convertContent( int|string $missionId, string $format ): void
	{
		$this->checkIsEditor( $missionId );
		$words			= (object) $this->getWords( 'edit' );
		/** @var Entity_Mission $mission */
		$mission		= $this->model->get( $missionId );

//print_m( $mission );die;
		if( !$mission )
			$this->messenger->noteError( $words->msgInvalidId );
		if( strtoupper( $mission->format ) === 'MARKDOWN' && strtoupper( $format ) === 'HTML' ){
			$content	= View_Helper_Markdown::transformStatic( $this->env, $mission->content );
			$data		= [
				'content'		=> $content,
				'format'		=> 'HTML',
				'modifiedAt'	=> time(),
				'modifierId'	=> $this->userId,
			];
			$this->model->edit( $missionId, $data, FALSE );
		}
		else if( strtoupper( $mission->format ) === 'HTML' && strtoupper( $format ) === 'MARKDOWN' ){
			if( !class_exists( '\\League\\HTMLToMarkdown\\HtmlConverter' ) ){
				$this->messenger->noteError( 'Converter package not installed. Use composer to install <code>ceus-media/markdown</code>!' );
				$this->restart( 'edit/'.$missionId, TRUE );
			}
			$converter	= new HtmlToMarkdownConverter( [
				'header_style'		=> 'atx',
				'hard_break'		=> TRUE,
				'bold_style'		=> '**',
				'italic_style'		=> '*',
			] );
			$data	= [
				'content'		=> $converter->convert( $mission->content ),
				'format'		=> 'Markdown',
				'modifiedAt'	=> time(),
				'modifierId'	=> $this->userId,
			];
			$this->model->edit( $missionId, $data, FALSE );
		}
		$this->restart( 'edit/'.$missionId, TRUE );
	}

	/**
	 *	@param		int|string		$missionId
	 *	@param		string			$missionDocumentId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function downloadDocument( int|string $missionId, string $missionDocumentId ): void
	{
		$this->deliverDocument( $missionId, $missionDocumentId, TRUE );
	}

	/**
	 *	@param		int|string		$missionId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string $missionId ): void
	{
		$this->checkIsEditor( $missionId );
		$words			= (object) $this->getWords( 'edit' );
		/** @var Entity_Mission $mission */
		$mission		= $this->model->get( $missionId );
		if( !$mission )
			$this->messenger->noteError( $words->msgInvalidId );
		if( !in_array( $mission->status, [-1, 0, 1, 2, 3] ) ){
			$this->messenger->noteError( $words->msgArchived );
			$this->restart( 'view/'.$missionId, TRUE );
		}
		if( $mission->status < 0 || $mission->status > 3 )
			$this->session->set( $this->filterKeyPrefix.'mode', 'archive' );
		else if( $this->session->get( $this->filterKeyPrefix.'mode' ) == 'archive' )
			$this->session->set( $this->filterKeyPrefix.'mode', 'now' );

		if( !array_key_exists( $mission->projectId, $this->userProjects ) )
			$this->messenger->noteError( $words->msgInvalidProject );

		if( $this->messenger->gotError() )
			$this->restart( NULL, TRUE );

		if( $this->lock ){
			if( $this->lock->isLockedByOther( 'Work_Missions', $missionId ) ){
				$lockUser	= $this->lock->getLockUser( 'Work_Missions', $missionId );
				$this->messenger->noteNotice( $words->msgLocked, $lockUser->username );
				$this->restart( 'view/'.$missionId, TRUE );
			}
			else if( !$this->lock->isLockedByMe( 'Work_Missions', $missionId ) )
				$this->lock->lockByMe( 'Work_Missions', $missionId );
		}

		$title		= $this->request->get( 'title' );
		$dayStart	= $this->request->get( 'dayStart' );
		$dayEnd		= $this->request->get( 'dayEnd' );
		if( $this->request->get( 'type' ) == 0 ){
			$dayStart	= $this->logic->getDate( $this->request->get( 'dayWork', '' ) );
			$dayEnd		= $this->request->get( 'dayDue' ) ? $this->logic->getDate( $this->request->get( 'dayDue' ) ) : NULL;
		}
		$format		= $this->request->get( 'format' ) ?: $this->contentFormat;

		if( $this->request->get( 'edit' ) ){
			if( !$title )
				$this->messenger->noteError( $words->msgNoTitle );
			if( !$this->messenger->gotError() ){
				$data	= [
					'workerId'			=> (int) $this->request->get( 'workerId' ),
					'projectId'			=> (int) $this->request->get( 'projectId' ),
					'type'				=> (int) $this->request->get( 'type' ),
					'priority'			=> (int) $this->request->get( 'priority' ),
					'status'			=> (int) $this->request->get( 'status' ),
					'title'				=> $title,
					'dayStart'			=> (string) $dayStart,
					'dayEnd'			=> (string) $dayEnd,
					'timeStart'			=> $this->request->get( 'timeStart' ),
					'timeEnd'			=> $this->request->get( 'timeEnd' ),
//					'minutesProjected'	=> $this->getMinutesFromInput( $this->request->get( 'minutesProjected' ) ),
					'minutesProjected'	=> round( View_Work_Mission::parseTime( $this->request->get( 'timeProjected' ) ) / 60 ),
//					'minutesRequired'	=> $this->getMinutesFromInput( $this->request->get( 'minutesRequired' ) ),
//					'hoursProjected'	=> $this->request->get( 'hoursProjected' ) ? $this->request->get( 'hoursProjected' ) : NULL,
//					'hoursRequired'		=> $this->request->get( 'hoursRequired' ) ? $this->request->get( 'hoursRequired' ) : NULL,
					'location'			=> $this->request->get( 'location' ),
					'reference'			=> $this->request->get( 'reference' ),
					'format'			=> $format,
					'modifiedAt'		=> time(),
					'modifierId'		=> $this->userId,
				];
				if( /*strtoupper( $format ) == "HTML" || */$this->request->has( 'content' ) )
					$data['content']	= $this->request->get( 'content' );

				$this->model->edit( $missionId, $data, FALSE );
				$this->messenger->noteSuccess( $words->msgSuccess );
				if( $this->request->get( 'inform') )
					$this->logic->noteChange( 'update', $missionId, $mission, $this->userId );
				$this->logic->noteVersion( $missionId, $this->userId, $mission->content );
				$this->restart( './work/mission' );
			}
		}
		$mission->creator	= array_key_exists( $mission->creatorId, $this->userMap ) ? $this->userMap[$mission->creatorId] : NULL;
		$mission->modifier	= array_key_exists( $mission->modifierId, $this->userMap ) ? $this->userMap[$mission->modifierId] : NULL;
		$mission->worker	= array_key_exists( $mission->workerId, $this->userMap ) ? $this->userMap[$mission->workerId] : NULL;

		$this->addData( 'mission', $mission );
		$this->addData( 'users', $this->logicProject->getProjectUsers( $mission->projectId ) );
		$missionUsers		= [$mission->creatorId => $mission->creator];
		if( $mission->workerId )
			$missionUsers[$mission->workerId]	= $mission->worker;

		$model		= new Model_Project( $this->env );
		foreach( $model->getProjectUsers( (int) $mission->projectId ) as $user )
			$missionUsers[$user->userId]	= $user;
		$this->addData( 'userProjects', $this->userProjects );
		$this->addData( 'missionUsers', $missionUsers );
		$this->addData( 'format', $mission->format ?: $this->contentFormat );

		if( $this->useIssues ){
			$this->env->getLanguage()->load( 'work/issue' );
			$this->addData( 'wordsIssue', $this->env->getLanguage()->getWords( 'work/issue' ) );
		}

		if( $this->useTimer ){
			$logic	= Logic_Work_Timer::getInstance( $this->env );
			$conditions	= [
				'module'	=> 'Work_Missions',
				'moduleId'	=> $mission->missionId,
				'status'	=> [0, 1, 2],
			];
			$this->addData( 'openTimers', $logic->countTimers( $conditions ) );

			$conditions	= [
				'moduleId'	=> 0,
				'userId'	=> $this->userId,
			];
			$this->addData( 'unrelatedTimers', $logic->index( $conditions, ['title' => 'ASC'] ) );
		}

		$model		= new Model_Mission_Document( $this->env );
		$orders		= ['modifiedAt' => 'DESC', 'createdAt' => 'DESC'];
		/** @var Entity_Mission_Document[] $documents */
		$documents	= $model->getAllByIndex( 'missionId', $missionId, $orders );
		$this->addData( 'documents', $documents );
		$this->env->getPage()->setTitle( $mission->title, 'prepend' );
	}

	public function filter( $reset = NULL): void
	{
		$sessionPrefix	= $this->getModeFilterKeyPrefix();
		if( $this->request->has( 'reset' ) || $reset ){
			$this->session->remove( $sessionPrefix.'query' );
			$this->session->remove( $sessionPrefix.'types' );
			$this->session->remove( $sessionPrefix.'priorities' );
			$this->session->remove( $sessionPrefix.'states' );
			$this->session->remove( $sessionPrefix.'projects' );
			$this->session->remove( $sessionPrefix.'order' );
			$this->session->remove( $sessionPrefix.'direction' );
			$this->session->remove( $sessionPrefix.'day' );
			$this->session->remove( $sessionPrefix.'workers' );
		}
		if( $this->request->has( 'access' ) )
			$this->session->set( $sessionPrefix.'access', $this->request->get( 'access' ) );
		if( $this->request->has( 'query' ) )
			$this->session->set( $sessionPrefix.'query', $this->request->get( 'query' ) );
		if( $this->request->has( 'types' ) )
			$this->session->set( $sessionPrefix.'types', $this->request->get( 'types' ) );
		if( $this->request->has( 'priorities' ) )
			$this->session->set( $sessionPrefix.'priorities', $this->request->get( 'priorities' ) );
		if( $this->request->has( 'states' ) )
			$this->session->set( $sessionPrefix.'states', $this->request->get( 'states' ) );
		if( $this->request->has( 'projects' ) )
			$this->session->set( $sessionPrefix.'projects', $this->request->get( 'projects' ) );
		if( $this->request->has( 'workers' ) )
			$this->session->set( $sessionPrefix.'workers', $this->request->get( 'workers' ) );
		if( $this->request->has( 'order' ) )
			$this->session->set( $sessionPrefix.'order', $this->request->get( 'order' ) );
		if( $this->request->has( 'direction' ) )
			$this->session->set( $sessionPrefix.'direction', $this->request->get( 'direction' ) );
#			if( $this->request->has( 'direction' ) )
#				$this->session->set( $sessionPrefix.'direction', $this->request->get( 'direction' ) );
		if( $this->request->isAjax() ){
			print( json_encode( (object) [
				'session'	=> $this->session->getAll(),
				'request'	=> $this->request->getAll()
			] ) );
			exit;
		}
		$this->restart( '', TRUE );
//		$this->request->isAjax() ? exit : $this->restart( '', TRUE );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function import(): void
	{
		$this->checkIsEditor();
		$file	= $this->env->getRequest()->get( 'serial' );
		if( $file['error'] != 0 ){
			$handler	= new UploadErrorHandler();
			$this->messenger->noteError( 'Upload-Fehler: '.$handler->getErrorMessage( $file['error'] ) );
		}
		else{
			$gz			= FileReader::load( $file['tmp_name'] );
			$serial		= @gzinflate( substr( $gz, 10, -8 ) );
			$missions	= @unserialize( $serial );
			if( !$serial )
				$this->messenger->noteError( 'Das Entpacken der Daten ist fehlgeschlagen.' );
			else if( !$missions )
				$this->messenger->noteError( 'Keine Daten enthalten.' );
			else{
				$model	= new Model_Mission( $this->env );
				$model->truncate();
				foreach( $missions as $mission )
					$model->add( (array) $mission );
				$this->messenger->noteSuccess( 'Die Daten wurden importiert.' );
			}
		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	Default action on this controller.
	 *	@access		public
	 *	@param		int|string|NULL		$missionId
	 *	@return		void
	 */
	public function index( int|string|NULL $missionId = NULL ): void
	{
		if( trim( $missionId ?? '' ) )
			$this->restart( 'view/'.$missionId, TRUE );

		$this->initFilters( $this->userId );
		$mode	= $this->session->get( $this->filterKeyPrefix.'mode' );
		if( $mode !== 'now' )
			$this->restart( './work/mission/'.$mode );

		$words			= (object) $this->getWords( 'index' );

		if( $this->request->has( 'view' ) )
			$this->session->set( 'work-mission-view-type', (int) $this->request->get( 'view' ) );

//		if( (int) $this->session->get( 'work-mission-view-type' ) == 1 )
//			$this->restart( './work/mission/calendar' );

		$this->addData( 'userId', $this->userId );
		$this->addData( 'viewType', (int) $this->session->get( 'work-mission-view-type' ) );

		$this->assignFilters();

		$this->setData( [																		//  assign data to view
			'missions'		=> $this->logic->getFilteredUserMissions( $this->userId, $this->filterKeyPrefix ),				//  add user missions
			'userProjects'	=> $this->userProjects,													//  add user projects
			'users'			=> $this->userMap,														//  add user map
			'currentDay'	=> (int) $this->session->get( $this->filterKeyPrefix.'day' ),			//  set currently selected day
		] );
	}

	public function kanban(): void
	{
		$this->session->set( $this->filterKeyPrefix.'mode', 'kanban' );
		$this->restart( NULL, TRUE );
	}

	public function now(): void
	{
		$this->session->set( $this->filterKeyPrefix.'mode', 'now' );
		$this->restart( NULL, TRUE );
	}

	public function removeDocument( int|string $missionId, int|string $missionDocumentId ): void
	{
		$this->logic->removeDocument( $missionDocumentId );
		$this->restart( 'edit/'.$missionId.'#documents', TRUE );
	}

	/**
	 *	@param		int|string		$missionId
	 *	@param		$priority
	 *	@param		bool			$showMission
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setPriority( int|string $missionId, $priority, bool $showMission = FALSE ): void
	{
		$this->checkIsEditor( $missionId );
		$data	= [];
		$this->model->edit( $missionId, [															//  store in database
			'priority'		=> $priority,															//  - new priority
			'modifierId'	=> $this->userId,														//  - modifying user id
			'modifiedAt'	=> time(),																//  - modification time
		] );
		if( !$showMission )																			//  back to list
			$this->restart( NULL, TRUE );											//  jump to list
		$this->restart( 'edit/'.$missionId, TRUE );									//  otherwise jump to or stay in mission
	}

	/**
	 *	@param		int|string		$missionId
	 *	@param		$status
	 *	@param		bool			$showMission
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setStatus( int|string $missionId, $status, bool $showMission = FALSE ): void
	{
		$this->checkIsEditor( $missionId );
		$this->model->edit( $missionId, [															//  store in database
			'status'		=> $status,																//  - new status
			'modifierId'	=> $this->userId,														//  - modifying user id
			'modifiedAt'	=> time(),																//  - modification time
		] );
		if( $status < 0 || !$showMission )															//  mission aborted/done or back to list
			$this->restart( NULL, TRUE );											//  jump to list
		$this->restart( 'edit/'.$missionId, TRUE );									//  otherwise jump to or stay in mission
	}

	/**
	 * @param		string		$type
	 * @param		boolean		$send
	 * @return		void
	 * @throws		ReflectionException
	 * @throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function testMail( string $type, bool $send = FALSE ): void
	{
		switch( $type ){
			case "daily":																			//
				$modelUser		= new Model_User( $this->env );										//
				$user			= $modelUser->get( $this->userId );									//

				$groupings	= ['missionId'];														//  group by mission ID to apply HAVING clause
				$havings	= [																		//  apply filters after grouping
					'creatorId = '.(int) $user->userId,												//
					'workerId = '.(int) $user->userId,												//
				];
				if( $this->env->getModules()->has( 'Manage_Projects' ) ){					//  look for module
					$modelProject	= new Model_Project( $this->env );								//
					$userProjects	= $modelProject->getUserProjects( $user->userId );				//  get projects assigned to user
					if( $userProjects )																//  projects found
						$havings[]	= 'projectId IN ('.join( ',', array_keys( $userProjects ) ).')';//  add to HAVING clause
				}
				$havings	= [join( ' OR ', $havings )];									//  render HAVING clause

				//  --  TASKS  --  //
				$filters	= [																		//  task filters
					'type'		=> 0,																//  tasks only
					'status'	=> [0, 1, 2, 3],													//  states: new, accepted, progressing, ready
					'dayStart'	=> "<= ".date( "Y-m-d", time() ),							//  present and past (overdue)
				];
				$order	= ['priority' => 'ASC'];
				/** @var Entity_Mission[] $tasks */
				$tasks	= $this->model->getAll( $filters, $order, [], [], $groupings, $havings );	//  get filtered tasks ordered by priority

				//  --  EVENTS  --  //
				$filters	= [																		//  event filters
					'type'		=> 1,																//  events only
					'status'	=> [0, 1, 2, 3],													//  states: new, accepted, progressing, ready
					'dayStart'	=> "<= ".date( "Y-m-d", time() ),							//  starting today
				];
				$order	= ['timeStart' => 'ASC'];
				/** @var Entity_Mission $events */
				$events	= $this->model->getAll( $filters, $order, [], [], $groupings, $havings );	//  get filtered events ordered by start time

				if( $events || $tasks ){															//  user has tasks or events
					$mail		= new Mail_Work_Mission_Daily( $this->env, [						//  create mail and populate data
						'user'		=> $user,
						'tasks'		=> $tasks,
						'events'	=> $events
					] );
					$content	= print( $mail->getContent( 'htmlRendered' ) );
				}
				break;
			default:
				throw new InvalidArgumentException( 'Invalid mail type' );
		}
		print( $content ?? 'no type set' );
		exit;
	}

	/**
	 *	@param		int|string		$missionId
	 *	@param		bool|NULL		$asText
	 *	@return		never
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function testMailNew( int|string $missionId, ?bool $asText = NULL ): never
	{
		$data	= [
			'mission'	=> $this->model->get( $missionId ),
			'user'		=> $this->userMap[$this->userId],
		];
		$mail	= new Mail_Work_Mission_New( $this->env, $data );
		$asText ? xmp( $mail->getContent('textRendered' ) ) : print( $mail->getContent( 'htmlRendered' ) );
		exit;
	}

	/**
	 *	@param		int|string		$missionId
	 *	@param		bool|NULL		$asText
	 *	@return		never
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function testMailUpdate( int|string $missionId, ?bool $asText = NULL ): never
	{
		/** @var Entity_Mission $missionOld */
		$missionOld		= $this->model->get( $missionId );
		$missionNew		= clone( $missionOld );

		$missionOld->type		= array_rand( array_flip( [0, 1] ) );
		$missionOld->status		= array_rand( array_flip( [-2, -1, 0, 1, 2, 3, 4] ) );
		$missionOld->location	= array_rand( array_flip( ['Raum 301', ''] ) );
		$missionOld->timeStart	= array_rand( array_flip( [10, 12, 14, 16] ) ).':'.array_rand( array_flip( ['00', '30'] ) );
		$missionOld->timeEnd	= array_rand( array_flip( [10, 12, 14, 16] ) ).':'.array_rand( array_flip( ['00', '30'] ) );

		$missionNew->projectId	= array_rand( array_flip( [1, 2, 3] ) );
		$missionNew->workerId	= array_rand( array_flip( [1, 2, 4] ) );
		$missionNew->type		= array_rand( array_flip( [0, 1] ) );
		$missionNew->status		= array_rand( array_flip( [-2, -1, 0, 1, 2, 3, 4] ) );
		$missionNew->priority	= array_rand( array_flip( [1, 2, 3, 4, 5] ) );
		$missionNew->timeStart	= array_rand( array_flip( [10, 12, 14, 16] ) ).':'.array_rand( array_flip( ['00', '30'] ) );
		$missionNew->timeEnd	= array_rand( array_flip( [10, 12, 14, 16] ) ).':'.array_rand( array_flip( ['00', '30'] ) );
		$missionNew->title		= array_rand( array_flip( [$missionOld->title, 'Heißt jetzt ganz anders'] ) );
		$missionNew->location	= array_rand( array_flip( [$missionOld->location, 'Schulungsraum', ''] ) );
		$missionNew->dayStart	= date( "Y-m-d", strtotime( $missionNew->dayStart ) + array_rand( array_flip( [-1, 0, 1] ) ) * 3600 * 24 );
		$missionNew->dayEnd		= date( "Y-m-d", strtotime( $missionNew->dayEnd ) + array_rand( array_flip( [-1, 0, 1] ) ) * 3600 * 24 );
		$data		= [
			'missionBefore'	=> $missionOld,
			'missionAfter'	=> $missionNew,
			'user'			=> $this->userMap[$this->userId],
		];
		$mail	= new Mail_Work_Mission_Update( $this->env, $data );
		$asText ? xmp( $mail->getContent( 'textRendered' ) ) : print( $mail->getContent( 'htmlRendered' ) );
		exit;
	}

	/**
	 *	@param		int|string		$missionId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function view( int|string $missionId ): void
	{
		$words		= (object) $this->getWords( 'edit' );

		/** @var Entity_Mission $mission */
		$mission	= $this->model->get( $missionId );
		if( !$mission ){
			$this->messenger->noteError( $words->msgInvalidId );
			$this->restart( NULL, TRUE );
		}
		if( !array_key_exists( $mission->projectId, $this->userProjects ) ){
			$this->messenger->noteError( $words->msgInvalidProject );
			$this->restart( NULL, TRUE );
		}

/*		$mode	= $this->session->get( $this->filterKeyPrefix.'mode' );
		if( $mission->status < 0 || $mission->status > 3 ){
			if( in_array( $mode, ['now', 'future'] ) )
				$this->session->set( $this->filterKeyPrefix.'mode', 'archive' );
		}
		else if( $this->session->get( $this->filterKeyPrefix.'mode' ) == 'archive' ){
			$this->session->set( $this->filterKeyPrefix.'mode', 'now' );
		}*/

/*		$title		= $this->request->get( 'title' );
		$dayStart	= $this->request->get( 'dayStart' );
		$dayEnd		= $this->request->get( 'dayEnd' );
		if( $this->request->get( 'type' ) == 0 ){
			$dayStart	= $this->logic->getDate( $this->request->get( 'dayWork' ) );
			$dayEnd		= $this->request->get( 'dayDue' ) ? $this->logic->getDate( $this->request->get( 'dayDue' ) ) : NULL;
		}*/
		$mission->creator	= array_key_exists( $mission->creatorId, $this->userMap ) ? $this->userMap[$mission->creatorId] : NULL;
		$mission->modifier	= array_key_exists( $mission->modifierId, $this->userMap ) ? $this->userMap[$mission->modifierId] : NULL;
		$mission->worker	= array_key_exists( $mission->workerId, $this->userMap ) ? $this->userMap[$mission->workerId] : NULL;
		$mission->versions	= $this->logic->getVersions( $missionId );
		$this->addData( 'mission', $mission );
		$this->addData( 'users', $this->userMap );
		$missionUsers		= [$mission->creatorId => $mission->creator];
		if( $mission->workerId )
			$missionUsers[$mission->workerId]	= $mission->worker;

		$model		= new Model_Project( $this->env );
		foreach( $model->getProjectUsers( (int) $mission->projectId ) as $user )
			$missionUsers[$user->userId]	= $user;
		$this->addData( 'userProjects', $this->userProjects );
		$mission->project	= $model->get( (int) $mission->projectId );
		$this->addData( 'missionUsers', $missionUsers );

		if( $this->useIssues ){
			$this->env->getLanguage()->load( 'work/issue' );
			$this->addData( 'wordsIssue', $this->env->getLanguage()->getWords( 'work/issue' ) );
		}

		$model		= new Model_Mission_Document( $this->env );
		$orders		= ['modifiedAt' => 'DESC', 'createdAt' => 'DESC'];
		/** @var Entity_Mission_Document[] $documents */
		$documents	= $model->getAllByIndex( 'missionId', $missionId, $orders );
		$this->addData( 'documents', $documents );
		$this->env->getPage()->setTitle( $mission->title, 'prepend' );
	}

	/**
	 *	@param		int|string		$missionId
	 *	@param		int|string		$missionDocumentId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function viewDocument( int|string $missionId, int|string $missionDocumentId ): void
	{
		$this->deliverDocument( $missionId, $missionDocumentId );
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->acl			= $this->env->getAcl();

		$this->model		= new Model_Mission( $this->env );
		$this->logicProject	= Logic_Project::getInstance( $this->env );
		$this->logic		= Logic_Work_Mission::getInstance( $this->env );
		$this->logicAuth	= Logic_Authentication::getInstance( $this->env );

		$this->isEditor		= $this->acl->has( 'work/mission', 'edit' );
		$this->isViewer		= $this->acl->has( 'work/mission', 'view' );
		$this->useIssues	= $this->env->getModules()->has( 'Work_Issues' );
		$this->useTimer		= $this->env->getModules()->has( 'Work_Timer' );

		if( $this->logicAuth->isAuthenticated() ){
			$this->userId		= $this->logicAuth->getCurrentUserId();
			$this->userRoleId	= $this->logicAuth->getCurrentRoleId();
		}

		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.work_missions.', TRUE );
		$this->contentFormat	= $this->moduleConfig->get( 'format' );

//		if( !$this->userId || !$this->isViewer )
//			$this->restart( NULL, FALSE, 401 );

		$this->logicProject	= Logic_Project::getInstance( $this->env );
		$this->userMap		= $this->logicProject->getCoworkers( $this->userId, NULL, TRUE );

		//  @todo	 DO NOT DO THIS!!! (badly scaling)
//		$model			= new Model_User( $this->env );
//		foreach( $model->getAll() as $user )
//			$this->userMap[$user->userId]	= $user;

		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'useTimer', $this->useTimer );
		$this->addData( 'useIssues', $this->useIssues );
		$this->addData( 'acl', $this->acl );
		$this->addData( 'userId', $this->userRoleId );
		$this->addData( 'userRoleId', $this->userRoleId );

		$this->userProjects		= $this->logic->getUserProjects( $this->userId, TRUE );
		if( $this->hasFullAccess() )
			$this->userProjects		= $this->logic->getUserProjects( $this->userId );
		$this->addData( 'projects', $this->userProjects );
		if( $this->env->getModules()->has( 'Resource_Database_Lock' ) )
			$this->lock	= new Logic_Database_Lock( $this->env );

//		$this->env->getModules()->callHook( 'Test', 'test', [] );
	}

	/**
	 *	@param		int|string|NULL		$missionId
	 *	@param		bool				$strict
	 *	@param		int					$status
	 *	@return		bool
	 */
	protected function checkIsEditor( int|string|NULL $missionId = NULL, bool $strict = TRUE, int $status = 403 ): bool
	{
		if( $this->isEditor )
			return TRUE;
		if( $strict ){
			$words		= (object) $this->getWords( 'msg' );
			$message	= $words->errorNoRightToAdd;
			$redirect	= NULL;
			if( $missionId ){
				$message	= $words->errorNoRightToEdit;
				$redirect	= 'view/'.$missionId;
			}
			$this->env->getMessenger()->noteError( $message );
			$this->restart( $redirect, TRUE, $status );
		}
		return FALSE;
	}

	/**
	 *	@param		int|string		$missionId
	 *	@param		int|string		$missionDocumentId
	 *	@param		bool			$download
	 *	@return		never
	 *	@todo		check sanity, see below
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function deliverDocument( int|string $missionId, int|string $missionDocumentId, bool $download = FALSE ): never
	{
		//	check missionId against user
		//	check missionDocumentId against missionId

		$model		= new Model_Mission_Document( $this->env );
		$document	= $model->get( $missionDocumentId );
		if( !$document ){
			$this->messenger->noteError( 'Document ID is invalid' );
			$this->restart( './view/'.$missionId );
		}
		$pathname	= "contents/documents/missions/".$document->hashname;
		if( !file_exists( $pathname ) ){
			$this->messenger->noteError( 'Document is not existing' );
			$this->restart( './view/'.$missionId );
		}
		$timestamp	= max( $document->createdAt, $document->modifiedAt );
		$disposition	= $download ? 'attachment' : 'inline';
		header( 'Content-Type: '.$document->mimeType );
		header( 'Content-Length: '.$document->size );
		header( 'Last-Modified: '.date( 'r', $timestamp ) );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Content-Disposition: '.$disposition.'; filename='.$document->filename );

		$fp = @fopen( $pathname, "rb" );
		if( !$fp )
			header("HTTP/1.0 500 Internal Server Error");
		fpassthru( $fp );
		exit;
	}

	/**
	 *	@param		string		$input
	 *	@return		int
	 */
	protected function getMinutesFromInput( string $input ): int
	{
		if( !strlen( trim( $input ) ) )
			return 0;
		if( substr_count( $input, ':' ) ){
			[$hours, $minutes]	= explode( ':', $input );
			return (int) $hours + (int) $minutes * 60;
		}
		return (int) $input;
	}

	protected function getModeFilterKeyPrefix(): string
	{
		$mode	= '';
		if( $this->session->get( $this->filterKeyPrefix.'mode' ) !== 'now' )
			$mode	= $this->session->get( $this->filterKeyPrefix.'mode' ).'.';
		return $this->filterKeyPrefix.$mode;
	}

	/**
	 * @todo	remove this because all methods receiver userId and this is using roleId from session
	 */
	protected function hasFullAccess(): bool
	{
		$roleId	= $this->session->get( 'auth_role_id', '' );
		return '' !== $roleId && $this->env->getAcl()->hasFullAccess( $roleId );
	}

	protected function initDefaultFilters(): void
	{
		if( $this->session->get( $this->filterKeyPrefix.'mode' ) === NULL )
			$this->session->set( $this->filterKeyPrefix.'mode', $this->defaultFilterValues['mode'] );
		if( !$this->session->get( $this->filterKeyPrefix.'types' ) )
			$this->session->set( $this->filterKeyPrefix.'types', $this->defaultFilterValues['types'] );
		if( !$this->session->get( $this->filterKeyPrefix.'priorities' ) )
			$this->session->set( $this->filterKeyPrefix.'priorities', $this->defaultFilterValues['priorities'] );
		if( !$this->session->get( $this->filterKeyPrefix.'states' ) ){
//			$tense		= $this->session->get( $this->filterKeyPrefix.'tense' );
			$states		= $this->defaultFilterValues['states'];
			$this->session->set( $this->filterKeyPrefix.'states', $states );
		}
		if( !$this->session->get( $this->filterKeyPrefix.'projects' ) )
			$this->session->set( $this->filterKeyPrefix.'projects', array_keys( $this->userProjects ) );
		if( !$this->session->get( $this->filterKeyPrefix.'workers' ) )
			$this->session->set( $this->filterKeyPrefix.'workers', array_keys( $this->userMap ) );
		if( $this->session->get( $this->filterKeyPrefix.'order' ) === NULL ){
			if( $this->session->get( $this->filterKeyPrefix.'direction' ) === NULL ){
//				$tense		= $this->session->get( $this->filterKeyPrefix.'tense' );
				$order		= $this->defaultFilterValues['order'];
				$direction	= $this->defaultFilterValues['direction'];
				$this->session->set( $this->filterKeyPrefix.'order', $order );
				$this->session->set( $this->filterKeyPrefix.'direction', $direction );
			}
		}
	}

	/**
	 *	@param		int|string		$userId
	 *	@return		void
	 */
	protected function initFilters( int|string $userId ): void
	{
		if( !(int) $userId )
			return;
		if( !$this->session->getAll( $this->filterKeyPrefix, TRUE )->count() )
			$this->recoverFilters( $userId );

		//  --  DEFAULT SETTINGS  --  //
		$this->initDefaultFilters();

		//  --  GENERAL LOGIC CONDITIONS  --  //
		$conditions	= [];
		$conditions['status']	= $this->defaultFilterValues['states'];
		switch( $this->session->get( $this->filterKeyPrefix.'mode' ) ){
			case 'now':
				$conditions['dayStart']	= '< '.date( "Y-m-d", time() + 7 * 24 * 60 * 60 );				//  @todo:  calculation is incorrect
				break;
//			case 'future':
//				$conditions['dayStart']	= '>= '.date( "Y-m-d", time() + 6 * 24 * 60 * 60 );				//  @todo:  calculation is incorrect
//				break;
		}
		foreach( $conditions as $key => $value )
			$this->logic->generalConditions[$key]	= $value;
	}

	/**
	 *	@param		int|string		$userId
	 *	@return		void
	 */
	protected function recoverFilters( int|string $userId ): void
	{
		$model	= new Model_Mission_Filter( $this->env );
		$serial	= $model->getByIndex( 'userId', $userId, [], ['serial'], FALSE );
//	print_m( $serial );
//	print_m( unserialize( $serial ) );
//	die;
//	$this->env->getMessenger()->noteNotice( '<xmp>'.$serial.'</xmp>' );
//		if( !strlen( $serial ) )
//			return;
		$serial	= $serial ? unserialize( $serial ) : NULL;
		if( is_array( $serial ) ){
			foreach( $serial as $key => $value )
				$this->session->set( $this->filterKeyPrefix.$key, $value );
			$this->env->getMessenger()->noteNotice( 'Filter für Aufgaben aus der letzten Sitzung wurden reaktiviert.' );
			$this->restart( NULL, TRUE );
		}
	}

}
