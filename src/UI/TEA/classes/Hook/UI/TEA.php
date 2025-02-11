<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Hook;
use CeusMedia\TemplateAbstraction\Engine as TemplateEngine;
use CeusMedia\TemplateAbstraction\Environment as TemplateAbstractionEnvironment;
use CeusMedia\TemplateAbstraction\Factory as TemplateEngineFactory;
use CeusMedia\TemplateEngine\Template as SimpleTemplateEngineTemplate;

class Hook_UI_TEA extends Hook
{
	protected array $adapters	= [
		'Dwoo'		=> 'CeusMedia\\TemplateAbstraction\\Adapter\\Dwoo',
		'H2O'		=> 'CeusMedia\\TemplateAbstraction\\Adapter\\H2O',
		'Latte'		=> 'CeusMedia\\TemplateAbstraction\\Adapter\\Latte',
		'Mustache'	=> 'CeusMedia\\TemplateAbstraction\\Adapter\\Mustache',
		'PHP'		=> 'CeusMedia\\TemplateAbstraction\\Adapter\\PHP',
		'phpHaml'	=> 'CeusMedia\\TemplateAbstraction\\Adapter\\phpHaml',
		'PHPTAL'	=> 'CeusMedia\\TemplateAbstraction\\Adapter\\PHPTAL',
		'Smarty'	=> 'CeusMedia\\TemplateAbstraction\\Adapter\\Smarty',
		'STE'		=> 'CeusMedia\\TemplateAbstraction\\Adapter\\STE',
		'Twig'		=> 'CeusMedia\\TemplateAbstraction\\Adapter\\Twig',
	];

	public function onEnvInit(): void
	{
		if( !class_exists( '\CeusMedia\TemplateAbstraction\Factory' ) )
			throw new RuntimeException( 'Template Engine Abstraction (ceus-media/template-abstraction) is not available' );

		$config			= $this->env->getConfig();
		$moduleConfig	= $config->getAll( 'module.ui_tea.', TRUE );
		$environment	= new TemplateAbstractionEnvironment();

		/**
		 * @var string $key
		 * @var bool $value
		 */
		foreach( $moduleConfig->getAll( 'engine.', TRUE ) as $key => $value )
			if( 'PHP' !== $key && array_key_exists( $key, $this->adapters ) )
				$environment->registerEngine( new TemplateEngine( $key, $this->adapters[$key], $value ) );
		$this->configureSimpleTemplateEngine( $environment, $moduleConfig );
		$this->configureDefaultTemplateEngine( $environment, $moduleConfig );

		$pathTemplates	= $config->get( 'path.templates', 'templates/' );
		if( !str_starts_with( $pathTemplates, '/' ) )
			$pathTemplates	= $this->env->path.$pathTemplates;

		$tea	= new TemplateEngineFactory( $environment );						//  create a template factory
		$tea->setTemplatePath( $pathTemplates );									//  set template to app root since templates AND content files are possible
		$tea->setCachePath( $moduleConfig->get( 'pathCache' ) );				//  set path to template cache
		$tea->setCompilePath( $moduleConfig->get( 'pathCacheCompiled' ) );		//  set path to compiled template cache

		$this->context->set( 'tea', $tea );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function onViewRealizeTemplate(): void
	{
		/** @var TemplateEngineFactory $tea */
		$tea		= $this->env->get( 'tea' );
		$payload	= $this->getPayload();
		$template	= $tea->getTemplate( $payload['filePath'], $payload['data'] ?? [] );
		$payload['content']	= $template->render();
		$this->setPayload( $payload );
	}

	//  --  PRIVATE  --  //

	/**
	 *	@param		TemplateAbstractionEnvironment	$environment
	 *	@param		Dictionary						$moduleConfig
	 *	@return		void
	 */
	private function configureDefaultTemplateEngine( TemplateAbstractionEnvironment $environment, Dictionary $moduleConfig ): void
	{
		$defaultEngineKey	= $moduleConfig->get( 'defaultsForTemplates' );
		if( !array_key_exists( $defaultEngineKey, $this->adapters ) )
			throw new RuntimeException( 'Template Engine "'.$defaultEngineKey.'" cannot be default, since it is not available' );
		if( !$moduleConfig->get( 'engine.'.$defaultEngineKey, FALSE ) )
			throw new RuntimeException( 'Template Engine "'.$defaultEngineKey.'" cannot be default, since it is not enabled' );
		$environment->setDefaultEngineKey( $moduleConfig->get( 'defaultsForTemplates' ) );
	}

	/**
	 * Simple Template Engine: configure plugins and filters
	 * @param TemplateAbstractionEnvironment $environment
	 * @param Dictionary $moduleConfig
	 * @return void
	 */
	private function configureSimpleTemplateEngine( TemplateAbstractionEnvironment $environment, Dictionary $moduleConfig ): void
	{
		if( !$environment->hasEngine( 'STE' ) )
			return;
		if( TemplateEngine::STATUS_DISABLED === $environment->getEngine( 'STE' )->getStatus() )
			return;

		$messenger		= $this->env->getMessenger();
		$regexPlugin	= "/^plugin\.([a-z0-9]+)$/i";											//  regular expression to detect STE plugin
		$regexFilter	= "/^filter.([a-z0-9]+)$/i";											//  regular expression to detect STE filter
		foreach( $moduleConfig->getAll( 'options.STE.', TRUE ) as $pair ){		//  iterate module configuration pairs
			if( !$pair->value )																	//  if pair value is not positive
				continue;																		//  skip (for performance)
			if( preg_match( $regexPlugin, $pair->key ) ){										//  configuration pair for STE plugin found
				$plugin	= preg_replace( $regexPlugin, '\\1', $pair->key );			//  extract plugin name
				$class	= 'CeusMedia\\TemplateEngine\\Plugin\\'.ucfirst( $plugin );				//  anticipate plugin class name
				if( !class_exists( $class ) ){													//  plugin class is NOT loadable
					$message	= 'TEA: STE Plugin "%s" is missing class %s';
					$messenger->noteFailure( sprintf( $message, $plugin, $class ) );
					continue;
				}
				SimpleTemplateEngineTemplate::addPlugin( new $class );						//  register plugin globally on STE & skip to next pair
			}
			else if( preg_match( $regexFilter, $pair->key ) ){									//  configuration pair for active STE filter found
				$filter	= preg_replace( $regexFilter, '\\1', $pair->key );			//  extract filter name
				$class	= 'CeusMedia\\TemplateEngine\\Filter\\'.ucfirst( $filter );				//  anticipate filter class name
				if( !class_exists( $class ) ){													//  filter class is loadable
					$message	= 'TEA: STE Filter "%s" is missing class %s';
					$messenger->noteFailure( sprintf( $message, $filter, $class ) );
					continue;
				}
				SimpleTemplateEngineTemplate::addFilter( new $class );						//  register filter globally on STE & skip to next pair
			}
		}
	}
}
