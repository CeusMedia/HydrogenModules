<?php

use CeusMedia\Bootstrap\Nav\TabbableNavbar;
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\Text\CamelCase as TextCamelCase;
use CeusMedia\Common\UI\HTML\Tree\VariableDump as HtmlVariableDump;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_DevCenter
{
	public array $options	= [];

	protected Dictionary $session;

	protected ?string $height;

	protected bool $open;

	public function __construct( Environment $env )
	{
		$this->session	= $env->getSession();
		$height			= $this->session->get( 'DevCenterHeight' );
		$this->height	= $height ? $height."%" : NULL;
		$this->open		= (bool) $this->session->get( 'DevCenterStatus' );
	}

	public function render( Resource_DevCenter $resourceDevCenter, string $label, $url = NULL ): string
	{
		$tabs	= new TabbableNavbar();
		$tabs->setFixed( 'top' );
		foreach( $resourceDevCenter->getResources() as $resource ){
			$id		= preg_replace( "/[^a-z0-9 _-]/i", "", $resource->key );
			$id		= TextCamelCase::convert( 'tab'.ucfirst( $id ) );
			$data	= HtmlTreeVariableDump::dumpVar( $resource->value, !TRUE, !TRUE );
			$count	= HtmlTreeVariableDump::$count;
			if( $count )
				$resource->label	.= '&nbsp;&nbsp;<span class="badge">'.$count.'</span>';
			$tabs->add( $id, $resource->label, $data );
		}
		$current	= $this->session->get( 'DevCenterTab' );
		$tabs		= $tabs->render();
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
