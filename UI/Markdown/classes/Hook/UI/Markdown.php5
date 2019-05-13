<?php
class Hook_UI_Markdown extends CMF_Hydrogen_View{

	static public function onRenderContent( CMF_Hydrogen_Environment $env, $context, $module, $data ){
		if( in_array( strtolower( $data->type ), array( 'markdown', 'md' ) ) )
			$data->content	= Markdown::defaultTransform( $data->content );
	}
}
?>
