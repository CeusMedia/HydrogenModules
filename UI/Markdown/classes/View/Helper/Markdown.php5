<?php

use CeusMedia\Common\Alg\Obj\Constant as ObjectConstant;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;
use CeusMedia\Markdown\Renderer\Html as MarkdownToHtmlRenderer;

class View_Helper_Markdown extends View
{
	protected $renderer;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$module		= $this->env->getModules()->get( 'UI_Markdown' );
		if( array_key_exists( 'renderer', $module->config ) ){
			$constant	= 'RENDERER_'.strtoupper( $module->config['renderer']->value );
			$constants	= new ObjectConstant( MarkdownToHtmlRenderer::class );
			if( $constants->hasValue( $constant ) )
				$this->renderer	= $constants->getValue( $constant );
		}
	}

/*	static public function ___onRenderContent( Environment $env, $context, $module, $data )
	{
		if( in_array( strtolower( $data->type ), array( 'markdown', 'md' ) ) )
			$data->content	= Markdown::defaultTransform( $data->content );
	}*/

	public function setRenderer( int $renderer ): self
	{
		$this->renderer		= $renderer;
		return $this;
	}

	public function transform( string $markdown, bool $wrapped = TRUE, ?int $renderer = NULL ): string
	{
		$renderer	= new MarkdownToHtmlRenderer();			//  create renderer
		if( NULL !== $this->renderer )
			$renderer->setRenderer( $this->renderer );
		else if( NULL !== $renderer )
			$renderer->setRenderer( $renderer );
		$html		= $renderer->convert( $markdown );					//  convert to HTML
		if( !$wrapped )
			$html	= preg_replace( "/^<p>(.*)<\/p>$/s", "\\1", $html );
		return $html;
	}

	public static function transformStatic( Environment $env, string $markdown, bool $wrapped = TRUE, ?int $renderer = NULL ): string
	{
		$helper	= new self( $env );
		return $helper->transform( $markdown, $wrapped, $renderer );
	}
}
