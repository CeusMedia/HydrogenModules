<?php

use CeusMedia\Common\Exception\Runtime as RuntimeException;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\HydrogenFramework\Environment\Remote as RemoteEnvironment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_System_Load extends Hook
{
	public function onEnvInit(): void
	{
		$config			= $this->env->getConfig();
		$moduleConfig	= $config->getAll( 'module.server_system_load.', TRUE );	//  shortcut module configuration
		$cores			= (int) $moduleConfig->get( 'cores' );									//  get number of cpu cores from module config
		$max			= (float) $moduleConfig->get( 'max' );									//  get maximum load from module config
		$loads			= sys_getloadavg();															//  get system load values
		$load			= array_shift( $loads ) / $cores;									//  get load of last minute relative to number of cores
		if( $max > 0 && $load > $max ){																//  a maximum load is set and load is higher than that
			if( $this->env instanceof RemoteEnvironment )											//  if application is accessed remotely
				throw RuntimeException::create()
					->setCode( 503 )
					->setMessage( 'Service not available: server load too high' );					//  throw exception instead of HTTP response
			header( 'HTTP/1.1 503 Service Unavailable' );									//  send HTTP 503 code
			header( 'Content-type: text/html; charset=utf-8' );								//  send MIME type header for UTF-8 HTML error page
			if( $moduleConfig->get( 'retryAfter' ) > 0 )										//  seconds to retry after are set
				header( 'Retry-After: '.$moduleConfig->get( 'retryAfter' ) );			//  send retry header
			$message	= '<h1>Service not available</h1><p>Due to heavy load this service is temporarily not available.<br/>Please try again later.</p>';
			$language	= $this->env->getLanguage()->getLanguage();									//  get default language
			$pathLocale	= $this->env->getConfig()->get( 'path.locales' ).$language.'/';		//  get path of locales
			$fileName	= $pathLocale.'html/error/503.html';										//  error page file name
			if( file_exists( $fileName ) )															//  error page file exists
				$message	= FileReader::load( $fileName );										//  load error page content
			print( $message );																		//  display error message
			exit;																					//  and quit application
		}
	}

	public function onRegisterDashboardPanels(): void
	{
		$this->context->registerPanel( 'system-server-load', [
			'url'		=> './ajax/system/load/renderDashboardPanel',
			'icon'		=> 'fa fa-fw fa-bar-chart',
			'title'		=> 'System: Auslastung',
			'heading'	=> 'System: Auslastung',
			'refresh'	=> 10,
		] );
	}
}
