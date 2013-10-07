<?php
class Controller_Index extends CMF_Hydrogen_Controller{
	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL){

		if( $this->env->getModules()->has( 'Info_Pages' ) ){
			$path		= trim( $this->env->getRequest()->get( 'path' ) );							//  @todo	kriss: unbind 'path' using router's path key
			$path		= $path ? $path : 'index';
			$this->redirect( 'info/page', 'index', array( 'id' => $path ) );
			return;
		}
		
		$this->addData( 'path', join( '/', func_get_args() ) );
	}
}
?>
