<?php
class View_Helper_DevCenter{

	public $options	= array();
	protected $session;
	protected $height;
	protected $open;

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->session	= $env->getSession();
		$height			= $this->session->get( 'DevCenterHeight' );
		$this->height	= $height ? $height."%" : NULL;
		$this->open		= (bool) $this->session->get( 'DevCenterStatus' );
	}

	public function render( Resource_DevCenter $resourceDevCenter, $label, $url = NULL ){
		$tabs	= new \CeusMedia\Bootstrap\TabbableNavbar();
		$tabs->setFixed( 'top' );
		foreach( $resourceDevCenter->getResources() as $resource ){
			$id		= preg_replace( "/[^a-z0-9 _-]/i", "", $resource->key );
			$id		= Alg_Text_CamelCase::convert( 'tab'.ucfirst( $id ) );
			$data	= UI_HTML_Tree_VariableDump::dumpVar( $resource->value, !TRUE, !TRUE );
			$count	= UI_HTML_Tree_VariableDump::$count;
			if( $count )
				$resource->label	.= '&nbsp;&nbsp;<span class="badge">'.$count.'</span>';
			$tabs->add( $id, $resource->label, $data );
		}
		$current	= $this->session->get( 'DevCenterTab' );
		$tabs		= $tabs->render( $current, $label, $url );
		$content	= UI_HTML_Tag::create( 'div', $tabs, array( 'id' => "DevCenterContent" ) );		//
		$handleTop	= UI_HTML_Tag::create( 'div', "====", array( 'id' => 'DevCenterHandleTop' ) );	//

		$style		= array();
		if( is_int( $height = $this->height ) || is_string( $this->height ) )
			$style['height']	= strpos( $height, '%' ) ? $height : $height.'px';
		if( !$this->open )
			$style['display']	= "none";

		$attributes	= array( 'id' => "DevCenter" );
		if( $style ){
			$attributes['style']	= array();
			foreach( $style as $key => $value )
				$attributes['style'][]	= $key.': '.$value;
			$attributes['style']	= join( '; ', $attributes['style'] );
		}
		return UI_HTML_Tag::create( 'div', $handleTop.$content, $attributes );
	}
}
?>
