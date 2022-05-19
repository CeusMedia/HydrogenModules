<?php
class View_Manage_Image_Slider extends CMF_Hydrogen_View
{
	protected function __onInit()
	{
		$this->env->getPage()->css->theme->addUrl( 'module.manage.image.slider.css' );
	}

	public function add(){}

	public function demo()
	{
		$this->env->getCaptain()->disableHook( 'View', 'onRenderContent' );
	}

	public function edit(){}

	public function editSlide(){}

	public function index(){}

	public function label( $section, $name, $options = [] )
	{
		$options	= array_merge( array(
			'titleElement'	=> 'abbr',
			'titleClass'	=> NULL,
			'suffixClass'	=> 'muted',
		), $options );
		$words		= $this->getWords( $section );
		$keyLabel	= 'label'.ucfirst( $name );
		$keyTitle	= $keyLabel.'_title';
		$keySuffix	= $keyLabel.'_suffix';
		if( !isset( $words->$keyLabel ) )
			return;
		$label		= str_replace( " ", "&nbsp;", $words->$keyLabel );
		if( isset( $words->$keyTitle ) && strlen( trim( $words->$keyTitle ) ) )
			$label	= '&nbsp;'.new UI_HTML_Tag( 'abbr', $label, array(
				'title'		=> trim( $words->$keyTitle ),
				'class'		=> $options['titleClass'],
			) );
		if( isset( $words->$keySuffix ) && strlen( trim( $words->$keySuffix) ) )
			$label	.= '&nbsp;'.new UI_HTML_Tag( 'small', trim( $words->$keySuffix ), array(
				'class'		=> $options['suffixClass'],
			) );
		return $label;
	}
}
