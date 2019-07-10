<?php
class View_Manage_Content_Style extends CMF_Hydrogen_View{

	protected function __onInit(){
		$this->page	= $this->env->getPage();
		$pathJs	= $this->env->getConfig()->get( 'path.scripts' );
		$this->page->js->addUrl( $pathJs.'module.manage.content.style.js', 'top' );
		$this->page->css->theme->addUrl( 'module.manage.content.style.css' );
	}

	public function index(){
		$file	= $this->getData( 'file' );
		if( $file ){
			$this->page->js->addScriptOnReady( 'ModuleManageStyle.init("'.$file.'");' );
		}
	}

	public function listFiles( $files, $currentFile ){
		$words		= (object) $this->getWords( 'index.filter' );
		$list	= array();
		if( !$files )
			return '<div class="muted"><em>'.$words->noEntries.'</em></div>';
		foreach( $files as $file ){
			$name	= pathinfo( $file, PATHINFO_FILENAME );
			$ext	= pathinfo( $file, PATHINFO_EXTENSION );
			$label	= $name.'<small class="muted">.'.$ext.'</small>';
			$class	= $file == $currentFile ? 'active' : NULL;
			$link	= UI_HTML_Tag::create( 'a', $label, array(
				'href'	=> './manage/content/style/'.$file,
				'class'	=> 'autocut',
				'title'	=> htmlentities( $file, ENT_QUOTES, 'UTF-8' ),
			) );
			$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
		}
		return UI_HTML_Tag::create( 'ul', $list, array(
			'class'	=> 'nav nav-pills nav-stacked'
		) );
	}
}
?>