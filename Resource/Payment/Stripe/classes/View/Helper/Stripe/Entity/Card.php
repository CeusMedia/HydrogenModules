<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Stripe_Entity_Card extends View_Helper_Stripe_Abstract{

	protected $nodeClass	= NULL;
	protected $nodeName		= 'span';
	protected $card;
	protected $url;

	public function render(){
		$helperCardLogo		= new View_Helper_Stripe_Entity_CardProviderLogo( $this->env );
		$helperCardNumber	= new View_Helper_Stripe_Entity_CardNumber( $this->env );
		$helperCardLogo->setSize( View_Helper_Stripe_Entity_CardProviderLogo::SIZE_SMALL );
		$helperCardLogo->setNodeName( 'span' );
		$logo		= $helperCardLogo->setProvider( $this->card->CardProvider )->render();
		$number		= $helperCardNumber->set( $this->card->Alias )->render();
		$item		= $logo.$number;
		$attributes	= array(
			'class'		=> 'card-list-item-small',
		);
		if( $this->url ){
			$url	= sprinf( $this->url, $this->card->id );
			if( $this->nodeName == 'a' )
				$attributes['href']	= $url;
			else
 				$attributes['onclick']	= 'document.location.href="'.$url.'";';
		}
		return HtmlTag::create( $this->nodeName, $item, $attributes );
	}

	public function set( $card ){
		$this->card	= $card;
		return $this;
	}

	public function setNodeClass( $classNames ){
		$this->nodeClass	= $classNames;
		return $this;
	}

	public function setNodeName( $nodeName ){
		$this->nodeName	= $nodeName;
		return $this;
	}

	public function setUrl( $url ){
		$this->url		= $url;
	}
}
?>
