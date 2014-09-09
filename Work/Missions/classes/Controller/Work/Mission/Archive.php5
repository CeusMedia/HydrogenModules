<?php
class Controller_Work_Mission_Archive extends CMF_Hydrogen_Controller{

	protected $options;
	protected $request;
	protected $session;

	protected $defaultFilterValues	= array(
		'tense'			=> 0,
		'states'		=> array(
			Model_Mission::STATUS_ABORTED,
			Model_Mission::STATUS_REJECTED,
			Model_Mission::STATUS_FINISHED
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
		'order'			=> 'dayStart',
		'direction'		=> 'DESC',
	);

	protected function __onInit(){
		$this->logic	= new Logic_Mission( $this->env );
		$this->options	= $this->env->getConfig()->getAll( 'module.work_missions.', TRUE );
		$this->request	= $this->env->getRequest();
		$this->session	= $this->env->getSession();
		$this->acl		= $this->env->getAcl();
		$this->isEditor	= $this->acl->has( 'work/mission', 'edit' );
		$this->isViewer	= $this->acl->has( 'work/mission', 'view' );

		$this->session->set( 'filter.work.mission.tense', 0 );
		if( !( $mode = $this->session->get( 'filter.work.mission.archive.mode' ) ) ){
			if( !( $mode = $this->session->get( 'filter.work.mission.mode' ) ) )
				$this->session->set( 'filter.work.mission.mode', $mode = 'list' );
			$this->session->set( 'filter.work.mission.archive.mode', $mode );
		}
		if( !( $mode = $this->session->get( 'filter.work.mission.archive.mode' ) ) ){
				$this->session->set( 'filter.work.mission.mode', $mode = 'list' );

        $model          = new Model_User( $this->env );
        foreach( $model->getAll() as $user )
            $this->userMap[$user->userId]   = $user;

        $modules    = $this->env->getModules();
        $this->useProjects  = $modules->has( 'Manage_Projects' );
        $this->useIssues    = $modules->has( 'Manage_Issues' );

        $userId     = $this->session->get( 'userId' );
        if( $userId ){
            if( $this->hasFullAccess() )
                $this->userProjects = $this->logic->getUserProjects( $userId );
            else
                $this->userProjects     = $this->logic->getUserProjects( $userId, TRUE );
//            $this->initFilters( $userId );
        }
	}

	public function ajaxRenderContent(){
		if( $this->session->get( 'filter_work_mission_archive_mode' ) === 'calendar' )
			$this->redirect( 'work/mission/archive/calendar', 'ajaxRenderContent' );
		else
			$this->redirect( 'work/mission/archive/list', 'ajaxRenderContent' );
	}

	protected function assignFilters(){
        $userId         = $this->session->get( 'userId' );
        $this->addData( 'userId', $userId );
        $this->addData( 'viewType', (int) $this->session->get( 'work-mission-view-type' ) );

        $access     = $this->session->get( 'filter.work.mission.access' );
        $direction  = $this->session->get( 'filter.work.mission.direction' );
        $order      = $this->session->get( 'filter.work.mission.order' );
        if( !$order )
            $this->restart( './work/mission/filter?order=priority' );
        if( !$access )
            $this->restart( './work/mission/filter?access=worker' );

        $direction  = $direction ? $direction : 'ASC';
        $this->session->set( 'filter.work.mission.direction', $direction );

        $this->setData( array(                                                                      //  assign data t$
            'useProjects'	=> $this->useProjects,
            'userProjects'	=> $this->userProjects,                                             //  add user projec$
            'users'			=> $this->userMap,                                                      //  add user map
        ) );

        $this->addData( 'filterAccess', $access );
        $this->addData( 'filterTypes', $this->session->get( 'filter.work.mission.types' ) );
        $this->addData( 'filterPriorities', $this->session->get( 'filter.work.mission.priorities' ) );
        $this->addData( 'filterStates', $this->session->get( 'filter.work.mission.states' ) );
        $this->addData( 'filterOrder', $order );
        $this->addData( 'filterProjects', $this->session->get( 'filter.work.mission.projects' ) );
        $this->addData( 'filterDirection', $direction );
        $this->addData( 'filterTense', $this->session->get( 'filter.work.mission.tense' ) );
		$this->addData( 'filterMode', $this->session->get( 'filter.work.mission.mode' ) );
		$this->addData( 'filterQuery', $this->session->get( 'filter.work.mission.query' ) );
		$this->addData( 'defaultFilterValues', $this->defaultFilterValues );
		$this->addData( 'wordsFilter', $this->env->getLanguage()->getWords( 'work/mission' ) );
	}


	public function index(){
		if( $this->session->get( 'filter_work_mission_archive_mode' ) === 'calendar' )
			$this->restart( './work/mission/archive/calendar' );
		$this->restart( './work/mission/archive/list' );
	}

	protected function hasFullAccess(){
		return $this->env->getAcl()->hasFullAccess( $this->env->getSession()->get( 'roleId' ) );
	}
}
?>
