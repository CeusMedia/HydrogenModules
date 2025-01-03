<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Info_Page extends Controller
{
	/**
	 *	@param		string		$pageId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index( string $pageId = 'index' ): void
	{
		$directAccess	= $this->env->getConfig()->get( 'module.info_page.direct' );			//  get right to directly access page controller
		$isRedirected	= $this->env->getRequest()->get( '__redirected' );						//  check if page controller has been redirected to
		$accessGranted	= 'allowed' === $directAccess || $isRedirected;								//

		$logic		= new Logic_Page( $this->env );													//  get page logic instance
		$pageId		= strlen( trim( $pageId ) ) ? trim( $pageId ) : 'index';						//  ensure page ID is not empty
		/** @var Entity_Page $page */
		$page		= $logic->getPageFromPath( $pageId, TRUE );							//  try to find page for page ID

		if( !$logic->isAccessible( $page ) )
			throw new RuntimeException( 'Access denied', 403 );

		if( $accessGranted && $page ){																//  access allowed and valid page ID
			$this->addData( 'page', $page );													//  provide page object to view
		}
		else{																						//  otherwise
			$this->restart( NULL );																//  redirect to index controller
/*			$words	= (object) $this->getWords( 'index', 'info/pages' );
			$words	= (object) $this->getWords( 'index', 'main' );
			$this->env->getMessenger()->noteNotice( $words->msgPageNotFound );
			$this->env->getResponse()->setStatus( 404 );*/
		}
	}
}
