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

	public function add(){
		$config			= $this->env->getConfig();
		$session		= $this->env->getSession();
		$request		= $this->env->getRequest();
		$messenger		= $this->env->getMessenger();
		$words			= $this->getWords( 'add' );

		$model		= new Model_Mission( $this->env );
		$content	= $request->get( 'content' );
		$daysLeft	= $request->get( 'daysLeft' );
		$status		= $request->get( 'status' );
		
		if( $request->get( 'add' ) ){
			if( !$content )
				$messenger->noteError( $words->msgNoContent );
			if( !$messenger->gotError() ){
				$data	= array(
					'content'	=> $content,
					'status'	=> $status,
					'daysLeft'	=> $daysLeft,
					'createdAt'	=> time(),
				);
				$model->add( $data );
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

		$model		= new Model_Mission( $this->env );
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
					'modifiedAt'	=> time(),
				);
				$model->edit( $missionId, $data );
				$messenger->noteSuccess( $words->msgSuccess );
				$this->restart( './work/mission' );
			}
		}
		$mission	= $model->get( $missionId );
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

		$model		= new Model_Mission( $this->env );
		$missions	= $model->getAll( array( 'status' => '>=-1', array( 'daysLeft' => 'ASC', 'daysOverdue' => 'DESC' ) ) );
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
