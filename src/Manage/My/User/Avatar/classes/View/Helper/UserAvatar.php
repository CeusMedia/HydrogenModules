<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_UserAvatar
{
	protected Environment $env;
	protected ?string $userId		= NULL;
	protected $size;
	protected Dictionary $moduleConfig;
	protected Model_User_Avatar $modelAvatar;
	protected bool $useGravatar		= FALSE;
	protected array $cache				= [];

	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->modelAvatar	= new Model_User_Avatar( $this->env );
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.manage_my_user_avatar.', TRUE );
		$this->useGravatar( (bool) $this->moduleConfig->get( 'fallback.gravatar', '' ) );
	}

	public function get()
	{
		if( !$this->userId )
			throw new RuntimeException( "No user set" );
		if( isset( $this->cache[$this->userId] ) )
			return $this->cache[$this->userId];
		$avatar	= $this->modelAvatar->getByIndices( [
			'userId'	=> $this->userId,
//			'status'	=> 1,
		] );
		$this->cache[$this->userId]	= $avatar;
		if( !$avatar )
			return NULL;
		return $avatar;
	}

	public function getImageUrl()
	{
		$avatar	= $this->get();
		if( $avatar ){
			$path		= $this->moduleConfig->get( 'path.images' );
			$filename	= $this->userId.'_'.$avatar->filename;
			if( $this->size < $this->moduleConfig->get( 'image.size.large' ) )
				$filename	= $this->userId.'__'.$avatar->filename;
			if( $this->size < $this->moduleConfig->get( 'image.size.medium' ) )
				$filename	= $this->userId.'___'.$avatar->filename;
			return $this->env->url.$path.$filename;
		}
		if( $this->useGravatar ){
			$helper	= new View_Helper_Gravatar( $this->env );
			$helper->setUser( $this->userId );
			$helper->setSize( $this->size );
			return $helper->getImageUrl();
		}
	}

	public function has(): bool
	{
		return (bool) $this->get();
	}

	public function render(): string
	{
		$avatar	= $this->get();
		if( $avatar ){
			return HtmlTag::create( 'img', NULL, array(
				'src'		=> $this->getImageUrl(),
				'class'		=> 'avatar',
				'width'		=> $this->size,
				'height'	=> $this->size,
			) );
		}
		if( $this->useGravatar ){
			$helper	= new View_Helper_Gravatar( $this->env );
			$helper->setUser( $this->userId );
			$helper->setSize( $this->size );
			return $helper->render();
		}
		return '';
	}

	public static function renderStatic( Environment $env, $userObjectOrId, $size ): string
	{
		$helper	= new self( $env );
		$helper->setUser( $userObjectOrId );
		$helper->setSize( $size );
		return $helper->render();
	}

	public function setSize( $size ): self
	{
		$this->size	= $size;
		return $this;
	}

	public function setUser( $userObjectOrId ): self
	{
		if( is_object( $userObjectOrId ) )
			$this->userId	= (string) $userObjectOrId->userId;
		else if( is_int( $userObjectOrId ) )
			$this->userId	= $userObjectOrId;
		else
			throw new InvalidArgumentException( "Given data is neither an user object nor an user ID" );
		return $this;
	}

	/**
	 *	Toggle to use Gravatar module as fallback.
	 *	Detects module UI:Helper:Gravatar.
	 *	@access		public
	 *	@param		boolean		$boolean		Flag: use Gravatar module if available
	 *	@return		void
	 */
	public function useGravatar( bool $boolean ): self
	{
		$hasGravatarModule	= $this->env->getModules()->has( 'UI_Helper_Gravatar' );
		$this->useGravatar	= $hasGravatarModule && $boolean;
		return $this;
	}
}
