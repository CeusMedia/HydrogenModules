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