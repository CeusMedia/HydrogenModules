<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\View;
use CeusMedia\HydrogenFramework\Hook;
use CeusMedia\TemplateAbstraction\Engine;
use CeusMedia\TemplateAbstraction\Environment as TemplateAbstractionEnvironment;
use CeusMedia\TemplateAbstraction\Factory;

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
		/** @var \CeusMedia\HydrogenFramework\Environment $context */
		$context		= $this->context;
		$config			= $this->env->getConfig();
		$moduleConfig	= $config->getAll( 'module.ui_tea.', TRUE );

		if( !class_exists( '\CeusMedia\TemplateAbstraction\Factory' ) )								//  check cmModules integration
			throw new RuntimeException( 'Template Engine Abstraction (TEA) is not available' );

		$environment	= new TemplateAbstractionEnvironment();

		/**
		 * @var string $key
		 * @var bool $value
		 */
		foreach( $moduleConfig->getAll( 'engine.', TRUE ) as $key => $value )
			if( 'PHP' !== $key && array_key_exists( $key, $this->adapters ) )
				$environment->registerEngine( new Engine( $key, $this->adapters[$key], $value ) );
		$this->configureSimpleTemplateEngine( $environment );
		$this->configureDefaultTemplateEngine( $environment, $moduleConfig );

		$pathTemplates	= $config->get( 'path.templates', 'templates/' );
		if( !str_starts_with( $pathTemplates, '/' ) )
			$pathTemplates	= $this->env->path.$pathTemplates;

		$tea	= new Factory( $environment );										//  create a template factory
		$tea->setTemplatePath( $pathTemplates );									//  set template to app root since templates AND content files are possible
		$tea->setCachePath( $moduleConfig->get( 'pathCache' ) );				//  set path to template cache
		$tea->setCompilePath( $moduleConfig->get( 'pathCacheCompiled' ) );		//  set path to compiled template cache

		$context->set( 'tea', $tea );
	}

	public function onViewRealizeTemplate(): void
	{
		/** @var View $context */
		$context	= $this->context;
		$payload	= $this->getPayload();

		/** @var Factory $tea */
		$tea		= $this->env->tea;
		$template	= $tea->getTemplate( $payload['filePath'], $payload['data'] );
		$payload['content']	= $template->render();
		$this->setPayload( $payload );
	}

	private function configureSimpleTemplateEngine( TemplateAbstractionEnvironment $environment ): void
	{
		$messenger		= $this->env->getMessenger();
		$engineConfig	= $this->env->getConfig()->getAll( 'module.ui_tea.engine.ste.', TRUE );

		//  --  STE: CONFIGURE PLUGINS & FILTERS  --  //
//		$messenger->noteNotice( 'TEA: STE init' );
		if( !$environment->hasEngine( 'STE' ) )
			return;

		$engine	= $environment->getEngine( 'STE' );
		if( Engine::STATUS_DISABLED === $engine->getStatus() )
			return;

		$regexPlugin	= "/^plugin\.([a-z0-9]+)$/i";			//  regular expression to detect STE plugin
		$regexFilter	= "/^filter.([a-z0-9]+)$/i";			//  regular expression to detect STE filter
		foreach( $engineConfig as $pair ){											//  iterate module configuration pairs
			if( !$pair->value )														//  if pair value is not positive
				continue;															//  skip (for performance)
			if( preg_match( $regexPlugin, $pair->key ) ){							//  configuration pair for STE plugin found
				$plugin	= preg_replace( $regexPlugin, '\\1', $pair->key );			//  extract plugin name
				$class	= 'CeusMedia\\TemplateEngine\\Plugin\\'.ucfirst( $plugin );				//  anticipate plugin class name
				if( !class_exists( $class ) ){													//  plugin class is NOT loadable
					$messenger->noteFailure( 'TEA: STE Plugin <cite>'.$plugin.'</cite> is missing class <code>'.$class.'<code>' );
					continue;
				}
				CeusMedia\TemplateEngine\Template::addPlugin( new $class );						//  register plugin globally on STE & skip to next pair
			}
			else if( preg_match( $regexFilter, $pair->key ) ){									//  configuration pair for active STE filter found
				$filter	= preg_replace( $regexFilter, '\\1', $pair->key );			//  extract filter name
				$class	= 'CeusMedia\\TemplateEngine\\Filter\\'.ucfirst( $filter );				//  anticipate filter class name
				if( !class_exists( $class ) ){								//  filter class is loadable
					$messenger->noteFailure( 'TEA: STE Filter <cite>'.$filter.'</cite> is missing class <code>'.$class.'<code>' );
					continue;
				}
				CeusMedia\TemplateEngine\Template::addFilter( new $class );					//  register filter globally on STE & skip to next pair
			}
		}
	}

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
}
