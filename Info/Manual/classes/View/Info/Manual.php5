<?php
class View_Info_Manual extends CMF_Hydrogen_View{

	public function add(){
	}

	public function edit(){
	}

	public function index(){
	}

	public function view(){
	}

	public function urlencode( $name ){
		return str_replace( "%2F", "/", rawurldecode( $name ) );
	}

}
?>
