<?php
/**
 *	Controller.
 *	@version		$Id$
 */
/**
 *	Controller.
 *	@version		$Id$
 *	@todo			implement
 *	@todo			code documentation
 */
class Controller_Work_Mission_Calendar extends CMF_Hydrogen_Controller{

	protected $acl;
	protected $filterKeyPrefix	= 'filter.work.mission.';
	protected $isEditor;
	protected $isViewer;
	protected $hasFullAccess	= FALSE;
	protected $logic;
	protected $messenger;
	protected $request;
	protected $session;
	protected $useIssues		= FALSE;
	protected $useProjects		= FALSE;
	protected $userMap			= array();

	protected $defaultFilterValues	= array(
		'states'	=> array(
			Model_Mission::STATUS_NEW,
			Model_Mission::STATUS_ACCEPTED,
			Model_Mission::STATUS_PROGRESS,
			Model_Mission::STATUS_READY
		),
		'priorities'	=> array(
			Model_Mission::PRIORITY_NONE,
			Model_Mission::PRIORITY_HIGHEST,
			Model_Mission::PRIORITY_HIGH,
			Model_Mission::PRIORITY_NORMAL,
			Model_Mission::PRIORITY_LOW,
			Model_Mission::PRIORITY_LOWEST
		),
		'types'			=> array(
			Model_Mission::TYPE_TASK,
			Model_Mission::TYPE_EVENT
		),
		'order'			=> 'priority',
		'direction'		=> 'ASC',
	);

	protected function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->acl			= $this->env->getAcl();

		$this->model		= new Model_Mission( $this->env );
		$this->logic		= new Logic_Mission( $this->env );

		$this->isEditor		= $this->acl->has( 'work/mission', 'edit' );
		$this->isViewer		= $this->acl->has( 'work/mission', 'view' );
		$this->useProjects	= $this->env->getModules()->has( 'Manage_Projects' );
		$this->useIssues	= $this->env->getModules()->has( 'Manage_Issues' );

		$userId				= $this->session->get( 'userId' );

		if( !$userId || !$this->isViewer )
			$this->restart( NULL, FALSE, 401 );

		//  @todo	kriss: DO NOT DO THIS!!! (badly scaling)
		$model			= new Model_User( $this->env );
		foreach( $model->getAll() as $user )
			$this->userMap[$user->userId]	= $user;

		$this->addData( 'useProjects', $this->useProjects );
		$this->addData( 'useIssues', $this->useIssues );

		$this->userProjects		= $this->logic->getUserProjects( $userId, TRUE );
		if( $this->hasFullAccess() )
			$this->userProjects		= $this->logic->getUserProjects( $userId );

