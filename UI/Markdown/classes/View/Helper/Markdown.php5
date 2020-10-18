<?php
class View_Helper_Markdown extends CMF_Hydrogen_View
{
	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$this->env	= $env;
	}

/*	static public function ___onRenderContent( CMF_Hydrogen_Environment $env, $context, $module, $data )
	{
		if( in_array( strtolower( $data->type ), array( 'markdown', 'md' ) ) )
			$data->content	= Markdown::defaultTransform( $data->content );
	}*/

	public function transform( string $markdown, bool $wrapped = TRUE ): string
	{
		$renderer	= new CeusMedia\Markdown\Renderer\Html();			//  create renderer
		$html		= $renderer->convert( $markdown );					//  convert to HTML
		if( !$wrapped )
			$html	= preg_replace( "/^<p>(.*)<\/p>$/s", "\\1", $html );
		return $html;
	}

	static public function transformStatic( CMF_Hydrogen_Environment $env, string $markdown, bool $wrapped = TRUE ): string
	{
		$helper	= new self( $env );
		return $helper->transform( $markdown, $wrapped );
	}
}

