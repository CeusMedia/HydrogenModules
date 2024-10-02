<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Database_Lock extends AjaxController
{
	protected Model_Lock $model;

	/**
	 *	@param		string		$panelId
	 *	@return		int
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function renderDashboardPanel( string $panelId ): int
	{
		$modelUser	= new Model_User( $this->env );
		$locks		= $this->model->getAll();
		foreach( $locks as $lock )
			$lock->user	= $modelUser->get( $lock->userId );
		$helper		= new View_Helper_Database_Dashboard_Locks( $this->env );
		$helper->setLocks( $locks );
		return $this->respondData( $helper->render() );
	}

	protected function __onInit(): void
	{
		$this->model	= new Model_Lock( $this->env );
	}
}
