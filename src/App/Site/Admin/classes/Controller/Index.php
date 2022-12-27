<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Index extends Controller
{
	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL ): void
	{
		$this->addData( 'isInside', $this->env->getSession()->has( 'userId' ) );
	}
}
