<?php
class View_Helper_Markdown extends CMF_Hydrogen_View{

	public function __construct( $env ){
		$this->env	= $env;
	}

	public function transform( $markdown ){
		return Markdown::defaultTransform( $markdown );
	}

	static public function transformStatic( $env, $markdown ){
		$helper	= new self( $env );
		return $helper->transform( $markdown );
	}
}
?>
