<?php
class View_Helper_Mangopay_Entity_WalletLogo{

	protected $env;
	protected $nodeClass	= NULL;
	protected $nodeName		= 'div';
	protected $wallet;
	protected $size			= 'large';

	const SIZE_SMALL		= 'fa-1x';
	const SIZE_MEDIUM		= 'fa-2x';
	const SIZE_LARGE		= 'fa-4x';

	public function __construct( $env ){
		$this->env		= $env;
		$this->setSize( self::SIZE_MEDIUM );
	}

	public function __toString(){
		return $this->render();
	}

	public function setWallet( $wallet ){
		$this->wallet	= $wallet;
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

	public function setSize( $size ){
		$this->size	= $size;
		return $this;
	}

	public function render(){
		$icon	= 'fa-money';
		switch( $this->wallet->Currency ){
			case 'EUR':
				$icon	= 'fa-euro';
				break;
			case 'USD':
				$icon	= 'fa-dollar';
				break;
			case 'YEN':
				$icon	= 'fa-yen';
				break;
		}
		$classes	= array( 'fa fa-fw', $icon, $this->size );
		$image		= UI_HTML_Tag::create( 'i', '', array( 'class' => join( ' ', $classes ) ) );
		return UI_HTML_Tag::create( $this->nodeName, $image, array(
			'class'	=> $this->nodeClass,
		) );
	}

	static public function renderStatic( $env, $number, $nodeName = NULL, $nodeClass = NULL ){
		$instance	= new View_Helper_Mangopay_Entity_CardNumber( $env );
		if( $nodeName !== NULL )
			$this->setNodeName( $nodeName );
		if( $nodeClass !== NULL )
			$this->setNodeClass( $nodeClass );
		return $instance->set( $number )->render();
	}
}
?>
