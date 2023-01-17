<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Resource\Page as PageResource;
use CeusMedia\HydrogenFramework\Hook;

class Hook_JS_CodeMirror extends Hook
{
	public static function onPageApplyModules( Environment $env, object $context, object $module, array & $payload )
	{
		$moduleConfig	= $env->getConfig()->getAll( 'module.js_codemirror.', TRUE );
		if( !$moduleConfig->get( 'active' ) )
			return;

		$page		= $env->getPage();															//  $context is page too, but this is more readable
		$pathJs		= $env->getConfig()->get( 'path.scripts' );
		$configLoad	= $moduleConfig->getAll( 'load.', TRUE );
		$cdn		= $configLoad->get( 'cdn' );

		$scripts	= [];
		$styles		= [];

		if( $cdn === "cdnjs" ){
			$collector	= $page->css->theme;
			$version	= $configLoad->get( 'version' );
			$modes		= explode( ',', $configLoad->get( 'modes' ) );
			$addons		= explode( ',', $configLoad->get( 'addons' ) );
			$themes		= explode( ',', $configLoad->get( 'themes' ) );
			$pathCdn	= sprintf( $configLoad->get( 'cdn.url.cdnjs' ), $version );
			$suffixJs	= $configLoad->get( 'minified' ) ? '.min.js' : '.js';
			$suffixCss	= $configLoad->get( 'minified' ) ? '.min.css' : '.css';

			$scripts[]	= $pathCdn.'codemirror'.$suffixJs;
			$styles[]	= $pathCdn.'codemirror'.$suffixCss;
			if( $moduleConfig->get( 'load.map' ) ){
				$scripts[]	= $pathCdn.'codemirror'.$suffixJs.'.map';
				$styles[]	= $pathCdn.'codemirror'.$suffixCss.'.map';
			}
			foreach( $modes as $mode ){
				$scripts[]	= $pathCdn.'mode/'.$mode.'/'.$mode.$suffixJs;
				if( $configLoad->get( 'map' ) )
					$scripts[]	= $pathCdn.'mode/'.$mode.'/'.$mode.$suffixJs.'.map';
			}
			foreach( $addons as $addon ){
				$scripts[]	= $pathCdn.'addon/'.$addon.$suffixJs;
				if( $configLoad->get( 'map' ) )
					$scripts[]	= $pathCdn.'addon/'.$addon.$suffixJs.'.map';
			}
			foreach( $themes as $theme ){
				$styles[]	= $pathCdn.'theme/'.$theme.$suffixCss;
				if( $configLoad->get( 'map' ) )
					$styles[]	= $pathCdn.'theme/'.$theme.$suffixCss.'.map';
			}
			if( in_array( 'dialog/dialog', $addons ) )
				$styles[]	= $pathCdn.'addon/dialog/dialog'.$suffixCss;
		}
		else{																					//  use default CDN @deprecated @todo remove
			$collector	= $page->css->lib;
			$version	= $configLoad->get( 'version' );
			$modes		= explode( ',', $configLoad->get( 'modes' ) );
			$addons		= explode( ',', $configLoad->get( 'addons' ) );
			$themes		= explode( ',', $configLoad->get( 'themes' ) );
			$pathJsLib	= $env->getConfig()->get( 'path.scripts.lib' );							//  get default CDN from config
 			if( !strlen( trim( $pathJsLib ) ) )
				throw new RuntimeException( 'No default CDN configured' );
			$pathCdn		= $pathJsLib.'CodeMirror/'.$version.'/';

			$scripts[]		= $pathCdn.'lib/codemirror.js';
			$styles[]		= $pathCdn.'lib/codemirror.css';
			foreach( $modes as $mode )
				$scripts[]	= $pathCdn.'mode/'.$mode.'/'.$mode.'.js';
			foreach( $addons as $addon )
				$scripts[]	= $pathCdn.'addon/'.$addon.'.js';
			if( in_array( 'dialog/dialog', $addons ) )
				$styles[]	= $pathCdn.'addon/dialog/dialog.css';
			foreach( $themes as $theme )
				$styles[]	= $pathCdn.'theme/'.$theme.'.css';
		}
		foreach( $scripts as $script )
			$page->js->addUrl( $script );
		foreach( $styles as $style )
			$collector->addUrl( $style );

		$page->js->addUrl( $pathJs.'module.js.codemirror.js', 9 );
		$page->addCommonStyle( 'module.js.codemirror.css' );

		//  apply CodeMirror automatically
		$configAuto		= $moduleConfig->getAll( 'auto.', TRUE );
		if( !$configAuto->get( 'active' ) )
			return;
		$configOptions	= $configAuto->getAll( 'option.', !TRUE );
		$options		= [
			'gutter'			=> $configOptions['lineNumbers'] && !$configOptions['lineWrapping'],
			'fixedGutter'		=> $configOptions['lineNumbers'] && !$configOptions['lineWrapping'],
			'lineNumbers'		=> $configOptions['lineNumbers'],
			'indentUnit'		=> $configOptions['indentUnit'],
			'lineWrapping'		=> $configOptions['lineWrapping'],
			'indentWithTabs'	=> $configOptions['indentWithTabs'],
			'theme'				=> $configOptions['theme']
		];

		$verbose	= json_encode( $configAuto->get( 'verbose' ) );
		$selector	= json_encode( $configAuto->get( 'selector' ) );
		$options	= json_encode( $options );
		$script		= '
ModuleCodeMirror.verbose = '.$verbose.';
ModuleCodeMirror.apply( '.$selector.', '.$options.', true );
CodeMirror.on(window, "resize", function() {
	var showing = document.body.getElementsByClassName("CodeMirror-fullscreen")[0];
	if (showing){																		//  a code mirror is fullscreen right now
		var height = ModuleCodeMirror.getWinHeight() + "px";							//  get viewport height
		showing.CodeMirror.getWrapperElement().style.height = height;					//  apply viewport height to code mirror
	}
});';

		$level	= $configAuto->get( 'level' );
		$level	= PageResource::interpretLoadLevel( $level );		//  sanitize level supporting old string values
		$page->js->addScriptOnReady( $script, max( 2, min( 8, $level ) ) );						//  append script call on document ready
	}

	/**
	 *	@static
	 *	@param		Environment		$env		Environment object
	 *	@param		object			$context	Caller object
	 *	@param		object			$module		Module config data object
	 *	@param		array			$payload	Map of payload data
	 *	@return		void
	 */
	public static function onGetAvailableContentEditor( Environment $env, object $context, object $module, array & $payload )
	{
		if( !empty( $payload['type'] ) && !in_array( $payload['type'], ['code'] ) )
			return;
		if( !empty( $payload['format'] ) && !in_array( $payload['format'], ['html', 'markdown', 'md'/*, '*'*/] ) )
			return;
		$editor	= (object) [
			'key'		=> 'codemirror',
			'label'		=> 'Code Mirror',
			'type'		=> 'code',
			'format'	=> $payload['format'],
			'score'		=> 5,
		];
		$criteria	= [
			'default'		=> 1,
			'current'		=> 2,
			'force'			=> 10,
		];
		foreach( $criteria as $key => $value )
			if( !empty( $payload[$key] ) && strtolower( $payload[$key] ) === $editor->key )
				$editor->score	+= $value;

//		if( !empty( $payload['format'] ) ){}
		$key	= str_pad( $editor->score * 1000, 8, '0', STR_PAD_LEFT ).'_'.$editor->key;
		$payload['list'][$key]	= $editor;
	}
}
