<?php
/**
 *	Button Lab Controller.
 *	@category		CMF.Hydrogen
 *	@package		Modules.Lab.Controller
 *	@version		$Id$
 */

use CeusMedia\HydrogenFramework\Controller;

/**
 *	Button Lab Controller.
 *	@category		CMF.Hydrogen
 *	@package		Modules.Lab.Controller
 *	@version		$Id$
 */
class Controller_Lab_Button extends Controller{

	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL ){

		$config		= $this->env->getConfig();
		$request	= $this->env->getRequest();
		$session	= $this->env->getSession();

		if( $request->get( 'style' ) )
			$session->set( 'style', $request->get( 'style' ) );
		$style		= $session->get( 'style' );

		$path		= $config->get( 'path.themes' ).$config->get( 'layout.theme' ).'/css/';
		$index		= new FS_File_RegexFilter( $path, "/^site\.lab\.button.+\.css$/i" );
		$styles		= [];
		foreach( $index as $entry )
			$styles[]	= $entry->getFilename();
		natcasesort( $styles );
		if( !in_array( $style, $styles ) )
			$style	= $styles[0];

		$this->env->getPage()->addThemeStyle( $style );
		$this->addData( 'styles', $styles );
		$this->addData( 'style', $style );

		if(func_num_args () ){
			print_m( func_get_args() );
		}

	}
}
?>
