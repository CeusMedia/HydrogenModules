<?php
class View_Helper_Markdown extends CMF_Hydrogen_View{

	public function __construct( $env ){
		$this->env	= $env;
	}

	static public function ___onRenderContent( $env, $context, $module, $data ){
		if( in_array( strtolower( $data->type ), array( 'markdown', 'md' ) ) )
			$data->content	= Markdown::defaultTransform( $data->content );
	}

	public function transform( $markdown ){
		return Markdown::defaultTransform( $markdown );
	}

	static public function transformStatic( $env, $markdown ){
		return Markdown::defaultTransform( $markdown );
	}
}
?>
