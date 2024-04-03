<?php
/**
 *	Controller.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Controller;

/**
 *	Controller.
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
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
