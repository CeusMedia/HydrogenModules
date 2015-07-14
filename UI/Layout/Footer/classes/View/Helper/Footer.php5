<?php
class View_Helper_Footer{

	static public function ___onPageBuild( $env, $context, $module, $data = array() ){
		$pattern	= "/^(.*)(\[footer\])(.*)$/sU";
		if( preg_match( $pattern, $data->content ) ){
			$links	= array();
			if( isset( $scopes->footer ) )
				foreach( $scopes->footer as $pageId => $page )
					$links[$page->path]	= $page->label;

			$footer	= array( array( 'id' => 'timestamp', 'label' => 'Date & Time: <b>'.date( "d.m.Y H:i:s" ).'</b>' ) );
			if( $env->getDatabase() )
				$footer[]	= array( 'id' => 'database', 'label' => 'Database Queries: <b>'.$env->getDatabase()->numberStatements.'</b>' );
			if( $env->getModules()->has( 'Server_System_Load' ) )
				$footer[]	= array( 'id' => 'system-load', 'title' => 'System Load' );
			foreach( $links as $path => $label )
				$footer[]	= array( 'label' => UI_HTML_Tag::create( 'a', $label, array( 'href' => $path ) ) );

			$list	= array();
			foreach( $footer as $entry ){
				$label	= "";
				if( isset( $entry['label'] ) ){
					$label	= $entry['label'];
					unset( $entry['label'] );
				}
				$list[]	= UI_HTML_Tag::create( 'li', $label, $entry );
			}
			$footer		= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'unstyled' ) );
			$footer		= UI_HTML_Tag::create( 'div', $footer, array( 'class' => 'container' ) );
			$content	= UI_HTML_Tag::create( 'div', $footer, array( 'id' => 'layout-footer' ) );
			$data->content  = preg_replace( $pattern, "\\1".$content."\\4", $data->content );
		}
	}
}
?>
