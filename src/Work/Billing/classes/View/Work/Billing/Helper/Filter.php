<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\HydrogenFramework\Environment;

class View_Work_Billing_Helper_Filter
{
	protected Environment $env;
	protected Dictionary $session;
	protected array $filters			= [];
	protected string $sessionPrefix;
	protected string $url;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->session	= $this->env->getSession();
	}

	public function __toString(): string
	{
		return $this->render();
	}

	public function render(): string
	{
		if( !$this->filters )
			throw new RuntimeException( 'No filter columns set' );
		if( !$this->url )
			throw new RuntimeException( 'No filter URL set' );
		if( !$this->sessionPrefix )
			throw new RuntimeException( 'No filter session prefix set' );

		$formFields		= [];
		foreach( $this->filters as $filter ){
			$value		= $this->session->get( $this->sessionPrefix.$filter );
			switch( $filter ){
				case 'year':
					$optYear	= [
						''	=> '- alle -',
					];
					$optYear[date( "Y" )]	= date( "Y" );
					$optYear[date( "Y" )-1]	= date( "Y" )-1;
					$optYear[date( "Y" )-2]	= date( "Y" )-2;
					$optYear	= HtmlElements::Options( $optYear, $value );
					$formFields[]	= '
					<div class="span2">
						<label for="input_year">Jahr</label>
						<select name="year" id="input_year" class="span12" onchange="this.form.submit()">'.$optYear.'</select>
					</div>';
					break;
				case 'month':
					$optMonth	= [
						''		=> '- alle -',
						'01'	=> 'Januar',
						'02'	=> 'Februar',
						'03'	=> 'März',
						'04'	=> 'April',
						'05'	=> 'Mai',
						'06'	=> 'Juni',
						'07'	=> 'Juli',
						'08'	=> 'August',
						'09'	=> 'September',
						'10'	=> 'Oktober',
						'11'	=> 'November',
						'12'	=> 'Dezember',
					];
					$optMonth	= HtmlElements::Options( $optMonth, $value );
					$formFields[]	= '
					<div class="span2">
						<label for="input_month">Monat</label>
						<select name="month" id="input_month" class="span12" onchange="this.form.submit()">'.$optMonth.'</select>
					</div>';
					break;
			}
		}

		return '
		<form action="'.$this->url.'" class="form-list-filter" method="post">
			<div class="row-fluid">
				'.join( $formFields ).'
			</div>
		</form>';
	}

	public function setFilters( array $filters ): self
	{
		$this->filters	= $filters;
		return $this;
	}

	public function setSessionPrefix( string $sessionPrefix ): self
	{
		$this->sessionPrefix	= $sessionPrefix;
		return $this;
	}

	public function setUrl( string $url ): self
	{
		$this->url	= $url;
		return $this;
	}
}
