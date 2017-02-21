<?php
class Controller_Info_Dashboard extends CMF_Hydrogen_Controller{

	protected $panels	= array();

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->model	= new Model_Dashboard( $this->env );
		$logicAuth		= Logic_Authentication::getInstance( $this->env );
		$this->userId	= $logicAuth->getCurrentUserId( FALSE );
		$this->addData( 'currentUserId', $this->userId );
		$this->env->getCaptain()->callHook( 'Dashboard', 'registerPanels', $this );
		$this->addData( 'panels', $this->panels );
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			$title	= trim( $this->request->get( 'title' ) );
			$panels	= $this->request->get( 'panels' );
			if( !( is_array( $panels ) && count( $panels ) ) )
				$panels	= array();
			$select	= $this->request->has( 'select' );
			$this->addUserDashboard( $this->userId, $title, $panels, $select );
			$this->messenger->noteSuccess( 'Dashboard "%s" has been created.', $title );
			$this->restart( NULL, TRUE );
		}
	}

	/**
	 *	@todo  			move to (yet not existing) logic class
	 */
	protected function addUserDashboard( $userId, $title, $panels = array(), $select = FALSE ){
		$dashboardId	= $this->model->add( array(
			'userId'		=> $userId,
			'title'			=> $title,
			'panels'		=> join( ',', $panels ),
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		) );
		if( count( $this->getUserDashboard( $userId ) ) === 1 || $select )
			$this->setUserDashboard( $userId, $dashboardId );
		return $dashboardId;
	}

	/**
	 *	@todo  			move to (yet not existing) logic class
	 */
	protected function addPanelToUserDashboard( $userId, $panelId, $position = 'bottom' ){
		$dashboard	= $this->getUserDashboard( $userId );
		if( !$dashboard )
			throw new RuntimeException( 'No active dashboard available for user' );
		$panels		= strlen( $dashboard->panels ) ? explode( ',', $dashboard->panels ) : array();
		switch( $position ){
			case 'top':
				array_unshift( $panels, $panelId );
				break;
			case 'bottom':
			default:
				array_push( $panels, $panelId );
				break;
		}
		$this->model->edit( $dashboard->dashboardId, array(
			'panels'		=> implode( ',', $panels ),
			'modifiedAt'	=> time()
		) );
	}

	/**
	 *	@todo  			move to (yet not existing) logic class
	 */
	protected function getUserDashboard( $userId ){
		return $this->model->getByIndices( array( 'userId' => $userId, 'isCurrent' => 1  ) );
	}

	public function addPanels(){
		$dashboard	= $this->getUserDashboard( $this->userId );
		if( !$dashboard ){
			$this->env->getMessenger()->noteError( 'No dashboard available. Please create one first!' );
			$this->restart( NULL, TRUE );
		}
		$panels		= $this->request->get( 'panels' );
		$positions	= $this->request->get( 'positions' );
		$panels		= is_array( $panels ) ? $panels : array();
		if( strlen( trim( $dashboard->panels ) ) )
			$dashboard->panels	= explode( ',', $dashboard->panels );
		else
			$dashboard->panels	= array();
		foreach( $panels as $panelId ){
			$panelId	= trim( $panelId );
			if( in_array( $panelId, $this->panels ) )
				continue;
			$position	= isset( $positions[$panelId] ) ? $positions[$panelId] : NULL;
			$this->addPanelToUserDashboard( $this->userId, $panelId, $position );
		}
		$this->env->getMessenger()->noteSuccess( 'Panel(s) added.' );
		$this->restart( NULL, TRUE );
	}

	public function ajaxSaveOrder(){
		$dashboard	= $this->getUserDashboard( $this->userId );
		if( !$dashboard ){
			$this->env->getMessenger()->noteError( 'No dashboard available. Please create one first!' );
			$this->restart( NULL, TRUE );
		}
		$this->model->edit( $dashboard->dashboardId, array(
			'panels'		=> $this->request->get( 'list' ),
			'modifiedAt'	=> time(),
		) );
		print( json_encode( TRUE ) );
		exit;
	}

	public function index(){
		try{
			if( $this->userId /*&& $this->moduleConfig->get( 'user.enabled' )*/){
				/*
				if( $this->moduleConfig->get( 'user.autoCreate' ) )
					if( !$this->model->count( array( 'userId' => $this->userId ) ) )
						$this->model->add( array(
							'userId'		=> $this->userId,
							'title'			=> 'Standard-Dashboard',
							'createdAt'		=> time(),
							'modifiedAt'	=> time(),
							) );
				*/
				$this->addData( 'dashboard', $this->getUserDashboard( $this->userId ) );
				$this->addData( 'dashboards', $this->getUserDashboards( $this->userId ) );
			}
		}
		catch( Exception $e ){
			$this->messenger->noteError( $e->getMessage() );
		}
	}

	protected function getUserDashboards( $userId ){
		return $this->model->getAllByIndices( array(
			'userId' => $userId
		), array( 'modifiedAt'	=> 'DESC' ) );
	}

	public function registerPanel( $panelId, $data ){
		$data		= array_merge( array(
			'id'		=> $panelId,
			'url'		=> NULL,
			'title'		=> 'Untitled',
			'heading'	=> 'Untitled',
			'mode'		=> 'fixed',
			'cols'		=> 1,
			'rows'		=> 1,
			'rank'		=> '50',
			'refresh'	=> 0
		), $data );
		$this->panels[$panelId]	= (object) $data;
	}

	public function remove( $dashboardId ){
		$this->model->remove( $dashboardId );
		$dashboard	= $this->model->getByIndices( array(
			'userId'		=> $this->userId,
		), array( 'modifiedAt' => 'DESC' ) );
		if( $dashboard )
			$this->setUserDashboard( $this->userId, $dashboard->dashboardId );
		$this->restart( NULL, TRUE );
	}

	public function removePanel( $panelId ){
		$dashboard	= $this->getUserDashboard( $this->userId );
		if( !$dashboard ){
			$this->env->getMessenger()->noteError( 'No dashboard available. Please create one first!' );
			$this->restart( NULL, TRUE );
		}
		$panels		= strlen( $dashboard->panels ) ? explode( ',', $dashboard->panels ) : array();
		unset( $panels[array_search( $panelId, $panels )] );
		$this->model->edit( $dashboard->dashboardId, array(
			'panels'		=> implode( ',', $panels ),
			'modifiedAt'	=> time()
		) );
		$this->env->getMessenger()->noteSuccess( 'Panel removed.' );
		$this->restart( NULL, TRUE );
	}

	public function ajaxRename(){
		$dashboardId	= trim( $this->request->get( 'dashboardId' ) );
		$title			= trim( $this->request->get( 'title' ) );
		$result			= FALSE;
		if( !strlen( $title ) ){
			print( json_encode( -1 ) );
			exit;
		}
		foreach( $this->getUserDashboards( $this->userId ) as $entry ){
			if( $entry->title === $title ){
				print( json_encode( -2 ) );
				exit;
			}
		}
		$dashboard	= $this->getUserDashboard( $this->userId );
		if( !$dashboard ){
			print( json_encode( -3 ) );
			exit;
		}
		$this->model->edit( $dashboard->dashboardId, array(
			'title'			=> $title,
			'modifiedAt'	=> time(),
		) );
		print( json_encode( TRUE ) );
		exit;
	}

	public function select( $dashboardId ){
		try{
			$this->setUserDashboard( $this->userId, $dashboardId );
		}
		catch( Exception $e ){
			$this->env->getMessenger()->noteError( 'Invalid dashboard ID.' );
		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@todo  			move to (yet not existing) logic class
	 */
	protected function setUserDashboard( $userId, $dashboardId ){
		$dashboard	= $this->model->getByIndices( array(
			'dashboardId'	=> $dashboardId,
			'userId'		=> $userId
		) );
		if( !$dashboard )
			throw new RangeException( 'Invalid dashboard ID' );
		$current	= $this->getUserDashboard( $userId );
		if( $current )
			$this->model->edit( $current->dashboardId, array( 'isCurrent' => 0 ) );
		$this->model->edit( $dashboard->dashboardId, array( 'isCurrent' => 1 ) );
	}
}
?>
