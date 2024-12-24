<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Info_Novelty extends AjaxController
{
	/**
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function dismiss(): void
	{
		$logicAuth	= Logic_Authentication::getInstance( $this->env );
		$userId		= $logicAuth->getCurrentUserId();
		$model		= new Model_Novelty( $this->env );
		$data		= $this->env->getRequest()->getAllFromSource( 'POST', TRUE );
		$model->add( [
			'userId'	=> $userId,
			'entryId'	=> $data->get( 'id' ),
			'type'		=> $data->get( 'type' ),
			'timestamp'	=> $data->get( 'timestamp' ),
		] );
		$this->respondData( TRUE );
	}

	/**
	 *	@param		int|string		$panelId
	 *	@return		void
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 */
	public function renderDashboardPanel( int|string $panelId ): void
	{
		$helper		= new View_Helper_Info_Novelty_DashboardPanel( $this->env );
		$helper->setLimit( 10 );
//		$helperNews	= new View_Helper_NewsList( $this->env );
//		$helperNews->collect( 'Page', 'collectNews', [] );
		$this->respondData( $helper->render() );
	}
}
