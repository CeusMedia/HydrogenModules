<?php
class Job_Abstract{
	
	/**	@var	CMF_Hydrogen_Environment_Abstract	$env		Environment object */
	protected $env;
	
	public function __construct( $env ){
		$this->env	= $env;
		$this->__onInit();
	}

	protected function __onInit(){
	}

	public function out( $message ){
		print( $message."\n" );
	}
}
?>
