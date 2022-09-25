<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Manage_Catalog_Bookstore extends View
{
	public function index()
	{
	}

	protected function renderMainTabs()
	{
		$currentTab		= (int) $this->env->getSession()->get( 'manage.catalog.bookstore.tab' );
		$tabs			= (object) $this->getWords( 'tabsMain', 'manage/catalog/bookstore' );
		$current		= strtolower( $this->env->getRequest()->get( '__controller' ) );
		$list			= [];
		foreach( $tabs as $key => $value ){
			$attributes	= array( 'href' => './'.$key );
			$link		= HtmlTag::create( 'a', $value, $attributes );
			$attributes	= array( 'class' => $key === $current ? 'active' : NULL );
			$list[]		= HtmlTag::create( 'li', $link, $attributes );
		}
		return HtmlTag::create( 'ul', $list, array( 'class' => "nav nav-tabs" ) );
	}
}
