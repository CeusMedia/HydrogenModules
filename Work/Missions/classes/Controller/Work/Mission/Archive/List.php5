<?php
//class Controller_Work_Mission_Archive_List extends CMF_Hydrogen_Controller{
class Controller_Work_Mission_Archive_List extends Controller_Work_Mission_Archive{

//	protected $options;

	protected function __onInit(){
		parent::__onInit();
//		$this->options	= $this->env->getConfig()->getAll( 'module.work_missions.', TRUE );
		$this->session->set( 'filter.work.mission.tense', 0 );
		$this->session->set( 'filter.work.mission.archive.mode', 'list' );
		if( !$this->session->get( 'filter.work.mission.mode' ) )
			$this->session->set( 'filter.work.mission.mode', 'list' );
	}

    protected function getFilteredMissions( $userId, $additionalConditions = array() ){
//      $config         = $this->env->getConfig();
        $session        = $this->env->getSession();
//      $userId         = $session->get( 'userId' );

        $query      = $session->get( 'filter.work.mission.query' );
        $types      = $session->get( 'filter.work.mission.types' );
        $priorities = $session->get( 'filter.work.mission.priorities' );
        $states     = $session->get( 'filter.work.mission.states' );
        $projects   = $session->get( 'filter.work.mission.projects' );
        $direction  = $session->get( 'filter.work.mission.direction' );
        $order      = $session->get( 'filter.work.mission.order' );
        $orders     = array(                    //  collect order pairs
            $order      => $direction,          //  selected or default order and direction
            'timeStart' => 'ASC',               //  order events by start time
        );
        if( $order != "title" )                 //  if not ordered by title
            $orders['title']    = 'ASC';        //  order by title at last

        $conditions = array();
        if( is_array( $types ) && count( $types ) )
            $conditions['type'] = $types;
        if( is_array( $priorities ) && count( $priorities ) )
            $conditions['priority'] = $priorities;
        if( is_array( $states ) && count( $states ) )
            $conditions['status']   = $states;
        if( strlen( $query ) )
            $conditions['title']    = '%'.str_replace( array( '*', '?' ), '%', $query ).'%';
        if( is_array( $projects ) && count( $projects ) )                                           //  if filtered b$
            $conditions['projectId']    = $projects;                                                //  apply project$
        foreach( $additionalConditions as $key => $value )
            $conditions[$key]           = $value;
        return $this->logic->getUserMissions( $userId, $conditions, $orders );
    }


	public function index(){
		$this->assignFilters();
		$session	= $this->session;

	}

	public function ajaxRenderContent(){
		$userId		= $this->session->get( 'userId' );
		$missions	= $this->getFilteredMissions( $userId );

print_m( $missions );
die;

		$this->addData( 'missions', $missions );
		print( $this->view->ajaxRenderContent() );
		exit;
	}
}
?>
