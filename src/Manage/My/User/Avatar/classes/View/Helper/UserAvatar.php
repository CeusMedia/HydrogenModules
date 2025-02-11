<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_UserAvatar
{
	protected Environment $env;
	protected ?Entity_User $user		= NULL;
	protected int $size					= 0;
	protected Dictionary $moduleConfig;
	protected Model_User_Avatar $modelAvatar;
	protected bool $useGravatar		= FALSE;
	protected array $cache				= [];

	/**
	 *	@param		Environment	$env
	 *	@param		Entity_User	$user
	 *	@param		int			$size
	 *	@return		string
	 *	@throws		ReflectionException
	 */
	public static function renderStatic( Environment $env, Entity_User $user, int $size = 0 ): string
	{
		$helper	= new self( $env );
		$helper->setUser( $user );
		$helper->setSize( $size );
		return $helper->render();
	}

	/**
	 *	@param		Environment		$env
	 *	@throws		ReflectionException
	 */
	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->modelAvatar	= new Model_User_Avatar( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_my_user_avatar.', TRUE );
		$this->useGravatar( (bool) $this->moduleConfig->get( 'fallback.gravatar', '' ) );
	}

	/**
	 *	@return		?object
	 */
	public function get(): ?object
	{
		if( NULL === $this->user )
			throw new RuntimeException( "No user set" );
		if( isset( $this->cache[$this->user->userId] ) )
			return $this->cache[$this->user->userId];
		$avatar	= $this->modelAvatar->getByIndices( [
			'userId'	=> $this->user->userId,
//			'status'	=> 1,
		] );
		$this->cache[$this->user->userId]	= $avatar;
		if( !$avatar )
			return NULL;
		return $avatar;
	}

	/**
	 *	@return		?string
	 */
	public function getImageUrl(): ?string
	{
		$avatar	= $this->get();
		if( $avatar ){
			$path		= $this->moduleConfig->get( 'path.images' );
			$filename	= $this->user->userId.'_'.$avatar->filename;
			if( $this->size < $this->moduleConfig->get( 'image.size.large' ) )
				$filename	= $this->user->userId.'__'.$avatar->filename;
			if( $this->size < $this->moduleConfig->get( 'image.size.medium' ) )
				$filename	= $this->user->userId.'___'.$avatar->filename;
			return $this->env->url.$path.$filename;
		}
		if( NULL !== $this->user && $this->useGravatar ){
			$helper	= new View_Helper_Gravatar( $this->env );
			$helper->setUser( $this->user );
			$helper->setSize( $this->size );
			return $helper->getImageUrl();
		}
		return NULL;
	}

	/**
	 *	@return		bool
	 */
	public function has(): bool
	{
		return (bool) $this->get();
	}

	/**
	 *	@return		string
	 */
	public function render(): string
	{
		$avatar	= $this->get();
		if( $avatar ){
			return HtmlTag::create( 'img', NULL, [
				'src'		=> $this->getImageUrl(),
				'class'		=> 'avatar',
				'width'		=> $this->size,
				'height'	=> $this->size,
			] );
		}
		if( NULL !== $this->user && $this->useGravatar ){
			$helper	= new View_Helper_Gravatar( $this->env );
			$helper->setUser( $this->user );
			$helper->setSize( $this->size );
			return $helper->render();
		}
		return '';
	}

	/**
	 *	@param		int		$size
	 *	@return		static
	 */
	public function setSize( int $size ): static
	{
		$this->size	= $size;
		return $this;
	}

	/**
	 *	@param		Entity_User		$user
	 *	@return		static
	 */
	public function setUser( Entity_User $user ): static
	{
		$this->user	= $user;
		return $this;
	}

	/**
	 *	Toggle to use Gravatar module as fallback.
	 *	Detects module UI:Helper:Gravatar.
	 *	@access		public
	 *	@param		boolean		$boolean		Flag: use Gravatar module if available
	 *	@return		static
	 */
	public function useGravatar( bool $boolean ): static
	{
		$hasGravatarModule	= $this->env->getModules()->has( 'UI_Helper_Gravatar' );
		$this->useGravatar	= $hasGravatarModule && $boolean;
		return $this;
	}
}
