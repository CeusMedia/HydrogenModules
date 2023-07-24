<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Company_User extends Controller
{
	public function index( $userId = NULL )
	{
		if( $userId !== NULL && strlen( trim( $userId ) ) && (int) $userId > 0 ){
			$this->restart( 'view/'.$userId, TRUE );
		}
	}

	public function view( $userId )
	{
	}
}
