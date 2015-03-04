<?php
class View_Info_Page extends CMF_Hydrogen_View{

	static public function ___onRenderContent( $env, $context, $modules, $data ){
		$pattern	= "/^(.*)(\[page:(.+)\])(.*)$/sU";
		$logic		= new Logic_Page( $env );
		while( preg_match( $pattern, $data->content ) ){
			$path	= trim( preg_replace( $pattern, "\\3", $data->content ) );
			$page	= $logic->getPageFromPath( $path, TRUE );
			if( !$page ){
				$data->content	= preg_replace( $pattern, "", $data->content );
				$env->getMessenger()->noteFailure( 'Die eingebundene Seite "'.$path.'" existiert nicht.' );
			}
			else{
				$subcontent		= $page->content;													//  load nested page content
				$subcontent		= preg_replace( "/<h(1|2)>.*<\/h(1|2)>/", "", $subcontent );		//  remove headings above level 3
				$replacement	= "\\1".$subcontent."\\4";											//  insert content of nested page...
				$data->content		= preg_replace( $pattern, $replacement, $data->content );		//  ...into page content
			}
		}
	}

	public function index(){
//		$config		= $this->env->getConfig()->get( 'module.info_pages.', TRUE );
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
			return $this->renderContent( $object->content, $object->format );
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
