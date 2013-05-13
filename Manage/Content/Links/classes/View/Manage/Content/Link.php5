<?php
class View_Manage_Content_Link extends View_Manage_Content{
	public function add(){}
	public function edit(){}
	public function index(){}

	protected function renderList(){
		$list	= '<div><small class="muted"><em>Keine.</em></small></div>';
		if( ( $links = $this->getData( 'links' ) ) ){
			$list	= array();
			foreach( $links as $entry ){
				$link	= UI_HTML_Tag::create( 'a', $entry->title, array( 'href' => './manage/content/link/edit/'.$entry->linkId ) );
				$list[]	= UI_HTML_Tag::create( 'li', $link );
			}
			$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'nav nav-pills nav-stacked' ) );
		}
		return $list;
	}
}
?>
