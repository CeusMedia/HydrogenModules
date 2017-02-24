<?php
class Controller_Info_Dashboard extends CMF_Hydrogen_Controller{

	protected $logic;
	protected $messenger;
	protected $model;
	protected $moduleConfig;
	protected $panels			= array();
	protected $request;
	protected $session;
	protected $userId			= 0;

	public function __onInit(){
		/*  --  ENV RESOURCES  --  */
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();

		/*  --  MODULE RESOURCES  --  */
		$this->logic		= Logic_Info_Dashboard::getInstance( $this->env );
		$this->model		= new Model_Dashboard( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.info_dashboard.', TRUE );
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->messages	= (object) $this->getWords( 'msg', 'info/dashboard' );

		/*  --  USER SUPPORT  --  */
		if( $this->env->getModules()->has( 'Resource_Authentication' ) ){
			$logicAuth		= Logic_Authentication::getInstance( $this->env );
			$this->userId	= $logicAuth->getCurrentUserId( FALSE );
		}
		$this->addData( 'currentUserId', $this->userId );

		/*  --  REGISTER PANELS  --  */
		$this->env->getCaptain()->callHook( 'Dashboard', 'registerPanels', $this );
		$this->addData( 'panels', $this->panels );
	}

	public function add(){
		try{
			$this->checkUserDashboardsEnabled();
			if( $this->request->has( 'save' ) ){
				$title	= trim( $this->request->get( 'title' ) );
				$desc	= trim( $this->request->get( 'description' ) );
				$panels	= $this->request->get( 'panels' );
				if( !( is_array( $panels ) && count( $panels ) ) )
					$panels	= array();
				$select	= $this->request->has( 'select' );
				$this->logic->addUserDashboard( $this->userId, $title, $desc, $panels, $select );
				$this->messenger->noteSuccess( $this->messages->successDashboardAdded, $title );
				$this->restart( NULL, TRUE );
			}
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $this->message->errorException, $e->getMessage() );
			$this->restart( NULL, TRUE );
		}
	}

