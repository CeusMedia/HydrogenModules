<?php
class View_Manage_Content extends CMF_Hydrogen_View{
	public function index(){}

	protected function renderTabs(){
		$current	= $this->env->getRequest()->get( '__controller' );
		$tabs	    = array(
			'manage/content'			=> 'Übersicht',
			'manage/content/link'		=> 'Links',
			'manage/content/document'	=> 'Dokumente',
			'manage/content/image'		=> 'Bilder',
		);
		$list   = [];
		foreach( $tabs as $key => $value ){
			$class	= $key == $current ? 'active' : NULL;
			$link	= UI_HTML_Tag::create( 'a', $value, array( 'href' => $key ) );
			$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
		}
		$tabs	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'nav nav-tabs' ) );
		return $tabs;
	}
}
?>
