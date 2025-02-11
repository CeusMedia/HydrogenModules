<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Content extends Controller
{
	public function index()
	{
	}

	public function renderTabs(): string
	{
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
		return HtmlTag::create( 'ul', $list, ['class' => 'nav nav-tabs'] );
	}
}
