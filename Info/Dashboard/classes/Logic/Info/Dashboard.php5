<?php
/**
 *	@todo		code doc
 */
class Logic_Info_Dashboard{

	static protected $instance;

	protected $env;
	protected $model;
	protected $moduleConfig;

	/**
	 *	Constructor. Protected to force singleton use.
	 *	@access		protected
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env		Hydrogen framework environment object
	 *	@return		void
	 */
	protected function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env			= $env;
		$this->model		= new Model_Dashboard( $env );
		$this->moduleConfig	= $env->getConfig()->getAll( 'module.info_dashboard.', TRUE );
	}

	/**
	 *	Cloning is disabled to force singleton use.
	 *	@access		protected
	 *	@return		void
	 */
	protected function __clone(){}

	/**
	 *	Get singleton instance of logic.
	 *	@static
	 *	@access		public
	 *	@return		object			Singleton instance of logic
	 */
	static public function getInstance( $env ){
		if( !self::$instance )
			self::$instance	= new self( $env );
		return self::$instance;
	}

	/**
	 *	Adds a new user dashboard.
	 *	@access		public
	 *	@param		integer			$userId			ID of user to assign dashboard to
	 *	@param		string			$title			Title of dashboard (mandatory)
	 *	@param		string			$description	Description of dashboard (optional)
	 *	@param		array|string	$panels			List of panel IDs to add to dashboard as array or comma-separated string
	 *	@param		boolean			$select			Flag: set new dashboard as current
	 *	@return		integer			ID of new user dashboard
	 *	@throws		InvalidArgumentException		if given panels is neither an array nor a valid string
	 */
	public function addUserDashboard( $userId, $title, $description, $panels = array(), $select = FALSE ){
		if( is_string( $panels ) )
			$panels		= strlen( trim( $panels ) ) ? explode( ',', $panels ) : array();
		if( !is_array( $panels ) )
			throw new InvalidArgumentException( 'Panels list must be array or string' );
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
	 *	Adds a panel to current dashboard of user.
	 *	@access		public
	 *	@param		integer			$userId			ID of user to assign panel to
	 *	@param		string			$panelId		ID of panel to add to current dashboard of user
	 *	@param		string			$position		Position in dashboard to add panel to (top|bottom), default: bottom
	 *	@return		boolean
	 *	@throws		DomainException					if user is not having a current dashboard
	 *	@throws		RangeException					if limit of panels per dashboard has been reached
	 */
	public function addPanelToUserDashboard( $userId, $panelId, $position = 'bottom' ){
		$dashboard	= $this->getUserDashboard( $userId );
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
		return (bool) $this->model->edit( $dashboard->dashboardId, array(
			'panels'		=> implode( ',', $panels ),
			'modifiedAt'	=> time()
		) );
	}

	/**
	 *	Indicates whether an user (by its ID) has access to a dashboard (by ID).
	 *	If available, the dashboard data object will be returned.
	 *	If not available an exception will be thrown having strict mode enabled (default).
	 *	Otherwise with strict mode disabed FALSE will be returned.
	 *	@access		public
	 *	@param		integer			$userId			ID of user to assign dashboard to
	 *	@param		integer			$dashboardId	ID of dashboard to check user against to
	 *	@param		boolean			$strict			Flag: throw exception if not available (default), otherwise return FALSE
	 *	@return		object|boolean	Data object of current user dashboard or FALSE if not available and strict mode disabled
	 *	@throws		DomainException					if dashboard is not existing or not assigned to user and strict mode enabled
	 */
	public function checkUserDashboard( $userId, $dashboardId, $strict = TRUE ){
		foreach( $this->getUserDashboards( $userId ) as $dashboard )
			if( $dashboard->dashboardId == $dashboardId )
				return $dashboard;
		if( $strict )
			throw new DomainException( 'Dashboard either not existing or not related to user' );
		return FALSE;
	}

	/**
	 *	Indicates whether user dashboards are enabled by module configuration.
	 *	If not available an exception will be thrown having strict mode enabled (default).
	 *	Otherwise with strict mode disabed FALSE will be returned.
	 *	@access		public
	 *	@param		boolean			$strict			Flag: throw exception if not available (default), otherwise return FALSE
	 *	@return		boolean
	 *	@throws		RuntimeException				if not enabled and strict mode enabled
	 */
	public function checkUserDashboardsEnabled( $strict = TRUE ){
		if( $this->moduleConfig->get( 'perUser' ) )
			return TRUE;
		if( $strict )
			throw new RuntimeException( 'User dashboards are not enabled' );
		return FALSE;
	}

	/**
	 *	Returns current user dashboard if available.
	 *	@access		public
	 *	@param		integer			$userId			ID of user to get current dashboard for
	 *	@param		boolean			$strict			Flag: throw exception if not available (default), otherwise return FALSE
	 *	@return		integer			ID of new user dashboard
	 *	@throws		DomainException					if user is not having a current dashboard
	 */
	public function getUserDashboard( $userId, $strict = TRUE ){
		$dashboard	= $this->model->getByIndices( array( 'userId' => $userId, 'isCurrent' => 1 ) );
		if( $dashboard )
			return $dashboard;
		if( $strict )
			throw new DomainException( 'User has no current dashboard' );
		return FALSE;
	}

	/**
	 *	Returns list of user dashboards.
	 *	List is ordered by last modification date descending.
	 *	@access		public
	 *	@param		integer			$userId			ID of user to assign dashboard to
	 *	@return		array			List of user dashboards
	 */
	public function getUserDashboards( $userId ){
		return $this->model->getAllByIndices( array(
			'userId' => $userId
		), array( 'modifiedAt'	=> 'DESC' ) );
	}

	/**
	 *	Set user dashboard to be current.
	 *	@access		public
	 *	@param		integer			$userId			ID of user
	 *	@param		integer			$dashboardId	ID of dashboard to set as current for user
	 *	@return		boolean			Whether changes has been made or not
	 *	@throws		DomainException					if dashboard is not existing or not assigned to user and strict mode enabled
	 */
	public function setUserDashboard( $userId, $dashboardId ){
		$dashboardToSelect	= $this->checkUserDashboard( $userId, $dashboardId );
		if( ( $dashboardCurrent = $this->getUserDashboard( $userId, FALSE ) ) ){
			if( $dashboardId == $dashboardCurrent->dashboardId )
				return FALSE;
			$this->model->edit( $dashboardCurrent->dashboardId, array( 'isCurrent' => 0 ) );
		}
		$this->model->edit( $dashboardToSelect->dashboardId, array( 'isCurrent' => 1 ) );
		return TRUE;
	}

	/**
	 *	Sets ordered list of panels (by ID) to an user dashboard.
	 *	@access		public
	 *	@param		integer			$userId			ID of user to assign dashboard to
	 *	@param		array|string	$panels			List of panel IDs to set to dashboard as array or comma-separated string
	 *	@return		boolean			Whether changes has been made or not
	 *	@throws		DomainException					if user is not having a current dashboard
	 *	@throws		InvalidArgumentException		if given panels is neither an array nor a valid string
	 */
	public function setUserPanels( $userId, $panels = array() ){
		$dashboard	= $this->getUserDashboard( $userId );
		if( is_string( $panels ) )
			$panels		= strlen( trim( $panels ) ) ? explode( ',', $panels ) : array();
		if( !is_array( $panels ) )
			throw new InvalidArgumentException( 'Panels list must be array or string' );
		$panelsString	= join( ',', $panels );
		if( $dashboard->panels === $panelsString )
			return FALSE;
		return (bool) $this->model->edit( $dashboard->dashboardId, array(
			'panels'		=> $panelsString,
			'modifiedAt'	=> time(),
		) );
	}
}
?>
