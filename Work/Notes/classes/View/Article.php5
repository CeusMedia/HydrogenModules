<?php
class View_Article extends CMF_Hydrogen_View{
	public function add(){}
	public function index(){
		$query	= $this->env->getSession()->get( 'search_term' );
		$this->addData( 'query', $query );
	}
	public function view(){}
	public function edit(){}
}
?>