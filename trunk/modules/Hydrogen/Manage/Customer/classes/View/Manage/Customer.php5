<?php
class View_Manage_Customer extends CMF_Hydrogen_View{
	
	protected $tabs	= array();

	public function add(){}
	public function edit(){}
	public function index(){}
	public function map(){}
	public function rate(){}

	public function registerTab( $url, $label, $disabled = NULL ){
		$this->tabs[]	= (object) array(
			'url'		=> $url,
			'label'		=> $label,
			'disabled'	=> $disabled,
		);
	}
	
	public function renderTabs( $current = 0 ){
		$list	= array();
		if( count( $this->tabs ) < 2 )
			return '';
		$list	= array();
		foreach( $this->tabs as $nr => $tab ){
			$attributes	= array( 'href'	=> './manage/customer/'.$tab->url );
			$link		= UI_HTML_Tag::create( 'a', $tab->label, $attributes );
			$isActive	= $nr === $current || ( $tab->url === $current ) || !$nr && !$current; 
			$attributes	= array( 'class' => $isActive ? 'active' : NULL );
			if( $tab->disabled ){
				$link	= UI_HTML_Tag::create( 'a', $value );
				$attributes['class']	.= ' disabled';
			}
			$list[]	= UI_HTML_Tag::create( 'li', $link, $attributes );
		}
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => "nav nav-tabs" ) );
	}
}
?>
