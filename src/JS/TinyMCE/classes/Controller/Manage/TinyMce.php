<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_TinyMce extends Controller
{
	protected HttpRequest $request;
	protected Dictionary $session;
	protected View_Helper_TinyMce_FileBrowser $helper;
	protected View_Helper_Thumbnailer $thumbnailer;
	protected string $sessionPrefix;
	protected string $cssClassPrefix		= 'list';
	protected string $baseUrl;

	public function index( string $mode = 'image' )
	{
		$topicId	= (int) $this->session->get( $this->sessionPrefix.$mode );
		$path		= (string) $this->session->get( $this->sessionPrefix.$mode.'_'.$topicId );
		$this->helper->setPath( $path );
		$this->helper->setTopicId( $topicId );
		$this->helper->setDisplayMode( (int) $this->session->get( $this->sessionPrefix.'displayMode' ) );

		$helper		= new View_Helper_TinyMce( $this->env );
		if( $mode === 'image' ){
			$this->helper->setTopics( $helper->getImageList() );
			$this->helper->setSourceMode( View_Helper_TinyMce_FileBrowser::SOURCE_MODE_IMAGE );
		}
		else{
			$this->helper->setTopics( $helper->getLinkList() );
			$this->helper->setSourceMode( View_Helper_TinyMce_FileBrowser::SOURCE_MODE_LINK );
		}
		$this->helper->render();
	}

	public function setTopic( string $mode, string $topicId ): void
	{
		$this->session->set( $this->sessionPrefix.$mode, $topicId );
		$this->restart( $mode, TRUE );
	}

	public function setDisplayMode( string $mode, string $displayMode ): void
	{
		$this->session->set( $this->sessionPrefix.'displayMode', $displayMode );
		$this->restart( $mode, TRUE );
	}

	public function setPath( string $mode, string $topicId, string $pathBase64 = '' ): void
	{
		$this->session->set( $this->sessionPrefix.$mode.'_'.$topicId, base64_decode( $pathBase64 ) );
		$this->restart( $mode, TRUE );
	}

	protected function __onInit(): void
	{
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->sessionPrefix	= 'manager_tinymce_';

		$this->helper		= new View_Helper_TinyMce_FileBrowser( $this->env );
		$this->thumbnailer	= new View_Helper_Thumbnailer( $this->env, 128, 128 );

		$this->baseUrl	= $this->env->url;
		if( $this->env->getModules()->has( 'Resource_Frontend' ) )
			$this->baseUrl	= Logic_Frontend::getInstance( $this->env )->getPath();
	}
}
