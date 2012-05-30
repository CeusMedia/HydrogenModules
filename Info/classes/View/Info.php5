<?php
/**
 *	View.
 *	@category		cmApps
 *	@package		Chat.Client.View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
/**
 *	View.
 *	@category		cmApps
 *	@package		Chat.Client.View
 *	@extends		CMF_Hydrogen_View
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012 Ceus Media
 *	@version		$Id$
 */
class View_Info extends CMF_Hydrogen_View {

	protected function __onInit() {
#		$this->env->page->addThemeStyle( 'screen/site.info.css' );			
	}

	public function index(){
		$fileName	= $this->getData( 'fileName' );													//  get requested content file
		$ext		= pathinfo( $fileName, PATHINFO_EXTENSION );
		if( !$ext )
			$fileName	.= '.html';

		if( $fileName ){																			//  a file has been requested
			if( $this->hasContentFile( 'html/'.$fileName ) ){										//  content file is existing
				if( $fileName == 'test.html' )														//  request to test.html
					continue;																		//  will be denied
				$content	= $this->loadContentFile( 'html/'.$fileName );							//  load content from file
				if( !strlen( trim( $content ) ) )													//  no visible content stored
					return NULL;																	//  note request failure to dispatcher
				return $content;																	//  return HTML content to dispatcher
			}
			$this->env->getResponse()->setStatus( '404 Not found' );
			return $this->loadContentFile( 'html/info/404.html' );											//  load content from file
#			$this->restart( './' );
		}
	}
}
?>