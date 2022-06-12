<?php

use CeusMedia\HydrogenFramework\View;

class View_Manage_Catalog_Bookstore extends View{

	public function index(){}

	protected function renderMainTabs(){
		$currentTab		= (int) $this->env->getSession()->get( 'manage.catalog.bookstore.tab' );
		$tabs			= (object) $this->getWords( 'tabsMain', 'manage/catalog/bookstore' );
		$current		= strtolower( $this->env->getRequest()->get( '__controller' ) );
		$list			= [];
		foreach( $tabs as $key => $value ){
			$attributes	= array( 'href' => './'.$key );
			$link		= UI_HTML_Tag::create( 'a', $value, $attributes );
			$attributes	= array( 'class' => $key === $current ? 'active' : NULL );
			$list[]		= UI_HTML_Tag::create( 'li', $link, $attributes );
		}
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => "nav nav-tabs" ) );
	}
}
?>
