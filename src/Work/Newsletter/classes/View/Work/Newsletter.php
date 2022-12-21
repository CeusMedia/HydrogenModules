<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Work_Newsletter extends View
{
	public function add()
	{
		$words			= (object) $this->getWords( NULL, 'work/newsletter' );
		$words->add		= (object) $words->add;
		$this->addData( 'words', $words );
	}

	public function edit()
	{
		$words			= (object) $this->getWords( NULL, 'work/newsletter' );
		$words->edit	= (object) $words->edit;
		$this->addData( 'words', $words );
	}

	public function editFull()
	{
		$words			= (object) $this->getWords( NULL, 'work/newsletter' );
		$words->edit	= (object) $words->edit;
		$this->addData( 'words', $words );
	}

	public function index()
	{
		$words			= (object) $this->getWords( NULL, 'work/newsletter' );
		$words->index	= (object) $words->index;
		$this->addData( 'words', $words );
	}

	protected function renderMainTabs()
	{
		$currentTab		= (int) $this->env->getSession()->get( 'work.newsletter.tab' );
		$tabs			= (object) $this->getWords( 'tabsMain', 'work/newsletter' );
		$current	= strtolower( $this->env->getRequest()->get( 'controller' ) );
		$list		= [];
		foreach( $tabs as $key => $value ){
			$attributes	= ['href'	=> './'.$key];
			$link		= HtmlTag::create( 'a', $value, $attributes );
			$attributes	= ['class'	=> $key === $current ? 'active' : NULL];
			$list[]	= HtmlTag::create( 'li', $link, $attributes );
		}
		return HtmlTag::create( 'ul', $list, ['class' => "nav nav-tabs"] );
	}

	protected function renderTabs( $tabs, $baseUrl, $current, $disabled = [] )
	{
		$list	= [];
		$number	= 0;
		foreach( $tabs as $key => $value ){
			$number++;
			$attributes	= ['href'	=> './work/newsletter/'.$baseUrl.$key];
			$link		= HtmlTag::create( 'a', $value, $attributes );
			$attributes	= array(
				'class'	=> (int) $key === $current ? 'active' : NULL
			);
			if( in_array( $number, $disabled ) ){
				$link	= HtmlTag::create( 'a', $value );
				$attributes['class']	.= ' disabled';
			}
			$list[]	= HtmlTag::create( 'li', $link, $attributes );
		}
		return HtmlTag::create( 'ul', $list, ['class' => "nav nav-tabs"] );
	}
}
