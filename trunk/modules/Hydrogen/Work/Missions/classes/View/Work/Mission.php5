<?php
/**
 *	View.
 *	@version		$Id$
 */
/**
 *	View.
 *	@version		$Id$
 *	@todo			implement
 *	@todo			code documentation
 */
class View_Work_Mission extends CMF_Hydrogen_View{

	public function onInit(){
		$page	= $this->env->getPage();
		$monthsLong		= array_values( (array) $this->getWords( 'months' ) );
		$monthsShort	= array_values( (array) $this->getWords( 'months-short' ) );
	
		$page->js->addScript( 'var monthNames = '.json_encode( $monthsLong).';' );
		$page->js->addScript( 'var monthNamesShort = '.json_encode( $monthsShort).';' );
	}
	
	public function add(){
		
	}

	public function edit(){
	}

	/**
	 *	Default action view on this view.
	 *	@access		public
	 *	@return		void
	 */
	public function index(){
		$this->config		= $this->env->getConfig();
		$this->session		= $this->env->getSession();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();

		//  your code now!
		//  add a method for every action view you need
		
	}

	public function remove(){
	}
}
?>