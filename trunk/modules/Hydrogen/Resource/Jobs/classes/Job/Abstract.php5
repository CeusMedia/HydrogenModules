<?php
class Job_Abstract{
	public function __construct( $env ){
		$this->env	= $env;
	}

	public function out( $message ){
		print( $message."\n" );
	}
}
?>