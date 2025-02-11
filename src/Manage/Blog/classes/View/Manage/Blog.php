<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Manage_Blog extends View
{
	public function add(): void
	{
	}

	public function edit(): void
	{
	}

	public function index(): void
	{
	}

	public function renderTabs( $current = NULL ): string
	{
		$tabs	= [
			''				=> 'Blog-Einträge',
			'/category'		=> 'Kategorien',
		];
		$list	= [];
		$badge	= '';
/*		if( $this->hasData( 'course' ) && $this->hasData( 'courses' ) ){
			$course	= $this->getData( 'course' );
			foreach( $this->getData( 'courses' ) as $item ){
				if( $course->courseId == $item->courseId && count( $item->newComments ) ){
					$badge	= $this->renderCommentsBadge( count( $item->activeComments ), count( $item->newComments ) );
					$tabs['/comment']	.= '&nbsp;&nbsp;'.$badge;
				}
			}
		}*/
		foreach( $tabs as $key => $value ){
			$attributes	= ['href' => './manage/blog'.$key];
			$link		= HtmlTag::create( 'a', $value, $attributes );
			$attributes	= ['class' => $key == $current ? 'active' : NULL];
			$list[]		= HtmlTag::create( 'li', $link, $attributes );
		}
		return HtmlTag::create( 'ul', $list, ['class' => 'nav nav-tabs'] );
	}
}
