<?php
class Controller_Compressor extends CMF_Hydrogen_Controller{

	static public function ___onPageBuild( CMF_Hydrogen_Environment_Abstract $env, $module, $context, $data = array() ){
		$config		= (object) $env->getConfig()->getAll( 'module.ui_compressor.' );
		$pathCache  = $env->getConfig()->get( 'path.cache' );
		$page		= $env->getPage();

		$page->js->setPrefix( $config->jsPrefix );
		$page->js->setSuffix( $config->jsSuffix );
		$page->js->setCachePath( $config->jsCachePath ? $config->jsCachePath : $pathCache );
		$page->js->setCompression( $config->jsMinify );

		$page->css->primer->setPrefix( $config->cssPrefix );
		$page->css->primer->setSuffix( $config->cssSuffix );
		$page->css->primer->setCachePath( $config->cssCachePath ? $config->cssCachePath : $pathCache );
		$page->css->primer->setCompression( $config->cssMinify );

		$page->css->theme->setPrefix( $config->cssPrefix );
		$page->css->theme->setSuffix( $config->cssSuffix );
		$page->css->theme->setCachePath( $config->cssCachePath ? $config->cssCachePath : $pathCache );
		$page->css->theme->setCompression( $config->cssMinify );

		$page->setPackaging( FALSE, $config->cssMinify );
	}
		
	public function flush(){
		$page	= $this->env->getPage();
		$page->js->clearCache();
		$page->css->primer->clearCache();
		$page->css->theme->clearCache();
		$this->env->getMessenger()->noteNotice( 'Compressed resource files removed from cache' );
		$request	= $this->env->getRequest();
		$redirect	= $request->has( 'from' ) ? $request->get( 'from' ) : NULL;
		$this->restart( $redirect );
	}
}
?>
