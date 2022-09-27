<?php

use CeusMedia\Common\Net\API\Gravatar;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Gravatar
{
	protected $env;
	protected $user;
	protected $size		= 32;
	protected $rating	= 'g';
	protected $default	= 'mm';

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$config	= $this->env->getConfig()->getAll( 'module.ui_helper_gravatar.', TRUE );
		if( $config->get( 'size' ) )
			$this->setSize( $config->get( 'size' ) );
		if( $config->get( 'rate' ) )
			$this->setRating( $config->get( 'rate' ) );
		if( $config->get( 'default' ) )
			$this->setDefault( $config->get( 'default' ) );
	}

	public function getImageUrl(): string
	{
		if( !$this->user )
			throw new RuntimeException( "No user set" );
		$gravatar	= new Gravatar( $this->size, $this->rating, $this->default );
		return $gravatar->getUrl( $this->user->email );
	}

	public function render(): string
	{
		if( !$this->user )
			throw new RuntimeException( "No user set" );
		$attributes['src']		= $this->getImageUrl( $this->user->email, $this->size, $this->rating );
		$attributes['width']	= $this->size;
		$attributes['height']	= $this->size;
		return HtmlTag::create( 'img', NULL, $attributes );
	}

	public function setDefault( string $theme ): self
	{
		$this->default	= $theme;
		return $this;
	}

	public function setRating( string $rating ): self
	{
		$this->rating	= $rating;
		return $this;
	}

	public function setSize( int $size ): self
	{
		$this->size		= $size;
		return $this;
	}

	public function setUser( $userObjectOrId ): self
	{
		if( is_object( $userObjectOrId ) )
			$this->user	= $userObjectOrId;
		else if( is_int( $userObjectOrId ) ){
			$model	= new Model_User( $this->env );
			$this->user	= $model->get( $userObjectOrId );
		}
		else
			throw new InvalidArgumentException( "Given data is neither an user object nor an user ID" );
			return $this;
	}
}
