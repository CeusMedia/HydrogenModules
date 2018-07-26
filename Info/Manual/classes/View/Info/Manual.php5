<?php
class View_Info_Manual extends CMF_Hydrogen_View{

	public function __onInit(){
		$page	= $this->env->getPage();
		$pathJs	= $this->env->getConfig()->get( 'path.scripts' );

		$page->css->theme->addUrl( 'module.info.manual.css' );
		$page->js->addUrl( $pathJs.'Info.Manual.js' );
	}

	public function add(){
	}

	public function category(){
	}

	public function edit(){
	}

	public function index(){
	}

	public function page(){
		$renderer	= $this->getData( 'renderer' );
		if( $renderer === "server-inline" ){
			$content	= $this->getData( 'content' );
			$content	= View_Helper_Markdown::transformStatic( $this->env, $content );
			$this->addData( 'content', $content );
		}
	}

	public function urlencode( $name ){
		return urlencode( $name );
		return str_replace( "%2F", "/", rawurldecode( $name ) );
	}

}
?>
