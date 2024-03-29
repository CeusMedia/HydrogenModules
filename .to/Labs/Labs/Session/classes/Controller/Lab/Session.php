<?php
/**
 *	Controller.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 */

use CeusMedia\HydrogenFramework\Controller;

/**
 *	Controller.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 */
class Controller_Lab_Session extends Controller
{
	public function index()
	{
	}

	public function reset()
	{
		$this->env->getSession()->clear();
		session_destroy();
		session_regenerate_id();
		$this->restart( NULL, TRUE );
	}
}
