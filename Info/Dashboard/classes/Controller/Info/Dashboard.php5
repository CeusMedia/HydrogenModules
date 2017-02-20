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
			$dashboardId	= $this->model->add( array(
				'userId'		=> $this->userId,
				'title'			=> $this->request->get( 'title' ),
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			) );
			$this->setUserDashboard( $this->userId, $dashboardId );
			$this->restart( NULL, TRUE );
		}
	}

	protected function getUserDashboard( $userId ){
		return $this->model->getByIndices( array( 'userId' => $userId, 'isCurrent' => 1  ) );
	}

	protected function setUserDashboard( $userId, $dashboardId ){
		$dashboard	= $this->model->getByIndices( array( 'dashboardId' => $dashboardId, 'userId' => $userId ) );
		if( !$dashboard )
			throw new RangeException( 'Invalid dashboard ID' );
		$current	= $this->getUserDashboard( $userId );
		if( $current )
			$this->model->edit( $current->dashboardId, array( 'isCurrent' => 0 ) );
		$this->model->edit( $dashboard->dashboardId, array( 'isCurrent' => 1 ) );
	}

	public function addPanel( $panelId = NULL, $position = NULL ){
		$panelId	= trim( $panelId ? $panelId : $this->request->get( 'panelId' ) );
		$position	= trim( $position ? $position : $this->request->get( 'position' ) );
		if( !strlen( $panelId ) ){
			$this->env->getMessenger()->noteError( 'No panel ID given.' );
			$this->restart( NULL, TRUE );
		}
		$dashboard	= $this->getUserDashboard( $this->userId );
		if( !$dashboard ){
			$this->env->getMessenger()->noteError( 'No dashboard available. Please create one first!' );
			$this->restart( NULL, TRUE );
		}
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
		$this->env->getMessenger()->noteSuccess( 'Panel added.' );
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
			if( $this->userId ){
				/*			if( !$this->model->count( array( 'userId' => $this->userId ) ) )
				$this->model->add( array(
				'userId'		=> $this->userId,
				'title'			=> 'Standard-Dashboard',
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
				) );*/
				$this->addData( 'dashboard', $this->model->getByIndices( array(
					'userId'	=> $this->userId,
					'isCurrent'	=> 1
				) ) );
			}
			$this->addData( 'dashboards', $this->model->getAllByIndices(
			array( 'userId' => $this->userId ),
			array( 'modifiedAt'	=> 'DESC' )
			) );
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
			'refresh'	=> 0
		), $data );
		$this->panels[$panelId]	= (object) $data;
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

	public function remove( $dashboardId ){
		$this->model->remove( $dashboardId );
		$dashboard	= $this->model->getByIndices( array(
			'userId'		=> $this->userId,
		), array( 'modifiedAt' => 'DESC' ) );
		if( $dashboard )
			$this->setUserDashboard( $this->userId, $dashboard->dashboardId );
		$this->restart( NULL, TRUE );
	}

	public function select( $dashboardId ){
		$dashboard	= $this->model->getByIndices( array(
			'userId'		=> $this->userId,
			'dashboardId'	=> $dashboardId
		) );
		if( !$dashboard ){
			$this->env->getMessenger()->noteError( 'Invalid dashboard ID.' );
			$this->restart( NULL, TRUE );
		}
		$this->setUserDashboard( $this->userId, $dashboardId );
		$this->restart( NULL, TRUE );
	}
}
?>
