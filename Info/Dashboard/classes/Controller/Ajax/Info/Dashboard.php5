<?php
class Controller_Ajax_Info_Dashboard extends CMF_Hydrogen_Controller_Ajax
{
	protected $logic;
	protected $model;
	protected $panels			= array();
	protected $userId			= 0;

	public function rename()
	{
		if( !$this->logic->checkUserDashboardsEnabled( FALSE ) )
			$this->respondError( -1, 'Dashboards are not enabled for the current user.' );

		$currentDashboard	= $this->logic->getUserDashboard( $this->userId, FALSE );
		if( !$currentDashboard )
			$this->respondError( -2, 'Current user has no active dashboard.' );

		$dashboardId	= trim( $this->request->get( 'dashboardId' ) );
		$title			= trim( $this->request->get( 'title' ) );
		$result			= FALSE;

		if( !strlen( trim( $title ) ) )
			$this->respondError( -3, 'Title is missing.' );

		$dashboards		= $this->logic->getUserDashboards( $this->userId );
		foreach( $dashboards as $entry )
			if( $entry->dashboardId != $currentDashboard->dashboardId )
				if( $entry->title === $title )
					$this->respondError( -4, 'Another dashboard already has this title.' );

		$result	= (bool) $this->model->edit( $currentDashboard->dashboardId, array(
			'title'			=> $title,
			'modifiedAt'	=> time(),
		) );
		$this->respondData( $result );
	}

	public function saveOrder()
	{
		if( !$this->logic->checkUserDashboardsEnabled( FALSE ) )
			$this->respondError( -1, 'Dashboards are not enabled for the current user.' );

		$currentDashboard	= $this->logic->getUserDashboard( $this->userId, FALSE );
		if( !$currentDashboard )
			$this->respondError( -2, 'Current user has no active dashboard.' );

		$list	= array();
		foreach( $this->request->get( 'list' ) as $panelId )
			if( array_key_exists( $panelId, $this->panels ) )
				$list[]	= $panelId;
		$result	= $this->logic->setUserPanels( $this->userId, $list );
		$this->respondData( $result );
	}

	protected function __onInit()
	{
		/*  --  MODULE RESOURCES  --  */
		$this->logic		= Logic_Info_Dashboard::getInstance( $this->env );
		$this->model		= new Model_Dashboard( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.info_dashboard.', TRUE );

		/*  --  USER SUPPORT  --  */
		if( $this->env->getModules()->has( 'Resource_Authentication' ) ){
			$logicAuth		= Logic_Authentication::getInstance( $this->env );
			$this->userId	= $logicAuth->getCurrentUserId( FALSE );
			$this->user		= $logicAuth->getCurrentUser( FALSE, TRUE );
		}
	}
}
