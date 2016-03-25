<?php
class Controller_Info_Blog extends CMF_Hydrogen_Controller{

	protected function __onInit(){

	}

	public function index(){

	}

	public function view( $postId = NULL ){
		if( $postId )
			$this->restart( NULL, TRUE );
	}
}
