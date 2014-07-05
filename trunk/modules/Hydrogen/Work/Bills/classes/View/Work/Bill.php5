<?php
class View_Work_Bill extends CMF_Hydrogen_View{

	public function __onInit(){
		parent::__onInit();
		$page			= $this->env->getPage();
		$session		= $this->env->getSession();
		$monthsLong		= array_values( (array) $this->getWords( 'months' ) );
		$monthsShort	= array_values( (array) $this->getWords( 'months-short' ) );
		$page->js->addScript( 'var monthNames = '.json_encode( $monthsLong).';' );
		$page->js->addScript( 'var monthNamesShort = '.json_encode( $monthsShort).';' );
	}

	public function add(){}
	public function edit(){}
	public function index(){}
	public function remove(){}
	public function graph(){}
}
?>
