<?php
class View_Helper_ModalRegistry extends CMF_Hydrogen_View_Helper_Abstract{

	protected $modals	= array();

	public function __construct( $env ){
		$this->env	= $env;
	}

	public function register( $key, $modal ){
		if( array_key_exists( $key, $this->modals ) )
			throw new RangeException( 'Modal with key "'.$key.'" already registered' );
		$this->modals[$key]	= $modal;
	}

	public function render(){
		$list	= array();
		foreach( $this->modals as $modal )
			$list[]	= $modal->render();
		return join( $list );
	}
}
