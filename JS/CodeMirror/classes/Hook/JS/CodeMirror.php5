<?php
class Hook_JS_CodeMirror extends CMF_Hydrogen_Hook{

	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$moduleConfig	= $env->getConfig()->getAll( 'module.js_codemirror.', TRUE );
		if( !$moduleConfig->get( 'active' ) )
			return;

		$page		= $env->getPage();															//  $context is page too, but this is more readable
		$pathJs		= $env->getConfig()->get( 'path.scripts' );
		$cdn		= $moduleConfig->get( 'load.cdn' );

		$scripts	= array();
		$styles		= array();

		if( $cdn === "cdnjs" ){
			$configLoad	= $moduleConfig->getAll( 'load.', TRUE );
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
			$version	= $moduleConfig->get( 'version' );
			$modes		= explode( ',', $moduleConfig->get( 'modes' ) );
			$addons		= explode( ',', $moduleConfig->get( 'addons' ) );
			$themes		= explode( ',', $moduleConfig->get( 'themes' ) );
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
			$page->css->theme->addUrl( $style );

		$page->js->addUrl( $pathJs.'module.js.codemirror.js', 9 );
		$page->addCommonStyle( 'module.js.codemirror.css' );

		//  apply CodeMirror automatically
		$configAuto		= $moduleConfig->getAll( 'auto.', TRUE );
		if( !$configAuto->get( 'active' ) )
			return;
		$configOptions	= $configAuto->getAll( 'option.', !TRUE );
		$options		= array(
			'gutter'			=> $configOptions['lineNumbers'] && !$configOptions['lineWrapping'],
			'fixedGutter'		=> $configOptions['lineNumbers'] && !$configOptions['lineWrapping'],
			'lineNumbers'		=> $configOptions['lineNumbers'],
			'indentUnit'		=> $configOptions['indentUnit'],
			'lineWrapping'		=> $configOptions['lineWrapping'],
			'indentWithTabs'	=> $configOptions['indentWithTabs'],
			'theme'				=> $configOptions['theme']
		);

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
		$level	= CMF_Hydrogen_Environment_Resource_Captain::interpretLoadLevel( $level );		//  sanitize level supporting old string values
		$page->js->addScriptOnReady( $script, max( 2, min( 8, $level ) ) );						//  append script call on document ready
	}
}
?>