	public function addPanels(){
		try{
			$this->checkUserDashboardsEnabled();
			if( !( $dashboard = $this->logic->getUserDashboard( $this->userId ) ) ){
				$this->env->getMessenger()->noteError( $this->messages->errorNoActiveUserDashboard );
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
					$this->logic->addPanelToUserDashboard( $this->userId, $panelId, $position );
					$count++;
				}
				catch( RangeException $e ){
					$this->messenger->noteError( $this->messages->errorPanelLimitReached );
					break;
				}
			}
			if( $count > 1 )
				$this->messenger->noteSuccess( $this->messages->successPanelsAdded, $count );
			else if( $count )
				$this->messenger->noteSuccess( $this->messages->successPanelAdded, $this->panels[$panelId]->title );
			$this->restart( NULL, TRUE );
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $this->message->errorException, $e->getMessage() );
			$this->restart( NULL, TRUE );
		}
	}

	public function ajaxRename(){
		$this->checkUserDashboardsEnabled();
		$dashboardId	= trim( $this->request->get( 'dashboardId' ) );
		$title			= trim( $this->request->get( 'title' ) );
		$result			= FALSE;
		if( !strlen( $title ) ){
			print( json_encode( -1 ) );
			exit;
		}
		if( !( $dashboard = $this->logic->getUserDashboard( $this->userId ) ) ){
			print( json_encode( -11 ) );
			exit;
		}
		foreach( $this->logic->getUserDashboards( $this->userId ) as $entry ){
			if( $entry->title === $title ){
				print( json_encode( -2 ) );
				exit;
			}
		}
		print( json_encode( (bool) $this->model->edit( $dashboard->dashboardId, array(
			'title'			=> $title,
			'modifiedAt'	=> time(),
		) ) ) );
		exit;
	}

	public function ajaxSaveOrder(){
		if( !$this->checkUserDashboardsEnabled( FALSE ) ){
			print( json_encode( -11 ) );
			exit;
		}
		if( !( $dashboard = $this->logic->getUserDashboard( $this->userId ) ) ){
			print( json_encode( -3 ) );
			exit;
		}
		$list	= array();
		foreach( $this->request->get( 'list' ) as $panelId )
			if( array_key_exists( $panelId, $this->panels ) )
				$list[]	= $panelId;
		print( json_encode( $this->logic->setUserPanels( $this->userId, $list ) ) );
		exit;
	}

	protected function checkUserDashboardsEnabled( $strict = TRUE ){
		if( $this->logic->checkUserDashboardsEnabled( FALSE ) )
			return TRUE;
		if( $strict ){
			$this->messenger->noteError( $this->messages->errorUserDashboardsDisabled );
			$this->restart( NULL, TRUE );
		}
		return FALSE;
	}

	public function index(){
		try{
			if( $this->checkUserDashboardsEnabled( FALSE ) && $this->userId ){
				if( $this->moduleConfig->get( 'perUser.autoCreate' ) ){
					if( !$this->getUserDashboards( $this->userId ) ){
						$this->logic->addUserDashboard(
							$this->userId,
							'Standard-Dashboard',
							'',
							explode( ',', $this->moduleConfig->get( 'panels' ) ),
							TRUE
						);
					}
				}
				$this->addData( 'dashboard', $this->logic->getUserDashboard( $this->userId, FALSE ) );
				$this->addData( 'dashboards', $this->logic->getUserDashboards( $this->userId ) );
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
			$this->messenger->noteFailure( $this->messages->errorException, $e->getMessage() );
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
		try{
			$this->checkUserDashboardsEnabled();
			if( !( $dashboard = $this->logic->checkUserDashboard( $this->userId, $dashboardId, FALSE ) ) ){
				$this->messenger->noteError( $this->messages->errorInvalidUserDashboard );
				$this->restart( NULL, TRUE );
			}
			$this->model->remove( $dashboardId );
			$dashboard	= $this->model->getByIndices( array(
				'userId'		=> $this->userId,
			), array( 'modifiedAt' => 'DESC' ) );
			if( $dashboard )
				$this->logic->setUserDashboard( $this->userId, $dashboard->dashboardId );
			$this->messenger->noteSuccess( $this->messages->successDashboardRemoved, $dashboart->title );
			$this->restart( NULL, TRUE );
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $this->message->errorException, $e->getMessage() );
			$this->restart( NULL, TRUE );
		}
	}

	public function removePanel( $panelId ){
		try{
			$this->checkUserDashboardsEnabled();
			if( !( $dashboard = $this->logic->getUserDashboard( $this->userId ) ) ){
				$this->messenger->noteError( $this->messages->errorInvalidUserDashboard );
				$this->restart( NULL, TRUE );
			}
			$panels		= strlen( $dashboard->panels ) ? explode( ',', $dashboard->panels ) : array();
			if( !array_key_exists( $panelId, $this->panels ) ){
				$this->messenger->noteError( $this->messages->errorPanelDiscontinued, $panelId );
				$this->restart( NULL, TRUE );
			}

			$panel		= $this->panels[$panelId];
			unset( $panels[array_search( $panelId, $panels )] );
			$this->model->edit( $dashboard->dashboardId, array(
				'panels'		=> implode( ',', $panels ),
				'modifiedAt'	=> time()
			) );
			$this->messenger->noteSuccess( $this->messages->successPanelRemoved, $panel->title );
			$this->restart( NULL, TRUE );
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $this->message->errorException, $e->getMessage() );
			$this->restart( NULL, TRUE );
		}
	}

	public function select( $dashboardId ){
		try{
			$this->checkUserDashboardsEnabled();
			if( !( $dashboard = $this->checkUserDashboard( $this->userId, $dashboardId, FALSE ) ) ){
				$this->messenger->noteError( $this->messages->errorInvalidUserDashboard );
				$this->restart( NULL, TRUE );
			}
			$this->logic->setUserDashboard( $this->userId, $dashboardId );
			$this->restart( NULL, TRUE );
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $this->message->errorException, $e->getMessage() );
			$this->restart( NULL, TRUE );
		}
	}
}
?>
