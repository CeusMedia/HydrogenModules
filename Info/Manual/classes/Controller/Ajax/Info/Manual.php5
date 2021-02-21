<?php
class Controller_Ajax_Info_Manual extends CMF_Hydrogen_Controller_Ajax
{
	protected $request;
	protected $session;
	protected $sessionPrefix	= 'filter_info_manual_';

	public function setBranchStatus()
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
		$this->respondData( array(
			'pageId'		=> $pageId,
			'categoryId'	=> $categoryId,
			'openPages'		=> $openPages,
		) );
	}

	protected function __onInit()
	{
		$this->request	= $this->env->getRequest();
		$this->session	= $this->env->getSession();
	}
}
