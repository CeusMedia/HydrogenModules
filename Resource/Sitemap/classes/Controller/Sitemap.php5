<?php
class Controller_Sitemap extends CMF_Hydrogen_Controller{

	public function index(){
		$logic	= Logic_Sitemap::getInstance( $this->env );
		$this->env->getModules()->callHook( 'Sitemap', 'registerLinks', $logic );
		$this->addData( 'links', $logic->getLinks() );
	}

	public function submit(){
		$logic	= Logic_Sitemap::getInstance( $this->env );
		$logic->submitToProviders();
		exit;
	}
}
