<?php
class View_Manage extends CMF_Hydrogen_View {

	public function index(){
		return $this->loadContent( 'manage', 'index' );
	}
}
?>