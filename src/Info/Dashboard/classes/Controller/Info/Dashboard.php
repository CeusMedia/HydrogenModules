<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Info_Dashboard extends Controller
{
	protected $messenger;
	protected $request;
	protected $session;
	protected Logic_Info_Dashboard $logic;
	protected Model_Dashboard $model;
	protected array $panels					= [];
	protected string $userId				= '0';
	protected object $messages;
	protected ?object $user					= NULL;

	public function add()
	{
		try{
			$this->checkUserDashboardsEnabled();
			if( $this->request->has( 'save' ) ){
				$title	= trim( $this->request->get( 'title' ) );
				$desc	= trim( $this->request->get( 'description' ) );
				$panels	= $this->request->get( 'panels' );
				if( !( is_array( $panels ) && count( $panels ) ) )
					$panels	= [];
				$select	= $this->request->has( 'select' );
				$this->logic->addUserDashboard( $this->userId, $title, $desc, $panels, $select );
				$this->messenger->noteSuccess( $this->messages->successDashboardAdded, $title );
				$this->restart( NULL, TRUE );
			}
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $this->messages->errorException, $e->getMessage() );
			$this->restart( NULL, TRUE );
		}
	}

	public function addPanels()
	{
		try{
			$this->checkUserDashboardsEnabled();
			if( !( $dashboard = $this->logic->getUserDashboard( $this->userId ) ) ){
				$this->env->getMessenger()->noteError( $this->messages->errorNoActiveUserDashboard );
				$this->restart( NULL, TRUE );
			}
			$panels		= $this->request->get( 'panels' );
			$positions	= $this->request->get( 'positions' );
			$panels		= is_array( $panels ) ? $panels : [];
			if( strlen( trim( $dashboard->panels ) ) )
				$dashboard->panels	= explode( ',', $dashboard->panels );
			else
				$dashboard->panels	= [];
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
			$this->messenger->noteFailure( $this->messages->errorException, $e->getMessage() );
			$this->restart( NULL, TRUE );
		}
	}

	public function index()
	{
		try{
			if( $this->checkUserDashboardsEnabled( FALSE ) && $this->userId ){
				if( $this->moduleConfig->get( 'perUser.autoCreate' ) ){
					if( !$this->logic->getUserDashboards( $this->userId ) ){
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
				$this->addData( 'dashboards', [] );
			}
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $this->messages->errorException, $e->getMessage() );
		}
		$this->addData( 'user', $this->user );
	}

	public function registerPanel( $panelId, $data )
	{
		$data		= array_merge( [
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
		], $data );
		$this->panels[$panelId]	= (object) $data;
	}

	public function remove( $dashboardId )
	{
		try{
			$this->checkUserDashboardsEnabled();
			if( !( $dashboard = $this->logic->checkUserDashboard( $this->userId, $dashboardId, FALSE ) ) ){
				$this->messenger->noteError( $this->messages->errorInvalidUserDashboard );
				$this->restart( NULL, TRUE );
			}
			$this->model->remove( $dashboardId );
			$dashboard	= $this->model->getByIndices( [
				'userId'		=> $this->userId,
			], ['modifiedAt' => 'DESC'] );
			if( $dashboard )
				$this->logic->setUserDashboard( $this->userId, $dashboard->dashboardId );
			$this->messenger->noteSuccess( $this->messages->successDashboardRemoved, $dashboard->title );
			$this->restart( NULL, TRUE );
		}
		catch( Exception $e ){
			$this->messenger->noteFailure( $this->messages->errorException, $e->getMessage() );
			$this->restart( NULL, TRUE );
		}
	}

	public function removePanel( $panelId )
	{
		try{
			$this->checkUserDashboardsEnabled();
			if( !( $dashboard = $this->logic->getUserDashboard( $this->userId ) ) ){
				$this->messenger->noteError( $this->messages->errorInvalidUserDashboard );
				$this->restart( NULL, TRUE );
			}
			$panels		= strlen( $dashboard->panels ) ? explode( ',', $dashboard->panels ) : [];
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
			$this->messenger->noteFailure( $this->messages->errorException, $e->getMessage() );
			$this->restart( NULL, TRUE );
		}
	}

	public function select( $dashboardId )
	{
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
			$this->messenger->noteFailure( $this->messages->errorException, $e->getMessage() );
			$this->restart( NULL, TRUE );
		}
	}

	protected function __onInit(): void
	{
		/*  --  ENV RESOURCES  --  */
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();

		/*  --  MODULE RESOURCES  --  */
		$this->logic		= Logic_Info_Dashboard::getInstance( $this->env );
		$this->model		= new Model_Dashboard( $this->env );
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->messages		= (object) $this->getWords( 'msg', 'info/dashboard' );

		/*  --  USER SUPPORT  --  */
		if( $this->env->getModules()->has( 'Resource_Authentication' ) ){
			$logicAuth		= Logic_Authentication::getInstance( $this->env );
			$this->userId	= (string) $logicAuth->getCurrentUserId( FALSE );
			$this->user		= $logicAuth->getCurrentUser( FALSE, TRUE );
		}
		$this->addData( 'currentUserId', $this->userId );

		/*  --  REGISTER PANELS  --  */
		$this->env->getCaptain()->callHook( 'Dashboard', 'registerPanels', $this );
		$this->addData( 'panels', $this->panels );
	}

	protected function checkUserDashboardsEnabled( bool $strict = TRUE ): bool
	{
		if( $this->logic->checkUserDashboardsEnabled( FALSE ) )
			return TRUE;
		if( $strict ){
			$this->messenger->noteError( $this->messages->errorUserDashboardsDisabled );
			$this->restart( NULL, TRUE );
		}
		return FALSE;
	}
}
