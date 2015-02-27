<?php
class View_Info_Page extends CMF_Hydrogen_View{

	public function index(){

		$config		= $this->env->getConfig()->get( 'module.info_pages.', TRUE );
		$request	= $this->env->getRequest();
		$page		= $this->env->getPage();

		$data		= new ADT_List_Dictionary( $this->getData() );								//  wrap view data into dictionary object
		if( ( $object = $data->get( 'page' ) ) ){												//  a page has been found for called path
			$separator	= $this->env->getConfig()->get( 'module.info_pages.title.separator' );	//  get title part separator
			foreach( $object->parents as $parent )												//  iterate superior pages
				$page->setTitle( $parent->title, -1, ' '.$separator.' ' );						//  append parent page title
			$page->setTitle( $object->title, -1, ' '.$separator.' ' );							//  append current page title
			if( !strlen( $content = trim( $object->content ) ) ){								//  page has HTML content
				$words	= $this->getWords( 'index', 'info/pages' );
				$this->env->getMessenger()->noteNotice( $words->msgEmptyContent );
			}
			$pattern	= "/^(.*)(\[page:(.+)\])(.*)$/sU";
			while( preg_match( $pattern, $object->content ) ){
				$path	= preg_replace( $pattern, "\\3", $object->content );
			//	if( $path == $request->get( 'page' ) )
				if( $path == $object->pageId )
					throw new Exception( 'Page "'.$path.'" must not include itself' );
				$subcontent		= $this->loadSubpage( $path );									//  load nested page content
				$subcontent		= preg_replace( "/<h(1|2)>.*<\/h(1|2)>/", "", $subcontent );		//  remove headings above level 3
				$replacement	= "\\1".$subcontent."\\4";										//  insert content of nested page...
				$object->content	= preg_replace( $pattern, $replacement, $object->content );	//  ...into page content
			}
			return $object->content;
		}
	}

	protected function loadSubpage( $path ){
		$logic	= new Logic_Page( $this->env );
		$page	= $logic->getPageFromPath( $path, TRUE );
		if( $page )
			return $page->content;
		$this->env->getMessenger()->noteFailure( 'Die eingebundene Seite "'.$path.'" existiert nicht.' );
	}
}
?>
