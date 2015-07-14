<?php
class Controller_Index extends CMF_Hydrogen_Controller{

	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL){
		$request	= $this->env->getRequest();
		if( $request->get( 'from' ) ){
			$this->restart( $request->get( 'from' ) );
		}
		$path	= $request->get( '__path' );
		if(  !in_array( $path, array( '', 'index', 'index/index' ) ) ){
			$words	= (object) $this->getWords( 'index', 'main' );
			$this->env->getMessenger()->noteNotice( $words->msgPageNotFound );
			$this->env->getResponse()->setStatus( 404 );
		}
		$this->addData( 'path', join( '/', func_get_args() ) );				//  @todo deprecated: remove after updating all instances
	}
}
?>
