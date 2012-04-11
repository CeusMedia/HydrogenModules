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
		$this->logic	= new Logic_Mission( $env );
	}

	public function add(){
		$config			= $this->env->getConfig();
		$session		= $this->env->getSession();
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= $this->getWords( 'add' );

		$content	= $request->get( 'content' );
		$day		= $request->get( 'day' );
		$status		= $request->get( 'status' );
		
		if( $request->get( 'add' ) ){
			if( !$content )
				$messenger->noteError( $words->msgNoContent );
			if( !$messenger->gotError() ){
				$data	= array(
					'content'	=> $content,
					'priority'	=> (int) $request->get( 'priority' ),
					'status'	=> $status,
					'day'		=> $this->logic->getDate( $day ),
					'reference'	=> $request->get( 'priority' ),
					'createdAt'	=> time(),
				);
				$this->model->add( $data );
				$messenger->noteSuccess( $words->msgSuccess );
				$this->restart( './work/mission' );
			}
		}
		$mission	= array();
		foreach( $this->model->getColumns() as $key )
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
		$day		= $request->get( 'day' );
		$status		= $request->get( 'status' );
		
		if( $request->get( 'edit' ) ){
			if( !$content )
				$messenger->noteError( $words->msgNoContent );
			if( !$messenger->gotError() ){
				$data	= array(
					'content'		=> $content,
					'status'		=> $status,
					'day'			=> $this->logic->getDate( $day ),
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

	public function filter(){
		$request		= $this->env->getRequest();
		$session		= $this->env->getSession();
		if( $request->has( 'reset' ) ){
			$session->remove( 'filter_mission_query' );
			$session->remove( 'filter_mission_order' );
			$session->remove( 'filter_mission_direction' );
		}
		else{
			if( $request->has( 'query' ) )
				$session->set( 'filter_mission_query', $request->get( 'query' ) );
			if( $request->has( 'order' ) )
				$session->set( 'filter_mission_order', $request->get( 'order' ) );
			if( $request->has( 'direction' ) )
				$session->set( 'filter_mission_direction', $request->get( 'direction' ) );
		}
		$this->restart( '', TRUE );
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

		$direction	= $session->get( 'filter_mission_direction' );
		$order		= $session->get( 'filter_mission_order' );
		$query		= $session->get( 'filter_mission_query' );

		$direction	= $direction ? $direction : 'ASC';
		$order		= $order ? array( $order => $direction ) : array();
		
		$conditions	= array( 'status' => '>=-1' );
		if( strlen( $query ) )
			$conditions['content']	= '%'.str_replace( array( '*', '?' ), '%', $query ).'%';
		
		$missions	= $this->model->getAll( $conditions, $order );
		$this->addData( 'missions', $missions );
	}

	public function setPriority( $missionId, $priority ){
		$this->model->edit( $missionId, array( 'priority' => $priority ) );
		$this->restart( 'edit/'.$missionId, TRUE );
	}

	public function setStatus( $missionId, $status ){
		$this->model->edit( $missionId, array( 'status' => $status ) );
		$this->restart( 'edit/'.$missionId, TRUE );
	}
}
?>
