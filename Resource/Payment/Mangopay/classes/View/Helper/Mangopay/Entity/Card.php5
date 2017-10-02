<?php
class View_Helper_Mangopay_Entity_Card{

	protected $env;
	protected $nodeClass	= NULL;
	protected $nodeName		= 'span';
	protected $card;
	protected $url;

	public function __construct( $env ){
		$this->env		= $env;
	}

	public function __toString(){
		return $this->render();
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

	public function render(){
		$helperCardLogo		= new View_Helper_Mangopay_Entity_CardProviderLogo( $this->env );
		$helperCardNumber	= new View_Helper_Mangopay_Entity_CardNumber( $this->env );
		$helperCardLogo->setSize( View_Helper_Mangopay_Entity_CardProviderLogo::SIZE_SMALL );
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
		return UI_HTML_Tag::create( $this->nodeName, $item, $attributes );
	}
}
?>
