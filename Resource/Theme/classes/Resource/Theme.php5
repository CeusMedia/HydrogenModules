<?php
class Resource_Theme{

	static public function ___onPageBuild( CMF_Hydrogen_Environment $env, $module, $context, $data = array() ){
		foreach( $env->getModules()->getAll() as $module ){
			if( !preg_match( '/^(UI_)?Theme_/', $module->id ) )
				continue;
			if( !$module->config['active']->value )
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
