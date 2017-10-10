<?php
class View_Helper_Mangopay_Entity_IBAN{

	protected $env;
	protected $nodeClass	= NULL;
	protected $nodeName		= 'tt';
	protected $iban;

	public function __construct( $env ){
		$this->env		= $env;
	}

	public function __toString(){
		return $this->render();
	}

	public function set( $iban ){
		$this->iban	= $iban;
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

	public function render(){
		$parts		= str_split( trim( $this->iban ), 4 );
		$label		= implode( ' ', $parts );
		return UI_HTML_Tag::create( $this->nodeName, $label, array(
			'class'	=> $this->nodeClass,
		) );
	}

	static public function renderStatic( $env, $iban, $nodeName = NULL, $nodeClass = NULL ){
		$instance	= new self( $env );
		if( $nodeName !== NULL )
			$this->setNodeName( $nodeName );
		if( $nodeClass !== NULL )
			$this->setNodeClass( $nodeClass );
		return $instance->set( $iban )->render();
	}
}
?>
