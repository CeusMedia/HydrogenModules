<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Info extends Controller
{
	public function index( $arg1, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL, $arg5 = NULL )
	{
		$this->addData( 'site', join( "/", func_get_args() ) );
	}
}
