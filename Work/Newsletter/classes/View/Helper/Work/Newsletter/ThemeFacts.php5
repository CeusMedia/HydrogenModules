<?php
class View_Helper_Work_Newsletter_ThemeFacts
{
	protected $attributes	= array();
	protected $data;

	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$this->env	= $env;
	}

	public function render(): string
	{
		$list	= array();
		if( !$this->data )
			throw new RuntimeException( 'No theme data set yet' );

		if( $this->data->license ){
			$license	= $this->data->license;
			if( $this->data->licenseUrl )
				$license	= \UI_HTML_Tag::create( 'a', $license, array( 'href' => $this->data->licenseUrl ) );
			$list[]	= \UI_HTML_Tag::create( 'dt', 'Lizenz' );
			$list[]	= \UI_HTML_Tag::create( 'dd', $license );
		}
		if( $this->data->version ){
			$date	= '';
			if( $this->data->modified ){
				$date	= date( 'Y-m-d', strtotime( $this->data->modified ) );
				$date	= \UI_HTML_Tag::create( 'small', '('.$date.')', array( 'class' => 'muted' ) );
			}
			$list[]	= \UI_HTML_Tag::create( 'dt', 'Version' );
			$list[]	= \UI_HTML_Tag::create( 'dd', $this->data->version.'&nbsp;'.$date );
		}
		if( isset( $this->data->author->name ) ){
			$author	= $this->data->author->name;
			if( $this->data->author->email )
				$author	= \UI_HTML_Tag::create( 'a', $author, array( 'href' => 'mailto:'.$this->data->author->email ) );
			$list[]	= \UI_HTML_Tag::create( 'dt', 'Autor' );
			$list[]	= $author;
		}
		if( $this->data->author->company ){
			$company	= $this->data->author->company;
			if( $this->data->author->url )
				$company	= \UI_HTML_Tag::create( 'a', $company, array( 'href' => $this->data->author->url ) );
			$list[]	= \UI_HTML_Tag::create( 'dt', 'Unternehmen' );
			$list[]	= $company;
		}
		return \UI_HTML_Tag::create( 'dl', $list, $this->attributes );
	}

	public function setListAttributes( array $attributes ): self
	{
		$this->attributes	= $attributes;
		return $this;
	}

	public function setThemeData( $data ): self
	{
		$this->data	= $data;
		return $this;
	}
}
