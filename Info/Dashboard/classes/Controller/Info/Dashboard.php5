<?php
class Controller_Info_Dashboard extends CMF_Hydrogen_Controller{

	protected $panels	= array();

	public function __onInit(){
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();
		$this->model		= new Model_Dashboard( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.info_dashboard.', TRUE );
		$logicAuth		= Logic_Authentication::getInstance( $this->env );
		$this->userId	= $logicAuth->getCurrentUserId( FALSE );
		$this->addData( 'currentUserId', $this->userId );
		$this->env->getCaptain()->callHook( 'Dashboard', 'registerPanels', $this );
		$this->addData( 'panels', $this->panels );
		$this->addData( 'moduleConfig', $this->moduleConfig );
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			$title	= trim( $this->request->get( 'title' ) );
			$desc	= trim( $this->request->get( 'description' ) );
			$panels	= $this->request->get( 'panels' );
			if( !( is_array( $panels ) && count( $panels ) ) )
				$panels	= array();
			$select	= $this->request->has( 'select' );
			$this->addUserDashboard( $this->userId, $title, $desc, $panels, $select );
			$this->messenger->noteSuccess( 'Dashboard "%s" has been created.', $title );
			$this->restart( NULL, TRUE );
		}
	}

	public function addPanels(){
		$this->checkUserDashboardsEnabled();
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
		$count	= 0;
		foreach( $panels as $panelId ){
			$panelId	= trim( $panelId );
			if( in_array( $panelId, $this->panels ) )
				continue;
			$position	= isset( $positions[$panelId] ) ? $positions[$panelId] : NULL;
			try{
				$this->addPanelToUserDashboard( $this->userId, $panelId, $position );
				$count++;
			}
			catch( RangeException $e ){
				$message	= 'Maximale Anzahl an Panels erreicht. Weitere werden nicht gespeichert.';
				$this->messenger->noteError( $message );
				break;
			}
		}
		if( $count )
			$this->env->getMessenger()->noteSuccess( '%s Panel(s) hinzugefügt.', $count );
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

	protected function checkUserDashboardsEnabled( $strict = TRUE ){
		if( $this->moduleConfig->get( 'perUser' ) )
			return TRUE;
		if( $strict ){
			$this->env->getMessenger()->noteError( 'User dashboards are not enabled.' );
			$this->restart( NULL, TRUE );
		}
		return FALSE;
	}

	public function index(){
		try{
			if( $this->checkUserDashboardsEnabled( FALSE ) && $this->userId ){
				if( $this->moduleConfig->get( 'perUser.autoCreate' ) ){
					if( !$this->getUserDashboards( $this->userId ) ){
						$this->addUserDashboard(
							$this->userId,
							'Standard-Dashboard',
							'',
							explode( ',', $this->moduleConfig->get( 'panels' ) ),
							TRUE
						);
					}
				}
				$this->addData( 'dashboard', $this->getUserDashboard( $this->userId ) );
				$this->addData( 'dashboards', $this->getUserDashboards( $this->userId ) );
			}
			else{
				$this->addData( 'dashboard', (object) array(
					'dashboardId'	=> 0,
					'title'			=> '',
					'description'	=> '',
					'panels'		=> $this->moduleConfig->get( 'panels' ),
					'isCurrent'		=> TRUE,
				) );
				$this->addData( 'dashboards', array() );
			}
		}
		catch( Exception $e ){
			$this->messenger->noteError( $e->getMessage() );
		}
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
			'icon'		=> NULL,
			'refresh'	=> 0
		), $data );
		$this->panels[$panelId]	= (object) $data;
	}

	public function remove( $dashboardId ){
		$this->checkUserDashboardsEnabled();
		$this->model->remove( $dashboardId );
		$dashboard	= $this->model->getByIndices( array(
			'userId'		=> $this->userId,
		), array( 'modifiedAt' => 'DESC' ) );
		if( $dashboard )
			$this->setUserDashboard( $this->userId, $dashboard->dashboardId );
		$this->restart( NULL, TRUE );
	}

	public function removePanel( $panelId ){
		$this->checkUserDashboardsEnabled();
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

	public function select( $dashboardId ){
		$this->checkUserDashboardsEnabled();
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
	protected function addUserDashboard( $userId, $title, $description, $panels = array(), $select = FALSE ){
		$dashboardId	= $this->model->add( array(
			'userId'		=> $userId,
			'title'			=> $title,
			'description'	=> $description,
			'panels'		=> join( ',', $panels ),
			'createdAt'		=> time(),
			'modifiedAt'	=> time(),
		) );
		if( count( $this->getUserDashboards( $userId ) ) === 1 || $select )
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
		if( count( $panels ) >= $this->moduleConfig->get( 'perUser.maxPanels' ) )
			throw new RangeException( 'Maximum panels limit reached.' );
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

	/**
	 *	@todo  			move to (yet not existing) logic class
	 */
	protected function getUserDashboards( $userId ){
		return $this->model->getAllByIndices( array(
			'userId' => $userId
		), array( 'modifiedAt'	=> 'DESC' ) );
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
