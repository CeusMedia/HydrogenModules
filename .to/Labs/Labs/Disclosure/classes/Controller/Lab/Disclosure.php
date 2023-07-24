<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Lab_Disclosure extends Controller{
	public function index(){}

	public function clearCache(){
		$this->env->getPage()->js->clearCache();
		$this->restart( NULL, TRUE );
	}
}
?>
