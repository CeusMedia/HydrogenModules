<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Work_Time extends Hook
{
	public static function onAuthBeforeLogout( Environment $env, $module, $context, $payload = [] )
	{
		$data	= new Dictionary( $payload );
		if( ( $userId = $data->get( 'userId' ) ) ){
			$logicTimer	= Logic_Work_Timer::getInstance( $env );
			$modelTimer	= new Model_Work_Timer( $env );
			$indices	= ['userId' => $userId, 'status' => 1];
			$active		= $modelTimer->getByIndices( $indices );
			if( $active ){
				$logic	= Logic_Work_Timer::getInstance( $env );
				$logic->pause( $active->workTimerId );
			}
		}
	}

	public static function onDashboardRegisterDashboardPanels( Environment $env, $context, $module, $payload )
	{
		if( !$env->getAcl()->has( 'ajax/work/time', 'renderDashboardPanel' ) )
			return;
		$context->registerPanel( 'work-timer-my', [
			'url'			=> 'ajax/work/time/renderDashboardPanel',
			'title'			=> 'Aktivität: Meine',
			'heading'		=> 'Meine letzte Aktivität',
			'icon'			=> 'fa fa-fw fa-play',
			'rank'			=> 10,
		] );
		$context->registerPanel( 'work-timer-others', [
			'url'			=> 'work/time/ajaxRenderDashboardPanel',
			'title'			=> 'Aktivität: Andere',
			'heading'		=> 'Aktivitäten der Anderen',
			'rank'			=> 20,
			'refresh'		=> 10,
		] );
	}

	public static function onProjectRemove( Environment $env, $context, $module, $payload )
	{
		$projectId	= $payload['projectId'];
		$modelTimer	= new Model_Work_Timer( $env );
		$modelTimer->removeByIndex( 'projectId', $projectId );
	}

	public static function onEnvCallForModules( Environment $env, $context, $module, $payload = [] )
	{
		$context	= new View_Helper_Work_Time_Timer( $env );
		$payload	= [];
		$env->getCaptain()->callHook( 'Work_Timer', 'registerModule', $context, $payload );
	}

	public static function onWorkTimeRegisterTab( Environment $env, $context, $module, $payload )
	{
		$words	= (object) $env->getLanguage()->getWords( 'work/time' );						//  load words
		$context->registerTab( '', $words->tabs['dashboard'], 0 );								//  register main tab
//		$context->registerTab( 'archive', $words->tabs['archive'], 1 );							//  register main tab
//		$context->registerTab( 'report', $words->tabs['report'], 2 );							//  register main tab
	}

/*	static public function onRenderDashboardPanels( Environment $env, $context, $module, $payload )
	{
		$helper	= new View_Helper_Work_Time_Dashboard_My( $env );
		$context->registerPanel( 'work-timer-my', 'Letzte Aktivität', $helper->render(), '1col-fixed', 10 );

		$helper	= new View_Helper_Work_Time_Dashboard_Others( $env );
		$context->registerPanel( 'work-timer-others', 'Aktivitäten Anderer', $helper->render(), '3col-flex', 10 );
	}*/

	public static function onWorkTimeRegisterAnalysisTab( Environment $env, $context, $module, $payload )
	{
		$words	= (object) $env->getLanguage()->getWords( 'work/time' );							//  load words
//		$context->registerTab( '', $words->tabs['dashboard'], 0 );									//  register main tab
		$context->registerTab( 'analysis', $words->tabs['analysis'], 2 );							//  register main tab
//		$context->registerTab( 'report', $words->tabs['report'], 2 );								//  register main tab
	}

	public static function onWorkTimeRegisterArchiveTab( Environment $env, $context, $module, $payload )
	{
		$words	= (object) $env->getLanguage()->getWords( 'work/time' );							//  load words
//		$context->registerTab( '', $words->tabs['dashboard'], 0 );									//  register main tab
		$context->registerTab( 'archive', $words->tabs['archive'], 1 );								//  register main tab
//		$context->registerTab( 'report', $words->tabs['report'], 2 );								//  register main tab
	}
}
