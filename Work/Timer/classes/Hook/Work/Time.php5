<?php
class Hook_Work_Time extends CMF_Hydrogen_Hook
{
	public static function onAuthBeforeLogout( CMF_Hydrogen_Environment $env, $module, $context, $payload = [] )
	{
		$data	= new ADT_List_Dictionary( $payload );
		if( ( $userId = $data->get( 'userId' ) ) ){
			$logicTimer	= Logic_Work_Timer::getInstance( $env );
			$modelTimer	= new Model_Work_Timer( $env );
			$indices	= array( 'userId' => $userId, 'status' => 1 );
			$active		= $modelTimer->getByIndices( $indices );
			if( $active ){
				$logic	= Logic_Work_Timer::getInstance( $env );
				$logic->pause( $active->workTimerId );
			}
		}
	}

	public static function onDashboardRegisterDashboardPanels( CMF_Hydrogen_Environment $env, $context, $module, $payload )
	{
		if( !$env->getAcl()->has( 'ajax/work/time', 'renderDashboardPanel' ) )
			return;
		$context->registerPanel( 'work-timer-my', array(
			'url'			=> 'ajax/work/time/renderDashboardPanel',
			'title'			=> 'Aktivität: Meine',
			'heading'		=> 'Meine letzte Aktivität',
			'icon'			=> 'fa fa-fw fa-play',
			'rank'			=> 10,
		) );
		$context->registerPanel( 'work-timer-others', array(
			'url'			=> 'work/time/ajaxRenderDashboardPanel',
			'title'			=> 'Aktivität: Andere',
			'heading'		=> 'Aktivitäten der Anderen',
			'rank'			=> 20,
			'refresh'		=> 10,
		) );
	}

	public static function onProjectRemove( CMF_Hydrogen_Environment $env, $context, $module, $payload )
	{
		$projectId	= $payload['projectId'];
		$this->modelTimer->removeByIndex( 'projectId', $projectId );
	}

	public static function onEnvCallForModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = [] )
	{
		$context	= new View_Helper_Work_Time_Timer( $env );
		$env->getCaptain()->callHook( 'Work_Timer', 'registerModule', $context, array() );
	}

	public static function onWorkTimeRegisterTab( CMF_Hydrogen_Environment $env, $context, $module, $payload )
	{
		$words	= (object) $env->getLanguage()->getWords( 'work/time' );						//  load words
		$context->registerTab( '', $words->tabs['dashboard'], 0 );								//  register main tab
//		$context->registerTab( 'archive', $words->tabs['archive'], 1 );							//  register main tab
//		$context->registerTab( 'report', $words->tabs['report'], 2 );							//  register main tab
	}

/*	static public function onRenderDashboardPanels( CMF_Hydrogen_Environment $env, $context, $module, $payload )
	{
		$helper	= new View_Helper_Work_Time_Dashboard_My( $env );
		$context->registerPanel( 'work-timer-my', 'Letzte Aktivität', $helper->render(), '1col-fixed', 10 );

		$helper	= new View_Helper_Work_Time_Dashboard_Others( $env );
		$context->registerPanel( 'work-timer-others', 'Aktivitäten Anderer', $helper->render(), '3col-flex', 10 );
	}*/

	public static function onWorkTimeRegisterAnalysisTab( CMF_Hydrogen_Environment $env, $context, $module, $payload )
	{
		$words	= (object) $env->getLanguage()->getWords( 'work/time' );							//  load words
//		$context->registerTab( '', $words->tabs['dashboard'], 0 );									//  register main tab
		$context->registerTab( 'analysis', $words->tabs['analysis'], 2 );							//  register main tab
//		$context->registerTab( 'report', $words->tabs['report'], 2 );								//  register main tab
	}

	public static function onWorkTimeRegisterArchiveTab( CMF_Hydrogen_Environment $env, $context, $module, $payload )
	{
		$words	= (object) $env->getLanguage()->getWords( 'work/time' );							//  load words
//		$context->registerTab( '', $words->tabs['dashboard'], 0 );									//  register main tab
		$context->registerTab( 'archive', $words->tabs['archive'], 1 );								//  register main tab
//		$context->registerTab( 'report', $words->tabs['report'], 2 );								//  register main tab
	}
}
