<?php
class Resource_Theme{

	static public function ___onPageBuild( $env, $module, $context, $data = array() ){
		foreach( $env->getModules()->getAll() as $module ){
			if( !preg_match( '/^Theme_/', $module->id ) )
				continue;
			if( !$module->config['enabled']->value )
				continue;
			$page	= $env->getPage();
			foreach( $module->files->styles as $styleFile ){
				$page->addCommonStyle( $styleFile->file );
			}
			if( isset( $module->config['style'] ) ){
				$style	= $module->config['style']->value;
				$page->addBodyClass( 'style-'.$style );
			}
		}
	}
}
?>