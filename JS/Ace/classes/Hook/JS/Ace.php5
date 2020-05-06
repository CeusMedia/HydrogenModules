<?php
class Hook_JS_Ace extends CMF_Hydrogen_Hook{

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		$moduleConfig	= $env->getConfig()->getAll( 'module.js_ace.', TRUE );
		if( !$moduleConfig->get( 'active' ) )
			return;

		$page		= $env->getPage();															//  $context is page too, but this is more readable
		$words		= $env->getLanguage()->getWords( 'ace' );

		$configLoad	= $moduleConfig->getAll( 'load.', TRUE );
		$pathCdn	= $configLoad->get( 'cdn.url.'.$configLoad->get( 'cdn' ) );
		$pathCdn	= sprintf( $pathCdn, $configLoad->get( 'version' ) );
		if( !$pathCdn )
			throw new RuntimeException( 'Module does not configure URL of used CDN' );

		$page->addCommonStyle( 'module.js.ace.css' );
		$page->js->addUrl( $pathCdn.'ace.js' );
		$page->js->addUrl( $env->getConfig()->get( 'path.scripts' ).'module.js.ace.js' );

		//  apply Ace automatically
		$configAuto		= $moduleConfig->getAll( 'auto.', TRUE );
		if( !$configAuto->get( 'active' ) )
			return;

		$configOptions	= $configAuto->getAll( 'option.', TRUE );
		$durations		= $configOptions->getAll( 'save.duration.', FALSE );
		$overlayLabels	= $words['overlay-labels'];
		$script			= vsprintf( join( PHP_EOL, array(
			'ModuleAceAutoSave.options.words = %3$s;',
			'ModuleAceAutoSave.options.durations = %4$s;',
			'ModuleAce.verbose = %1$s;',
			'ModuleAce.applyAuto( %2$s );',
		) ), array(
			json_encode( $configAuto->get( 'verbose' ) ),
			json_encode( $configAuto->get( 'selector' ) ),
			json_encode( $overlayLabels ),
			json_encode( $durations ),
		) );

		$level	= $configAuto->get( 'level' );
		$level	= CMF_Hydrogen_Environment_Resource_Captain::interpretLoadLevel( $level );		//  sanitize level supporting old string values
		$page->js->addScriptOnReady( $script, max( 2, min( 8, $level ) ) );						// append script call on document ready
	}

	/**
	 *	@static
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Caller object
	 *	@param		object						$module		Module config data object
	 *	@param		array						$payload	Map of payload data
	 *	@return		void
	 */
	static public function onGetAvailableContentEditor( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() ){
		if( !empty( $payload->type ) && !in_array( $payload->type, array( 'code' ) ) )
			return;
		if( !empty( $payload->format ) && !in_array( $payload->format, array( 'html', 'markdown', 'md'/*, '*'*/ ) ) )
			return;
		$editor	= (object) array(
			'key'		=> 'ace',
			'label'		=> 'Ace',
			'type'		=> 'code',
			'format'	=> $payload->format,
			'score'		=> 5,
		);
		$criteria	= array(
			'default'		=> 1,
			'current'		=> 2,
			'force'			=> 10,
		);
		foreach( $criteria as $key => $value )
			if( !empty( $payload->$key ) && strtolower( $payload->$key ) === $editor->key )
				$editor->score	+= $value;

//		if( !empty( $payload->format ) ){}
		$key	= str_pad( $editor->score * 1000, 8, '0', STR_PAD_LEFT ).'_'.$editor->key;
		$payload->list[$key]	= $editor;
	}
}
