<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Form_Fill_Person{

	protected $fill;

	protected $form;

	protected $fields		= array(
		'gender',
		'firstname',
		'surname',
		'email',
		'phone',
		'street',
		'city',
		'postcode',
		'country'
	);

	protected $heading		= 'Person';

	public function __construct( $env ){
		$this->env		= $env;
	}

	public function addFields( $fields ){
		if( !is_array( $fields ) )
			throw new InvalidArgumentException( 'Fields must be an array' );
		$this->fields	= array_merge( $this->fields, $fields );
	}

	public function getFields(){
		return $this->fields;
	}

	public function getHeading(){
		return $this->heading;
	}

	public function removeFields( $fields ){
		if( !is_array( $fields ) )
			throw new InvalidArgumentException( 'Fields must be an array' );
		$this->fields	= array_diff( $this->fields, $fields );
	}

	public function render(){
		if( !$this->fill )
			throw new DomainException( 'No fill given' );
//		if( !$this->form )
//			throw new DomainException( 'No form given' );
		$inputs		= json_decode( $this->fill->data, TRUE );

		$checkValues	= ['true', 'ja', 'yes'];
		$listInfo		= [];
		foreach( $inputs as $name => $input ){
			if( in_array( trim( $name ), $this->fields ) ){
				$value	= $input['value'];
				if( $input['type'] == 'date' && strlen( $value ) )
					$value	= date( 'd.m.Y', strtotime( $value ) );
				else if( $input['type'] == 'check' )
					$value	= in_array( $input['value'], $checkValues ) ? "ja" : "nein";
				else if( in_array( $input['type'], ['select', 'choice'] ) )
					$value	= $input['valueLabel'];
				else if( $input['type'] == 'radio' && strlen( $value ) )
					$value	= $input['valueLabel'].'<br/><tt>('.$input['value'].')</tt>';

				if( !strlen( $value ) )
					$value		= '<em class="muted">keine Angabe</em>';
				$listInfo[]	= (object) ['label' => $input['label'], 'value' => $value];
				unset( $inputs[$name] );
			}
		}

		$dataPerson		= '';
		if( $listInfo ){
			$dataPerson	= HtmlTag::create( 'div', array(
				HtmlTag::create( 'h3', 'Person' ),
				$this->renderTable( $listInfo, TRUE ),
			) );
		}
		return $dataPerson;
	}

	protected function renderFacts( $facts, $horizontal = FALSE ){
		$list	= [];
		foreach( $facts as $label => $value ){
			$list[]	= HtmlTag::create( 'dt', $label );
			$list[]	= HtmlTag::create( 'dd', $value.'&nbsp;' );
		}
		if( $list )
			return HtmlTag::create( 'dl', $list, ['class' => $horizontal ? 'dl-horizontal' : NULL] );
	}

	protected function renderTable( $rows ){
		$list	= [];
		foreach( $rows as $row ){
			$list[]	= HtmlTag::create( 'tr', array(
				HtmlTag::create( 'th', $row->label ),
				HtmlTag::create( 'td', $row->value ),
			) );
		}
		return HtmlTag::create( 'table', array(
			HtmlElements::ColumnGroup( ['50%', '50%'] ),
			HtmlTag::create( 'tbody', $list ),
		), ['class' => 'table table-striped table-fixed table-bordered table-condensed'] );
	}

	public function setFields( $fields ){
		if( !is_array( $fields ) )
			throw new InvalidArgumentException( 'Fields must be an array' );
		$this->fields	= $fields;
	}

	public function setFill( $fill ){
		if( !is_object( $fill ) )
			throw new InvalidArgumentException( 'Fill must be an object' );
		$this->fill		= $fill;
	}

	public function setForm( $form ){
		if( !is_object( $form ) )
			throw new InvalidArgumentException( 'Form must be an object' );
		$this->form		= $form;
	}
}
