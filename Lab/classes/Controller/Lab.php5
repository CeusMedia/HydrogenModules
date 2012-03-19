<?php
/**
 *	Lab Controller.
 *	@category		CMF.Hydrogen
 *	@package		Modules.Lab.Controller
 *	@version		$Id$
 */
/**
 *	Lab Controller.
 *	@category		CMF.Hydrogen
 *	@package		Modules.Lab.Controller
 *	@version		$Id$
 */
class Controller_Lab extends CMF_Hydrogen_Controller{

	public function index( $arg1, $arg2, $arg3, $arg4 ){
		if(func_num_args () ){
			print_m( func_get_args() );
		}
		
	}

/*	public function exec( $command ){
		switch( $command ){
			default:
				break;
		}
	}*/
}
?>