<?php
class View_Info_News extends CMF_Hydrogen_View{

	static public function ___onRenderContent( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$pattern	= "/^(.*)(\[news:?(\w+)?\])(.*)$/sU";
		$helper		= new View_Helper_News( $env );
		while( preg_match( $pattern, $data->content ) ){
			$limit			= (int) trim( preg_replace( $pattern, "\\3", $data->content ) );
			$limit			= $limit ? $limit : 10;
			$subcontent		= $helper->render( $limit );											//  load news panel
	//		$subcontent		= preg_replace( "/<h(1|2)>.*<\/h(1|2)>/", "", $subcontent );			//  remove headings above level 3
			$replacement	= "\\1".$subcontent."\\4";												//  insert content of nested page...
			$data->content	= preg_replace( $pattern, $replacement, $data->content );				//  ...into page content
		}
	}

	public function index(){}
}
?>
