<?php
class View_Manage_Catalog extends CMF_Hydrogen_View{

	public function index(){}

	protected function renderMainTabs(){
		$currentTab		= (int) $this->env->getSession()->get( 'manage.catalog.tab' );
		$tabs			= (object) $this->getWords( 'tabsMain', 'manage/catalog' );
		$current		= strtolower( $this->env->getRequest()->get( 'controller' ) );
		$list			= array();
		foreach( $tabs as $key => $value ){
			$attributes	= array( 'href' => './'.$key );
			$link		= UI_HTML_Tag::create( 'a', $value, $attributes );
			$attributes	= array( 'class'    => $key === $current ? 'active' : NULL );
			$list[]		= UI_HTML_Tag::create( 'li', $link, $attributes );
		}
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => "nav nav-tabs" ) );
	}
}
?>
