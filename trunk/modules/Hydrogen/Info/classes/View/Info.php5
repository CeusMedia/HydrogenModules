<?php
class View_Info extends CMF_Hydrogen_View{

	protected function __onInit() {
		$this->env->page->addThemeStyle( 'site.info.css' );
	}

	public function index(){
		$site		= $this->getData( 'site' );
		$types		= explode( ',', $this->env->getConfig()->get( 'module.info.types' ) );
		foreach( $types as $type ){
			switch( strtolower( trim( $type ) ) ){
				case 'html':
					$fileKey	= 'html/info/'.$site.".html";
					if( $this->hasContentFile( $fileKey ) )
						return $this->loadContentFile( $fileKey );
					break;
				case 'md':
				case 'markdown':
					if( $this->env->getModules()->has( 'UI_Markdown' ) ){
						$fileKey	= 'html/info/'.$site.".md";
						if( $this->hasContentFile( $fileKey ) ){
							$content	= $this->loadContentFile( $fileKey );
							return View_Helper_Markdown::transformStatic( $this->env, $content );
						}
					}
					break;
			}
		}
		$this->env->getResponse()->setStatus( 404 );
		return $this->loadContentFile( 'html/info/404.html' );										//  load content from file
	}
}
?>
