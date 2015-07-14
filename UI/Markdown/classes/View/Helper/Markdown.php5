<?php
class View_Helper_Markdown extends CMF_Hydrogen_View{

	public function __construct( $env ){
		$this->env	= $env;
	}

	static public function ___onRenderContent( $env, $context, $module, $data ){
		if( in_array( strtolower( $data->type ), array( 'markdown', 'md' ) ) )
			$data->content	= Markdown::defaultTransform( $data->content );
	}

	public function transform( $markdown, $wrapped = TRUE ){
		$html	= Markdown::defaultTransform( $markdown );
		if( $wrapped )
			return $html;
		return preg_replace( "/^<p>(.*)<\/p>$/s", "\\1", $html );
	}

	static public function transformStatic( $env, $markdown, $wrapped = TRUE ){
		$helper	= new self( $env );
		return $helper->transform( $markdown, $wrapped );
	}
}
?>
