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


	static public function renderInfoBar( $post ){
		$facts	= array(
			'Autor: '	=> $post->author->username,
			'Datum: '	=> date( 'd.m.Y H:i', $post->createdAt ),
			'Gelesen: '	=> $post->nrViews.' mal',
//			'Kommentare: '	=> count( $post->comments ),
		);
		$facts		= self::renderFacts( $facts, 'dl-inline' );
		return UI_HTML_Tag::create( 'div', $facts, array( 'class' => 'blog-post-info' ) );
	}
}
