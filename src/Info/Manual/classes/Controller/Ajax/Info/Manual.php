<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Info_Manual extends AjaxController
{
	protected string $sessionPrefix	= 'filter_info_manual_';

	public function setBranchStatus(): void
	{
		$pageId		= (string) $this->request->get( 'pageId' );
		$categoryId	= $this->session->get( $this->sessionPrefix.'categoryId' );
		$sessionKey	= $this->sessionPrefix.'categoryId_'.$categoryId.'_openFolders';

		$openPages	= array_filter( explode( ',', $this->session->get( $sessionKey ) ) );
		if( !in_array( $pageId, $openPages ) )
			$openPages[]	= $pageId;
		else
			unset( $openPages[array_search( $pageId, $openPages )] );
		$this->session->set( $sessionKey, implode( ',', $openPages ) );
		$this->respondData( [
			'pageId'		=> $pageId,
			'categoryId'	=> $categoryId,
			'openPages'		=> $openPages,
		] );
	}

	protected function __onInit(): void
	{
		$this->request	= $this->env->getRequest();
		$this->session	= $this->env->getSession();
	}
}
