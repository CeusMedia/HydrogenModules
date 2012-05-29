<?php
class View_Manage_My_Branch extends CMF_Hydrogen_View{

	public function __onInit(){
		$this->env->getPage()->addScript( '$(document).ready(function(){loadMap( "map_canvas")});' );
	}
	public function index(){}
	public function add(){}
	public function edit(){}
}
?>