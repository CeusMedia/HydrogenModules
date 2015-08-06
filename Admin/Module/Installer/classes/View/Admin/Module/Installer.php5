<?php
class View_Admin_Module_Installer extends View_Admin_Module {

	protected function __onInit(){
		$this->env->getLanguage()->load( 'admin/module' );
		$this->env->getPage()->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'module.admin.module.installer.js' );
	}

	public function diff(){
		$fileLocal	= $this->getData( 'fileLocal' );
		$fileSource	= $this->getData( 'fileSource' );

		$file1	= File_Reader::loadArray( $fileLocal );
		$file2	= File_Reader::loadArray( $fileSource );

		$options = array(
			'ignoreWhitespace'	=> true,
	//		'ignoreCase'		=> true,
		);
		$diff		= new Diff( $file1, $file2, $options );											//  initialize the diff class
		$renderer	= new Diff_Renderer_Html_Inline;
		$body		= '
			<div class="container">
				<h2><span class="muted">Installer</span> Diff</h2>
				<b>Old:</b> <code>'.$fileLocal.'</code><br/>
				<b>New:</b> <code>'.$fileSource.'</code>
				'.$diff->render( $renderer ).'
				<hr/>
			</div>';

		$page	= new UI_HTML_PageFrame();
		$page->setBaseHref( $this->env->url );
		$page->addBody( $body );
		$page->addStylesheet( "//cdn.int1a.net/css/bootstrap.min.css" );
		$page->addStylesheet( "themes/custom/css/php-diff.css" );
		print( $page->build() );
		die;
	}

	public function index(){
	}

	public function uninstall(){
		$words		= $this->env->getLanguage()->getWords( 'admin/module' );
		$moduleId	= $this->getData( 'moduleId' );
		$module		= $this->getData( 'module' );
		if( isset( $modules[$moduleId] ) ){
			print_m( $module );
			die;
		}
	}

	public function update(){
		$words		= $this->env->getLanguage()->getWords( 'admin/module' );

		$moduleId	= $this->getData( 'moduleId' );
		$modules	= $this->getData( 'modulesAvailable' );
		if( isset( $modules[$moduleId] ) )
			$this->env->getPage()->setTitle( $modules[$moduleId]->title, 'append' );

		$this->addData( 'wordsTypes', $words['types'] );
		$this->env->getPage()->js->addScriptOnReady( 'AdminModuleUpdater.init();' );
	}

	public function view(){
		$words		= $this->env->getLanguage()->getWords( 'admin/module' );

		$moduleId	= $this->getData( 'moduleId' );
		$modules	= $this->getData( 'modules' );
		if( isset( $modules[$moduleId] ) )
			$this->env->getPage()->setTitle( $modules[$moduleId]->title, 'append' );

		$this->addData( 'wordsTypes', $words['types'] );
	}
}
?>
