<?php
/**
 *	Locale Content Management Controller.
 *	@category		cmApps
 *	@package		Chat.Admin.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2011-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\Common\FS\File\Editor as FileEditor;
use CeusMedia\Common\Net\HTTP\PartitionSession;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Manage_Content_Locale extends AjaxController
{
	protected HttpRequest $request;
	protected PartitionSession $session;
	protected string $language;
	protected string $basePath;
	protected string $folderPathFull;
	protected ?string $file			= NULL;

	public static array $folders	= [
		'locale'		=> '',
		'html'			=> 'html/',
		'mail'			=> 'mail/',
	];

	public static string $filterPrefix	= 'filter_manage_content_locale_';

	public function saveContent(): void
	{
		if( !( $this->language && $this->file ) )
			$this->respondError( 0, 'File or language not set' );

		$content	= $this->request->get( 'content' );
		$editor		= new FileEditor( $this->folderPathFull.$this->file );
		$editor->writeString( $content );
		$this->respondData( TRUE );
	}
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();

		$basePath		= $this->env->getConfig()->get( 'path.locales' );

		$this->language		= $this->env->getConfig()->get( 'locale.default' );
		if( $this->env->getModules()->has( 'Resource_Frontend' ) ){
			$frontend		= Logic_Frontend::getInstance( $this->env );
			$basePath		= $frontend->getPath( 'locales' );
			$this->language	= $frontend->getConfigValue( 'locale.default' );
		}
		if( $this->session->get( static::$filterPrefix.'language' ) )
			$this->language	= $this->session->get( static::$filterPrefix.'language' );

		$basePath		= preg_replace( '/^\.\//', '', $basePath );
		$folder			= $this->session->get( static::$filterPrefix.'folder' );
		$this->file		= $this->session->get( static::$filterPrefix.'file' );
		$this->folderPathFull	= $basePath.$this->language.'/'.static::$folders[$folder];
	}
}
