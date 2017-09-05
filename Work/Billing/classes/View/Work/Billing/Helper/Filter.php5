<?php
class View_Work_Billing_Helper_Filter{

	protected $filters			= array();
	protected $session;
	protected $sessionPrefix;
	protected $url;

	public function __construct( $env ){
		$this->env		= $env;
		$this->session	= $this->env->getSession();
	}

	public function __toString(){
		return $this->render();
	}

	public function setFilters( $filters ){
		$this->filters	= $filters;
	}

	public function setSessionPrefix( $sessionPrefix ){
		$this->sessionPrefix	= $sessionPrefix;
	}

	public function setUrl( $url ){
		$this->url	= $url;
	}

	public function render(){
		if( !$this->filters )
			throw new RuntimeException( 'No filter columns set' );
		if( !$this->url )
			throw new RuntimeException( 'No filter URL set' );
		if( !$this->sessionPrefix )
			throw new RuntimeException( 'No filter session prefix set' );

		$formFields		= array();
		foreach( $this->filters as $filter ){
			$value		= $this->session->get( $this->sessionPrefix.$filter );
			switch( $filter ){
				case 'year':
					$optYear	= array(
						''	=> '- alle -',
					);
					$optYear[date( "Y" )]	= date( "Y" );
					$optYear[date( "Y" )-1]	= date( "Y" )-1;
					$optYear[date( "Y" )-2]	= date( "Y" )-2;
					$optYear	= UI_HTML_Elements::Options( $optYear, $value );
					$formFields[]	= '
					<div class="span2">
						<label for="input_year">Jahr</label>
						<select name="year" id="input_year" class="span12" onchange="this.form.submit()">'.$optYear.'</select>
					</div>';
					break;
				case 'month':
					$optMonth	= array(
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
					);
					$optMonth	= UI_HTML_Elements::Options( $optMonth, $value );
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
}
