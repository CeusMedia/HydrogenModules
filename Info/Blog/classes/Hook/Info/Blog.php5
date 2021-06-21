<?php
class Hook_Info_Blog extends CMF_Hydrogen_Hook
{
	public static function onViewRenderContent( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() )
	{
		$data		= (object) $payload;
		$pattern	= "/^(.*)(\[blog:(.+)\])(.*)$/sU";
		while( preg_match( $pattern, $data->content ) ){
			$id				= trim( preg_replace( $pattern, "\\3", $data->content ) );
			$content		= View_Info_Blog::renderPostAbstractPanelStatic( $env, $id );
			$replacement	= "\\1".$content."\\4";													//  insert content of nested page...
			$data->content	= preg_replace( $pattern, $replacement, $data->content );				//  ...into page content
		}
	}
}
