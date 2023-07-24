<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Manage_Content extends View{
	public function index(){}

	protected function renderTabs(){
		$current	= $this->env->getRequest()->get( '__controller' );
		$tabs	    = [
			'manage/content'			=> 'Übersicht',
			'manage/content/link'		=> 'Links',
			'manage/content/document'	=> 'Dokumente',
			'manage/content/image'		=> 'Bilder',
		];
		$list   = [];
		foreach( $tabs as $key => $value ){
			$class	= $key == $current ? 'active' : NULL;
			$link	= HtmlTag::create( 'a', $value, ['href' => $key] );
			$list[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
		}
		$tabs	= HtmlTag::create( 'ul', $list, ['class' => 'nav nav-tabs'] );
		return $tabs;
	}
}
