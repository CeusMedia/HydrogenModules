<?php
class View_Manage_Blog extends CMF_Hydrogen_View{
	public function add(){}
	public function edit(){}
	public function index(){}

	public function renderTabs( $current = NULL ){
		$tabs	= array(
			''				=> 'Blog-Einträge',
			'/category'		=> 'Kategorien',
		);
		$list	= array();
		$badge	= '';
		if( $this->hasData( 'course' ) && $this->hasData( 'courses' ) ){
			$course	= $this->getData( 'course' );
			foreach( $this->getData( 'courses' ) as $item ){
				if( $course->courseId == $item->courseId && count( $item->newComments ) ){
					$badge	= $this->renderCommentsBadge( count( $item->activeComments ), count( $item->newComments ) );
					$tabs['/comment']	.= '&nbsp;&nbsp;'.$badge;
				}
			}
		}
		foreach( $tabs as $key => $value ){
			$attributes	= array( 'href' => './manage/blog'.$key );
			$link		= UI_HTML_Tag::create( 'a', $value, $attributes );
			$attributes	= array( 'class' => $key == $current ? 'active' : NULL );
			$list[]		= UI_HTML_Tag::create( 'li', $link, $attributes );
		}
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'nav nav-tabs' ) );
	}
}
