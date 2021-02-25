<?php
class View_Admin_Module_Viewer extends View_Admin_Module {

	protected function __onInit(){
		$this->env->getLanguage()->load( 'admin/module' );
		
	}

	public function index(){}

	public function view(){
		$moduleId	= $this->getData( 'moduleId' );
		$modules	= $this->getData( 'modules' );
		if( isset( $modules[$moduleId] ) )
			$this->env->getPage()->setTitle( $modules[$moduleId]->title, 'append' );
	}

	public function viewCode(){
		extract( $this->getData() );
		$xmpClass	= '';
		switch( $type ){
			case 'class':
				$xmpClass	= 'php';
				break;
			case 'locale':
				$xmpClass	= 'ini';
				break;
			case 'script':
				$xmpClass	= 'js';
				break;
			case 'style':
				$xmpClass	= 'css';
				break;
			case 'template':
				$xmpClass	= 'php';
				break;
		}
		$code		= UI_HTML_Tag::create( 'pre', htmlentities( $content ), array( 'class' => 'code '.$xmpClass ) );
		$body		= '<h2>'.$moduleId.' - '.$fileName.'</h2>'.$code;
/*		$page		= new UI_HTML_PageFrame();
		$page->addStylesheet( 'css/reset.css' );
		$page->addStylesheet( 'css/typography.css' );
		$page->addStylesheet( 'css/xmp.formats.css' );*/
		$page		= $this->env->getPage();
		$page->setBody( $body );
		print( $page->build( array( 'style' => 'margin: 1em' ) ) );
		exit;
	}
}
?>