		$this->initFilters( $userId );
	}

	protected function initFilters( $userId ){
		if( !(int) $userId )
			return;
		if( !$this->session->getAll( 'filter.work.mission.', TRUE )->count() )
			$this->recoverFilters( $userId );

		//  --  DEFAULT SETTINGS  --  //
		$this->initDefaultFilters();

		//  --  GENERAL LOGIC CONDITIONS  --  //
		$tense		= $this->session->get( $this->filterKeyPrefix.'tense' );
		$this->logic->generalConditions['status']		= $this->defaultFilterValues['states'];
		switch( $tense ){
			case 1:
				$this->logic->generalConditions['dayStart']	= '<'.date( "Y-m-d", time() + 7 * 24 * 60 * 60 );				//  @todo: kriss: calculation is incorrect
				break;
//			case 2:
//				$this->logic->generalConditions['dayStart']	= '>='.date( "Y-m-d", time() + 6 * 24 * 60 * 60 );				//  @todo: kriss: calculation is incorrect
//				break;
		}
	}

	public function ajaxRenderContent(){
		$tense		= (int) $this->session->get( $this->filterKeyPrefix.'tense' );
		if( $tense === 0 )
			$this->redirect( 'work/mission/archive', 'ajaxRenderContent' );
		else if( $tense === 2 )
			$this->redirect( 'work/mission/future', 'ajaxRenderContent' );
		else
			$this->redirect( 'work/mission', 'ajaxRenderList' );
	}

	protected function assignFilters(){
		$userId		= $this->session->get( 'userId' );
		$this->addData( 'userId', $userId );
		$this->addData( 'viewType', (int) $this->session->get( 'work-mission-view-type' ) );

//		$access		= $this->session->get( $this->filterKeyPrefix.'access' );
		$direction	= $this->session->get( $this->filterKeyPrefix.'direction' );
		$order		= $this->session->get( $this->filterKeyPrefix.'order' );

		if( !$order )
			$this->restart( './work/mission/filter?order=priority' );
//		if( !$access )
//			$this->restart( './work/mission/filter?access=worker' );

		$direction	= $direction ? $direction : 'ASC';
		$this->session->set( $this->filterKeyPrefix.'direction', $direction );

		$this->setData( array(																		//  assign data t$
			'userProjects'	=> $this->userProjects,													//  add user projec$
			'users'			=> $this->userMap,														//  add user map
		) );

//		$this->addData( 'filterAccess', $access );
		$this->addData( 'filterTypes', $this->session->get( $this->filterKeyPrefix.'types' ) );
		$this->addData( 'filterPriorities', $this->session->get( $this->filterKeyPrefix.'priorities' ) );
		$this->addData( 'filterStates', $this->session->get( $this->filterKeyPrefix.'states' ) );
		$this->addData( 'filterOrder', $order );
		$this->addData( 'filterProjects', $this->session->get( $this->filterKeyPrefix.'projects' ) );
		$this->addData( 'filterDirection', $direction );
		$this->addData( 'filterMode', $this->session->get( $this->filterKeyPrefix.'mode' ) );
		$this->addData( 'filterQuery', $this->session->get( $this->filterKeyPrefix.'query' ) );
		$this->addData( 'defaultFilterValues', $this->defaultFilterValues );
		$this->addData( 'wordsFilter', $this->env->getLanguage()->getWords( 'work/mission' ) );
	}

	public function index( $year = NULL, $month = NULL ){
		if( $year === NULL || $month === NULL ){
			$year	= date( "Y" );
			if( $this->session->has( 'work-mission-view-year' ) )
				$year	= $this->session->get( 'work-mission-view-year' );
			$month	= date( "m" );
			if( $this->session->has( 'work-mission-view-month' ) )
				$month	= $this->session->get( 'work-mission-view-month' );
			$this->restart( './work/mission/calendar/'.$year.'/'.$month );
		}
		if( $month < 1 || $month > 12 ){
			while( $month > 12 ){
				$month	-= 12;
				$year	++;
			}
			while( $month < 1 ){
				$month	+= 12;
				$year	--;
			}
			$this->restart( './work/mission/calendar/'.$year.'/'.$month );
		}
		$this->session->set( 'work-mission-view-year', $year );
		$this->session->set( 'work-mission-view-month', $month );

		$this->setData( array(
			'userId'	=> $this->session->get( 'userId' ),
			'year'		=> $year,
			'month'		=> $month,
		) );
	}

	protected function checkIsEditor( $missionId = NULL, $strict = TRUE, $status = 403 ){
		if( $this->isEditor )
			return TRUE;
		if( !$strict )
			return FALSE;
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

	public function export( $format = NULL, $debug = FALSE ){
		switch( $format ){
			case 'ical':
				$ical	= $this->exportAsIcal( $debug );
				$debug ? xmp( $ical ) : print( $ical );
				die;
				break;
			default:
				$missions	= $this->model->getAll();												//  get all missions
				$zip		= gzencode( serialize( $missions ) );									//  gzip serial of mission objects
				Net_HTTP_Download::sendString( $zip , 'missions_'.date( 'Ymd' ).'.gz' );			//  deliver downloadable file
		}
	}

	/**
	 * @todo	remove this because all methods receiver userId and this is using roleId from session
	 */
	protected function hasFullAccess(){
		return $this->env->getAcl()->hasFullAccess( $this->env->getSession()->get( 'roleId' ) );
	}

	public function filter(){
		if( $this->request->has( 'reset' ) ){
			$this->session->remove( $this->filterKeyPrefix.'access' );
			$this->session->remove( $this->filterKeyPrefix.'query' );
			$this->session->remove( $this->filterKeyPrefix.'types' );
			$this->session->remove( $this->filterKeyPrefix.'priorities' );
			$this->session->remove( $this->filterKeyPrefix.'states' );
			$this->session->remove( $this->filterKeyPrefix.'projects' );
			$this->session->remove( $this->filterKeyPrefix.'order' );
			$this->session->remove( $this->filterKeyPrefix.'direction' );
			$this->session->remove( $this->filterKeyPrefix.'day' );
		}
		if( $this->request->has( 'access' ) )
			$this->session->set( $this->filterKeyPrefix.'access', $this->request->get( 'access' ) );
		if( $this->request->has( 'query' ) )
			$this->session->set( $this->filterKeyPrefix.'query', $this->request->get( 'query' ) );
		if( $this->request->has( 'types' ) )
			$this->session->set( $this->filterKeyPrefix.'types', $this->request->get( 'types' ) );
		if( $this->request->has( 'priorities' ) )
			$this->session->set( $this->filterKeyPrefix.'priorities', $this->request->get( 'priorities' ) );
		if( $this->request->has( 'states' ) )
			$this->session->set( $this->filterKeyPrefix.'states', $this->request->get( 'states' ) );
		if( $this->request->has( 'projects' ) )
			$this->session->set( $this->filterKeyPrefix.'projects', $this->request->get( 'projects' ) );
		if( $this->request->has( 'order' ) )
			$this->session->set( $this->filterKeyPrefix.'order', $this->request->get( 'order' ) );
		if( $this->request->has( 'direction' ) )
			$this->session->set( $this->filterKeyPrefix.'direction', $this->request->get( 'direction' ) );
#			if( $this->request->has( 'direction' ) )
#				$this->session->set( $this->filterKeyPrefix.'direction', $this->request->get( 'direction' ) );
		if( $this->request->isAjax() ){
			print( json_encode( (object) array(
				'session'	=> $this->session->getAll(),
				'request'	=> $this->request->getAll()
			) ) );
			exit;
		}
		$this->restart( '', TRUE );
//		$this->request->isAjax() ? exit : $this->restart( '', TRUE );
	}

	protected function getFilteredMissions( $userId, $additionalConditions = array() ){
//		$config			= $this->env->getConfig();
//		$userId			= $this->session->get( 'userId' );

		$query		= $this->session->get( $this->filterKeyPrefix.'query' );
		$types		= $this->session->get( $this->filterKeyPrefix.'types' );
		$priorities	= $this->session->get( $this->filterKeyPrefix.'priorities' );
		$states		= $this->session->get( $this->filterKeyPrefix.'states' );
		$projects	= $this->session->get( $this->filterKeyPrefix.'projects' );
		$direction	= $this->session->get( $this->filterKeyPrefix.'direction' );
		$order		= $this->session->get( $this->filterKeyPrefix.'order' );
		$orders		= array(					//  collect order pairs
			$order		=> $direction,			//  selected or default order and direction
			'timeStart'	=> 'ASC',				//  order events by start time
		);
		if( $order != "title" )					//  if not ordered by title
			$orders['title']	= 'ASC';		//  order by title at last

		$conditions	= array();
		if( is_array( $types ) && count( $types ) )
			$conditions['type']	= $types;
		if( is_array( $priorities ) && count( $priorities ) )
			$conditions['priority']	= $priorities;
		if( is_array( $states ) && count( $states ) )
			$conditions['status']	= $states;
		if( strlen( $query ) )
			$conditions['title']	= '%'.str_replace( array( '*', '?' ), '%', $query ).'%';
		if( is_array( $projects ) && count( $projects ) )											//  if filtered by projects
			$conditions['projectId']	= $projects;												//  apply project conditions
		foreach( $additionalConditions as $key => $value )
			$conditions[$key]			= $value;
		return $this->logic->getUserMissions( $userId, $conditions, $orders );
	}

	protected function initDefaultFilters(){
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

	protected function recoverFilters( $userId ){
		$model	= new Model_Mission_Filter( $this->env );
		$serial	= $model->getByIndex( 'userId', $userId, 'serial' );
		$serial	= $serial ? unserialize( $serial ) : NULL;
		if( is_array( $serial ) ){
			foreach( $serial as $key => $value )
				$this->session->set( 'filter.work.mission.'.$key, $value );
			$this->env->getMessenger()->noteNotice( 'Filter für Aufgaben aus der letzten Sitzung wurden reaktiviert.' );
			$this->restart( NULL, TRUE );
		}
	}

	protected function saveFilters( $userId ){
		$model		= new Model_Mission_Filter( $this->env );
		$serial		= serialize( $this->session->getAll( 'filter.work.mission.' ) );
		$data		= array( 'serial' => $serial, 'timestamp' => time() );
		$indices	= array( 'userId' => $userId );
		$filter		= $model->getByIndex( 'userId', $userId );
		if( $filter )
			$model->edit( $filter->missionFilterId, $data );
		else
			$model->add( $data + $indices );
	}

	public function setFilter( $name, $value = NULL, $set = FALSE ){
		$values		= $this->session->get( $this->filterKeyPrefix.$name );
		if( is_array( $values ) ){
			if( $set )
				$values[]	= $value;
			else if( ( $pos = array_search( $value, $values ) ) >= 0 )
				unset( $values[$pos] );
		}
		else{
			$values	= $value;
		}
		$this->session->set( $this->filterKeyPrefix.$name, $values );
		$userId		= $this->session->get( 'userId' );
		$this->saveFilters( $userId );
		if( $this->env->getRequest()->isAjax() ){
			header( 'Content-Type: application/json' );
			print( json_encode( TRUE ) );
			exit;
		}
		$this->restart( NULL, TRUE );
	}
}
?>
