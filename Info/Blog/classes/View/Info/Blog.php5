<?php
class View_Info_Blog extends CMF_Hydrogen_View{
	public function index(){}
	public function post(){}

	static protected function renderFacts( $facts, $listClass = 'dl-horizontal' ){
		$list	= array();
		foreach( $facts as $label => $value ){
			$list[]	= UI_HTML_Tag::create( 'dt', $label ).UI_HTML_Tag::create( 'dd', $value );
		}
		return UI_HTML_Tag::create( 'dl', $list, array( 'class' => $listClass ) );
	}

	static public function renderComment( $comment ){
		$infobar	= self::renderCommentInfoBar( $comment );
		$content	= UI_HTML_Tag::create( 'blockquote', nl2br( trim( $comment->content ) ) );
		$html		= UI_HTML_Tag::create( 'div', $infobar.$content, array(
			'class'		=> 'list-comments-item'
		) );
		return $html;
	}

	static public function renderCommentInfoBar( $comment ){
		$facts	= array(
			'Autor: '	=> $comment->username,
			'Datum: '	=> date( 'd.m.Y H:i', $comment->createdAt ),
		);
		$facts		= self::renderFacts( $facts, 'dl-inline' );
		return UI_HTML_Tag::create( 'div', $facts, array( 'class' => 'infobar blog-comment-info' ) );
	}

	static public function renderPostInfoBar( $post ){
		$facts	= array(
			'Autor: '	=> $post->author->username,
			'Datum: '	=> date( 'd.m.Y H:i', $post->createdAt ),
			'Gelesen: '	=> $post->nrViews.' mal',
//			'Kommentare: '	=> count( $post->comments ),
		);
		$facts		= self::renderFacts( $facts, 'dl-inline' );
		return UI_HTML_Tag::create( 'div', $facts, array( 'class' => 'infobar blog-post-info' ) );
	}
}
