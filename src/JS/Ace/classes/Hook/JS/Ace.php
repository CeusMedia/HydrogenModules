<?php

use CeusMedia\HydrogenFramework\Environment\Resource\Page as PageResource;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_Ace extends Hook
{
	/**
	 *	@return		void
	 */
	public function onPageApplyModules(): void
	{
		$moduleConfig	= $this->env->getConfig()->getAll( 'module.js_ace.', TRUE );
		if( !$moduleConfig->get( 'active' ) )
			return;

		$page		= $this->env->getPage();															//  $context is page too, but this is more readable
		$words		= $this->env->getLanguage()->getWords( 'ace' );

		$configLoad	= $moduleConfig->getAll( 'load.', TRUE );
		$cdn		= $configLoad->get( 'cdn' );
		$version	= $configLoad->get( 'version' );

		if( $cdn === 'configJsLib' ){
			$pathJsLib	= $this->env->getConfig()->get( 'path.scripts.lib' );							//  get default CDN from config
			$pathCdn	= $pathJsLib.'Ace/'.$version.'/';
		}
		else{
			$pathCdn	= $configLoad->get( 'cdn.url.'.$cdn );
			$pathCdn	= sprintf( $pathCdn, $version );
			if( !$pathCdn )
				throw new RuntimeException( 'Module does not configure URL of used CDN' );
		}

		$page->addCommonStyle( 'module.js.ace.css' );
		$page->js->addUrl( $pathCdn.'ace.js' );
		$page->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'module.js.ace.js' );

		//  apply Ace automatically
		$configAuto		= $moduleConfig->getAll( 'auto.', TRUE );
		if( !$configAuto->get( 'active' ) )
			return;

		$configOptions	= $configAuto->getAll( 'option.', TRUE );
		$durations		= $configOptions->getAll( 'save.duration.', FALSE );
		$overlayLabels	= $words['overlay-labels'];
		$script			= vsprintf( join( PHP_EOL, [
			'ModuleAceAutoSave.options.words = %3$s;',
			'ModuleAceAutoSave.options.durations = %4$s;',
			'ModuleAce.verbose = %1$s;',
			'ModuleAce.applyAuto( %2$s );',
		] ), [
			json_encode( $configAuto->get( 'verbose' ) ),
			json_encode( $configAuto->get( 'selector' ) ),
			json_encode( $overlayLabels ),
			json_encode( $durations ),
		] );

		$level	= $configAuto->get( 'level' );
		$level	= PageResource::interpretLoadLevel( $level );									//  sanitize level supporting old string values
		$page->js->addScriptOnReady( $script, max( 2, min( 8, $level ) ) );						// append script call on document ready
	}

	/**
	 *	@return		void
	 */
	public function onGetAvailableContentEditor(): void
	{
		if( !empty( $this->payload['type'] ) && $this->payload['type'] !== 'code')
			return;
		if( !empty( $this->payload['format'] ) && !in_array( $this->payload['format'], ['html', 'markdown', 'md'/*, '*'*/], TRUE ) )
			return;
		$editor	= (object) [
			'key'		=> 'ace',
			'label'		=> 'Ace',
			'type'		=> 'code',
			'format'	=> $this->payload['format'],
			'score'		=> 5,
		];
		$criteria	= [
			'default'		=> 1,
			'current'		=> 2,
			'force'			=> 10,
		];
		foreach( $criteria as $key => $value )
			if( !empty( $this->payload[$key] ) && strtolower( $this->payload[$key] ) === $editor->key )
				$editor->score	+= $value;

//		if( !empty( $payload['format'] ) ){}
		$key	= str_pad( $editor->score * 1000, 8, '0', STR_PAD_LEFT ).'_'.$editor->key;
		$this->payload['list'][$key]	= $editor;
	}
}
