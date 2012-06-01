<?php
/**
 *	Button Lab Controller.
 *	@category		CMF.Hydrogen
 *	@package		Modules.Lab.Controller
 *	@version		$Id$
 */
/**
 *	Button Lab Controller.
 *	@category		CMF.Hydrogen
 *	@package		Modules.Lab.Controller
 *	@version		$Id$
 */
class Controller_Lab_Button extends CMF_Hydrogen_Controller{

	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL ){
		if(func_num_args () ){
			print_m( func_get_args() );
		}
		
	}
}
?>