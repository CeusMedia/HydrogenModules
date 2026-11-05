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
		$words		= $this->env->getLanguage()->getSection( 'manage/content', 'tabs' );
		$tabs	    = [
			'manage/content'			=> $words['index'],
			'manage/content/link'		=> $words['link'],
			'manage/content/document'	=> $words['document'],
			'manage/content/image'		=> $words['image'],
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
