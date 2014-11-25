<?php
class View_Helper_CodeMirror{

	static public function ___onPageApplyModules( $env, $context, $module, $data = array() ){
		$page		= $env->getPage();
		$pathJs		= $env->getConfig()->get( 'path.scripts' );
		$pathJsLib	= $env->getConfig()->get( 'path.scripts.lib' );
		$version	= $module->config['version']->value;
		$path		= $pathJsLib.'CodeMirror/'.$version.'/';

		$modes		= explode( ',', $module->config['modes']->value );
		$addons		= explode( ',', $module->config['addons']->value );
		$themes		= explode( ',', $module->config['themes']->value );

		$page->js->addUrl( $path.'lib/codemirror.js' );
		$page->js->addUrl( $pathJs.'codemirror.ext.js' );
		$page->css->theme->addUrl( $path.'lib/codemirror.css' );

		foreach( $modes as $mode )
			$page->js->addUrl( $path.'mode/'.$mode.'/'.$mode.'.js' );
		foreach( $addons as $addon )
			$page->js->addUrl( $path.'addon/'.$addon.'.js' );
		if( in_array( 'dialog/dialog', $addons ) )
			$page->css->theme->addUrl( $path.'addon/dialog/dialog.css' );
		foreach( $themes as $theme )
			$page->css->theme->addUrl( $path.'theme/'.$theme.'.css' );

		$auto	= $module->config;
		if( $module->config['auto']->value ){
			$config		= $env->getConfig()->getAll( 'module.js_codemirror.auto.' );
			$options	= json_encode( (object) array(
				'gutter'			=> $config['option.lineNumbers'] && !$config['option.lineWrapping'],
				'fixedGutter'		=> $config['option.lineNumbers'] && !$config['option.lineWrapping'],
				'lineNumbers'		=> $config['option.lineNumbers'],
				'indentUnit'		=> $config['option.indentUnit'],
				'lineWrapping'		=> $config['option.lineWrapping'],
				'indentWithTabs'	=> $config['option.indentWithTabs'],
				'theme'				=> $config['option.theme']
			) );
			$script	= '
$("'.$config['selector'].'").each(function(){									//  iterate found text areas
	var options = '.$options.';
	CodeMirror.apply($(this), options, true);
});';
			$page->js->addScriptOnReady( $script, 7 );
		}
	}
}
?>
