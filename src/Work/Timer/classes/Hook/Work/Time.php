<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Work_Time extends Hook
{
	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function onAuthBeforeLogout(): void
	{
		$data	= new Dictionary( $this->payload );
		if( ( $userId = $data->get( 'userId' ) ) ){
			$modelTimer	= new Model_Work_Timer( $this->env );
			$indices	= ['userId' => $userId, 'status' => 1];
			$active		= $modelTimer->getByIndices( $indices );
			if( $active ){
				$logic	= Logic_Work_Timer::getInstance( $this->env );
				$logic->pause( $active->workTimerId );
			}
		}
	}

	/**
	 *	@return		void
	 */
	public function onDashboardRegisterDashboardPanels(): void
	{
		if( !$this->env->getAcl()->has( 'ajax/work/time', 'renderDashboardPanel' ) )
			return;
		$this->context->registerPanel( 'work-timer-my', [
			'url'			=> 'ajax/work/time/renderDashboardPanel',
			'title'			=> 'Aktivität: Meine',
			'heading'		=> 'Meine letzte Aktivität',
			'icon'			=> 'fa fa-fw fa-play',
			'rank'			=> 10,
		] );
		$this->context->registerPanel( 'work-timer-others', [
			'url'			=> 'work/time/ajaxRenderDashboardPanel',
			'title'			=> 'Aktivität: Andere',
			'heading'		=> 'Aktivitäten der Anderen',
			'rank'			=> 20,
			'refresh'		=> 10,
		] );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function onProjectRemove(): void
	{
		$projectId	= $this->payload['projectId'];
		$modelTimer	= new Model_Work_Timer( $this->env );
		$modelTimer->removeByIndex( 'projectId', $projectId );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function onEnvCallForModules(): void
	{
		$context	= new View_Helper_Work_Time_Timer( $this->env );
		$payload	= [];
		$this->env->getCaptain()->callHook( 'Work_Timer', 'registerModule', $context, $payload );
	}

	/**
	 *	@return		void
	 */
	public function onWorkTimeRegisterTab(): void
	{
		$words	= (object) $this->env->getLanguage()->getWords( 'work/time' );			//  load words
		$this->context->registerTab( '', $words->tabs['dashboard'], 0 );						//  register main tab
//		$this->context->registerTab( 'archive', $words->tabs['archive'], 1 );					//  register main tab
//		$this->context->registerTab( 'report', $words->tabs['report'], 2 );						//  register main tab
	}

/*	public function onRenderDashboardPanels(): void
	{
		$helper	= new View_Helper_Work_Time_Dashboard_My( $this->env );
		$context->registerPanel( 'work-timer-my', 'Letzte Aktivität', $helper->render(), '1col-fixed', 10 );

		$helper	= new View_Helper_Work_Time_Dashboard_Others( $this->env );
		$context->registerPanel( 'work-timer-others', 'Aktivitäten Anderer', $helper->render(), '3col-flex', 10 );
	}*/

	/**
	 *	@return		void
	 */
	public function onWorkTimeRegisterAnalysisTab(): void
	{
		$words	= (object) $this->env->getLanguage()->getWords( 'work/time' );				//  load words
//		$this->context->registerTab( '', $words->tabs['dashboard'], 0 );							//  register main tab
		$this->context->registerTab( 'analysis', $words->tabs['analysis'], 2 );						//  register main tab
//		$this->context->registerTab( 'report', $words->tabs['report'], 2 );							//  register main tab
	}

	/**
	 *	@return		void
	 */
	public function onWorkTimeRegisterArchiveTab(): void
	{
		$words	= (object) $this->env->getLanguage()->getWords( 'work/time' );				//  load words
//		$this->context->registerTab( '', $words->tabs['dashboard'], 0 );							//  register main tab
		$this->context->registerTab( 'archive', $words->tabs['archive'], 1 );						//  register main tab
//		$this->context->registerTab( 'report', $words->tabs['report'], 2 );							//  register main tab
	}
}
