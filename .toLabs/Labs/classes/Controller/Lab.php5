<?php
/**
 *	Lab Controller.
 *	@category		CMF.Hydrogen
 *	@package		Modules.Lab.Controller
 *	@version		$Id$
 */

use CeusMedia\HydrogenFramework\Controller;

/**
 *	Lab Controller.
 *	@category		CMF.Hydrogen
 *	@package		Modules.Lab.Controller
 *	@version		$Id$
 */
class Controller_Lab extends Controller{

	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL ){
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
