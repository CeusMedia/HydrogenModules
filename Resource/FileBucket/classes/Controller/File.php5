<?php
class Controller_File extends CMF_Hydrogen_Controller{

	public function __onInit(){
		$this->logic		= new Logic_FileBucket( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.resource_file.', TRUE );

		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'sourcePath', $this->logic->getPath() );
	}

	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $args4 = NULL, $arg5 = NULL, $arg6 = NULL, $arg7 = NULL, $arg8 = NULL ){
		$this->addData( 'uriPath', implode( "/", func_get_args() ) );
		$this->addData( 'file', $this->logic->getByPath( $uriPath ) );
	}
}
?>
