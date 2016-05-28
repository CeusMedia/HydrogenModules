<?php
class View_Helper_Info_Dashboard{

	protected $panels;

	public function __construct( $env ){
		$this->env	= $env;
	}

	public function registerPanel( $key, $content ){
		$this->panels[$key]	= $content;
	}

	public function render( $columns = 4 ){
		$this->env->getCaptain()->callHook( 'Dashboard', 'renderPanels', $this );
		$panels			= array();
		$panelChunks	= array_chunk( $this->panels, $columns );
		foreach( $panelChunks as $panelChunk ){
			$list	= array();
			foreach( $panelChunk as $panel ){
				$list[]	= UI_HTML_Tag::create( 'div', $panel, array( 'class' => 'span'.( 12 / $columns ) ) );
			}
			$panels[]	= UI_HTML_Tag::create( 'div', $list, array( 'class' =>'row-fluid' ) );
		}
		return join( '', $panels );
	}

	public function unregisterPanel( $key ){
		if( !isset( $this->panels[$key] ) )
			return FALSE;
		unset( $this->panels[$key] );
		return TRUE;
	}
}
?>