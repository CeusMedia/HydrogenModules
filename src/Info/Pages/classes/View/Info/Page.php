<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\View;

class View_Info_Page extends View
{
	/**
	 *	@return		string|NULL
	 *	@throws		ReflectionException
	 */
	public function index(): ?string
	{
//		$config		= $this->env->getConfig()->get( 'module.info_pages.', TRUE );
		$page		= $this->env->getPage();

		$data		= new Dictionary( $this->getData() );											//  wrap view data into dictionary object
		/** @var ?Entity_Page $object */
		$object		= $data->get( 'page' );
		if( NULL === $object )																		//  no page has been found for called path
			return NULL;

		$separator	= $this->env->getConfig()->get( 'module.info_pages.title.separator' );		//  get title part separator
		foreach( $object->parents as $parent )														//  iterate superior pages
			$page->setTitle( $parent->title, -1, ' '.$separator.' ' );				//  append parent page title
		$page->setTitle( $object->title, -1, ' '.$separator.' ' );					//  append current page title
		if( '' === trim( $object->content ) ){														//  page has HTML content
			$words	= $this->getWords( 'index', 'info/pages' );
			$this->env->getMessenger()->noteNotice( $words->msgEmptyContent );
			if( $this->hasContentFile( 'info/page/empty.html' ) )
				$object->content	= $this->loadContentFile( 'info/page/empty.html' );
		}
		if( '' === trim( $object->content ) )
			$object->content  	= ' ';
		$page	= $this->env->getPage();
		$page->addBodyClass( 'info-page-'.$object->identifier );
		return $this->renderContent( $object->content, $object->format );
	}

	/**
	 *	@param		string		$path
	 *	@return		string
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function loadSubpage( string $path ): string
	{
		$logic	= new Logic_Page( $this->env );
		$page	= $logic->getPageFromPath( $path, TRUE );
		if( NULL !== $page )
			return $page->content;
		$this->env->getMessenger()->noteFailure( 'Die eingebundene Seite "'.$path.'" existiert nicht.' );
		return '';
	}
}
