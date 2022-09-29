<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Work_Newsletter_ThemeFacts
{
	protected $attributes	= [];
	protected $data;
	protected $env;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	public function render(): string
	{
		$list	= [];
		if( !$this->data )
			throw new RuntimeException( 'No theme data set yet' );

		if( $this->data->license ){
			$license	= $this->data->license;
			if( $this->data->licenseUrl )
				$license	= \HtmlTag::create( 'a', $license, ['href' => $this->data->licenseUrl] );
			$list[]	= \HtmlTag::create( 'dt', 'Lizenz' );
			$list[]	= \HtmlTag::create( 'dd', $license );
		}
		if( $this->data->version ){
			$date	= '';
			if( $this->data->modified ){
				$date	= date( 'Y-m-d', strtotime( $this->data->modified ) );
				$date	= \HtmlTag::create( 'small', '('.$date.')', ['class' => 'muted'] );
			}
			$list[]	= \HtmlTag::create( 'dt', 'Version' );
			$list[]	= \HtmlTag::create( 'dd', $this->data->version.'&nbsp;'.$date );
		}
		if( isset( $this->data->author->name ) ){
			$author	= $this->data->author->name;
			if( $this->data->author->email )
				$author	= \HtmlTag::create( 'a', $author, ['href' => 'mailto:'.$this->data->author->email] );
			$list[]	= \HtmlTag::create( 'dt', 'Autor' );
			$list[]	= $author;
		}
		if( $this->data->author->company ){
			$company	= $this->data->author->company;
			if( $this->data->author->url )
				$company	= \HtmlTag::create( 'a', $company, ['href' => $this->data->author->url] );
			$list[]	= \HtmlTag::create( 'dt', 'Unternehmen' );
			$list[]	= $company;
		}
		return \HtmlTag::create( 'dl', $list, $this->attributes );
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
