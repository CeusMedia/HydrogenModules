<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Stripe_Entity_Card extends View_Helper_Stripe_Abstract
{
	protected ?object $card			= NULL;
	protected ?string $nodeClass	= NULL;
	protected string $nodeName		= 'span';
	protected ?string $url			= NULL;

	public function render(): string
	{
		if( NULL === $this->card )
			throw new RuntimeException( 'No card object set' );
		$helperCardLogo		= new View_Helper_Stripe_Entity_CardProviderLogo( $this->env );
		$helperCardNumber	= new View_Helper_Stripe_Entity_CardNumber( $this->env );
		$helperCardLogo->setSize( View_Helper_Stripe_Entity_CardProviderLogo::SIZE_SMALL );
		$helperCardLogo->setNodeName( 'span' );
		$logo		= $helperCardLogo->setProvider( $this->card->CardProvider )->render();
		$number		= $helperCardNumber->set( $this->card->Alias )->render();
		$item		= $logo.$number;
		$attributes	= [
			'class'		=> 'card-list-item-small',
		];
		if( $this->url ){
			$url	= sprintf( $this->url, $this->card->id );
			if( $this->nodeName == 'a' )
				$attributes['href']	= $url;
			else
 				$attributes['onclick']	= 'document.location.href="'.$url.'";';
		}
		return HtmlTag::create( $this->nodeName, $item, $attributes );
	}

	public function set( object $card ): self
	{
		$this->card	= $card;
		return $this;
	}

	public function setNodeClass( string $classNames ): self
	{
		$this->nodeClass	= $classNames;
		return $this;
	}

	public function setNodeName( string $nodeName ): self
	{
		$this->nodeName	= $nodeName;
		return $this;
	}

	public function setUrl( string $url ): self
	{
		$this->url		= $url;
		return $this;
	}
}
