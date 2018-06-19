<?php
class View_Info_Page extends CMF_Hydrogen_View{

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
				if( $this->hasContentFile( 'info/page/empty.html' ) )
					$object->content	= $this->loadContentFile( 'info/page/empty.html' );
			}
			if( !strlen( trim( $object->content ) ) )
				$object->content  	= " ";
			$page	= $this->env->getPage();
			$page->addBodyClass( 'info-page-'.$object->identifier );
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
