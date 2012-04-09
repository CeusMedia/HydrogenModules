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
class Controller_Work_Mission extends CMF_Hydrogen_Controller{

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ) {
		parent::__construct( $env );
		$this->model	= new Model_Mission( $env );
	 }

	public function add(){
		$config			= $this->env->getConfig();
		$session		= $this->env->getSession();
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= $this->getWords( 'add' );

		$content	= $request->get( 'content' );
		$daysLeft	= $request->get( 'daysLeft' );
		$status		= $request->get( 'status' );
		
		if( $request->get( 'add' ) ){
			if( !$content )
				$messenger->noteError( $words->msgNoContent );
			if( !$messenger->gotError() ){
				$data	= array(
					'content'	=> $content,
					'priority'	=> (int) $request->get( 'priority' ),
					'status'	=> $status,
					'daysLeft'	=> $daysLeft,
					'reference'	=> $request->get( 'priority' ),
					'createdAt'	=> time(),
				);
				$this->model->add( $data );
				$messenger->noteSuccess( $words->msgSuccess );
				$this->restart( './work/mission' );
			}
		}
		$mission	= array();
		foreach( $model->getColumns() as $key )
			$mission[$key]	= strlen( $request->get( $key ) ) ? $request->get( $key ) : NULL;
		$this->addData( 'mission', (object) $mission );
	}

	public function edit( $missionId ){
		$config			= $this->env->getConfig();
		$session		= $this->env->getSession();
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= $this->getWords( 'edit' );

		$content	= $request->get( 'content' );
		$daysLeft	= $request->get( 'daysLeft' );
		$status		= $request->get( 'status' );
		
		if( $request->get( 'edit' ) ){
			if( !$content )
				$messenger->noteError( $words->msgNoContent );
			if( !$messenger->gotError() ){
				$data	= array(
					'content'		=> $content,
					'status'		=> $status,
					'daysLeft'		=> $daysLeft,
					'reference'		=> $request->get( 'reference' ),
					'modifiedAt'	=> time(),
				);
				$this->model->edit( $missionId, $data );
				$messenger->noteSuccess( $words->msgSuccess );
				$this->restart( './work/mission' );
			}
		}
		$mission	= $this->model->get( $missionId );
		$this->addData( 'mission', $mission );
	}

	/**
	 *	Default action on this controller.
	 *	@access		public
	 *	@return		void
	 */
	public function index(){
		$config			= $this->env->getConfig();
		$session		= $this->env->getSession();
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= $this->getWords( 'index' );

		$missions	= $this->model->getAll( array( 'status' => '>=-1', array( 'daysLeft' => 'ASC', 'daysOverdue' => 'DESC' ) ) );
		$this->addData( 'missions', $missions );
	}

	public function filter(){
		$request		= $this->env->getRequest();
		$session		= $this->env->getSession();
		if( $request->has( 'reset' ) )
			$session->remove( 'filter_mission_query' );
		else
			$session->set( 'filter_mission_query', $request->get( 'query' ) );
		$this->restart( '', TRUE );
	}
}
?>
