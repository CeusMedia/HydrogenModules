<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Manage_Image_Slider extends View
{
	public function add()
	{
	}

	public function demo(): void
	{
		$this->env->getCaptain()->disableHook( 'View', 'onRenderContent' );
	}

	public function edit()
	{
	}

	public function editSlide()
	{
	}

	public function index()
	{
	}

	public function label( $section, $name, $options = [] )
	{
		$options	= array_merge( [
			'titleElement'	=> 'abbr',
			'titleClass'	=> NULL,
			'suffixClass'	=> 'muted',
		], $options );
		$words		= $this->getWords( $section );
		$keyLabel	= 'label'.ucfirst( $name );
		$keyTitle	= $keyLabel.'_title';
		$keySuffix	= $keyLabel.'_suffix';
		if( !isset( $words->$keyLabel ) )
			return;
		$label		= str_replace( " ", "&nbsp;", $words->$keyLabel );
		if( isset( $words->$keyTitle ) && strlen( trim( $words->$keyTitle ) ) )
			$label	= '&nbsp;'.HtmlTag::create( 'abbr', $label, array(
				'title'		=> trim( $words->$keyTitle ),
				'class'		=> $options['titleClass'],
			) );
		if( isset( $words->$keySuffix ) && strlen( trim( $words->$keySuffix) ) )
			$label	.= '&nbsp;'.HtmlTag::create( 'small', trim( $words->$keySuffix ), [
				'class'		=> $options['suffixClass'],
			] );
		return $label;
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->css->theme->addUrl( 'module.manage.image.slider.css' );
	}
}
