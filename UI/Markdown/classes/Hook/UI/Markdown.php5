<?php
class Hook_UI_Markdown extends CMF_Hydrogen_View{

	static public function onRenderContent( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$config	= $env->getConfig()->getAll( 'module.ui_markdown.', TRUE );
		if( !$config->get( 'active' ) )
			return;
		if( !class_exists( '\Michelf\Markdown' ) ){
			$message	= 'Markdown is not installed. Use composer to install "michelf/php-markdown"!';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		if( in_array( strtolower( $data->type ), array( 'markdown', 'md' ) ) )
			$data->content	= Markdown::defaultTransform( $data->content );
	}

	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		$config	= $env->getConfig()->getAll( 'module.ui_markdown.', TRUE );
		if( !$config->get( 'active' ) )
			return;
		if( !class_exists( '\Michelf\Markdown' ) ){
			$message	= 'Markdown is not installed. Use composer to install "michelf/php-markdown"!';
			$env->getMessenger()->noteFailure( $message );
			return;
		}
		$libVersion	= \Michelf\Markdown::MARKDOWNLIB_VERSION;
		if( $config->get( 'version.min' ) ){
			if( version_compare( $libVersion, $config->get( 'version.min' ), '<=' ) ){
				$message	= 'Installed version of Markdown is invalid - must be atleast %s.';
				$env->getMessenger()->noteFailure( sprint_m( $message, $config->get( 'version.min' ) ) );
				return;
			}
		}
		if( $config->get( 'version.max' ) ){
			if( version_compare( $libVersion, $config->get( 'version.max' ), '>' ) ){
				$message	= 'Installed version of Markdown is invalid - must be atmost %s.';
				$env->getMessenger()->noteFailure( sprint_m( $message, $config->get( 'version.max' ) ) );
				return;
			}
		}
	}
}
?>
