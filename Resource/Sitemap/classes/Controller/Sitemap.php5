<?php
class Controller_Sitemap extends CMF_Hydrogen_Controller{

	public function index( $forceFormat = NULL ){
		$logic		= Logic_Sitemap::getInstance( $this->env );
		$options	= $this->env->getConfig()->getAll( 'module.resource_sitemap.', TRUE );
		$this->env->getModules()->callHook( 'Sitemap', 'registerLinks', $logic );

		$this->addData( 'options', $options );
		$this->addData( 'links', $logic->getLinks() );

		$format		= 'XML';
		if( $options->get( 'html.enabled' ) ){
			if( in_array( strtoupper( $forceFormat ), array( 'XML', 'HTML' ) ) )
				$format	= strtoupper( $forceFormat );
			else{
				$acceptHeader		= getEnv( 'HTTP_ACCEPT' );
				$contentTypesHtml	= array( 'application/xhtml+xml', 'text/html' );
				$contentTypesXml	= array( 'application/xml', 'text/xml' );
				$contentTypes		= array_merge( $contentTypesHtml, $contentTypesXml );
				$negotiated			= $this->negotiateContentType( $acceptHeader, $contentTypes );
				$format				= in_array( $negotiated, $contentTypesHtml ) ? 'HTML' : 'XML';
			}
		}
		$this->addData( 'format', $format );
	}

	public function submit(){
		$logic	= Logic_Sitemap::getInstance( $this->env );
		$logic->submitToProviders();
		exit;
	}

	protected function negotiateContentType( $headerFieldValue, $contentTypes ){
		$list	= [];
		$values	= $this->getQualifiedValues( $headerFieldValue );
		foreach( $values as $mimeType => $quality ){
			if( in_array( $mimeType, $contentTypes ) )
				$list[]	= $mimeType;
		}
		return  array_shift( $list );
	}

	protected function getQualifiedValues( $headerFieldValue ){
		$list	= [];
		$values	= preg_split( '/, */', $headerFieldValue );
		foreach( $values as $value ){
			$parts				= explode( ';q=', $value, 2 );
			$list[$parts[0]]	= count( $parts ) === 2 ? $parts[1] : 1;
		}
		arsort( $list );
		return $list;
	}
}
