<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_DevCenter
{
	public $options	= [];

	protected $session;

	protected $height;

	protected $open;

	public function __construct( Environment $env )
	{
		$this->session	= $env->getSession();
		$height			= $this->session->get( 'DevCenterHeight' );
		$this->height	= $height ? $height."%" : NULL;
		$this->open		= (bool) $this->session->get( 'DevCenterStatus' );
	}

	public function render( Resource_DevCenter $resourceDevCenter, string $label, $url = NULL ): string
	{
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
		$content	= HtmlTag::create( 'div', $tabs, ['id' => "DevCenterContent"] );		//
		$handleTop	= HtmlTag::create( 'div', "====", ['id' => 'DevCenterHandleTop'] );	//

		$style		= [];
		if( is_int( $height = $this->height ) || is_string( $this->height ) )
			$style['height']	= strpos( $height, '%' ) ? $height : $height.'px';
		if( !$this->open )
			$style['display']	= "none";

		$attributes	= ['id' => "DevCenter"];
		if( $style ){
			$attributes['style']	= [];
			foreach( $style as $key => $value )
				$attributes['style'][]	= $key.': '.$value;
			$attributes['style']	= join( '; ', $attributes['style'] );
		}
		return HtmlTag::create( 'div', $handleTop.$content, $attributes );
	}
}
