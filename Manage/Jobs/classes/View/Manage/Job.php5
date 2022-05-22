<?php

use CeusMedia\HydrogenFramework\Environment;

class View_Manage_Job extends CMF_Hydrogen_View
{
	public function index()
	{
	}

	public static function removeEnvPath( Environment $env, string $string ): string
	{
		return preg_replace( '@'.preg_quote( $env->uri, '@' ).'@', '', $string );
	}

	public static function renderTabs( Environment $env, $current = 0 )
	{
		$tabs	= new View_Helper_Navigation_Bootstrap_Tabs( $env );
		$tabs->setBasePath( './manage/job/' );
/*		$words	= (object) $env->getLanguage()->getWords( 'manage/job' );							//  load words
		$tabs->registerTab( '', $words->tabs['index'], 0 );											//  register job dashboard as main tab
		$tabs->registerTab( 'run', $words->tabs['run'], 1 );										//  register job runs tab
		$tabs->registerTab( 'schedule', $words->tabs['schedule'], 2 );								//  register job schedule tab
		$tabs->registerTab( 'definition', $words->tabs['definition'], 3 );							//  register job definitions tab
*/
		$tabs->registerTab( '', 'Dashboard', 0 );													//  register job dashboard as main tab
		$tabs->registerTab( 'run', 'Ausführungen', 1 );												//  register job runs tab
		$tabs->registerTab( 'schedule', 'Zeitplan', 2 );											//  register job schedule tab
		$tabs->registerTab( 'definition', 'Jobs', 3 );												//  register job definitions tab

		return $tabs->renderTabs( $current );
	}
}
