<?php
class View_Helper_Form_Fill_Data{

	protected $fill;
	protected $form;

	public function __construct( $env ){
		$this->env		= $env;
	}

	public function render(){
		if( !$this->fill )
			throw new DomainException( 'No fill given' );
		if( !$this->form )
			throw new DomainException( 'No form given' );
		$inputs		= json_decode( $this->fill->data, TRUE );

		foreach( $inputs as $name => $input ){
			if( in_array( trim( $name ), array( 'gender', 'firstname', 'surname', 'email', 'phone', 'street', 'city', 'postcode', 'country' ) ) ){
				if( $input['label'] ){
					unset( $inputs[$name] );
				}
			}
		}

		$checkValues	= array( 'true', 'ja', 'yes' );
		$listInfo		= array();
//print_m( $inputs );die;
		foreach( $inputs as $name => $input ){
			$value	= $input['value'];

			if( $input['type'] == 'date' && strlen( $value ) )
				$value	= date( 'd.m.Y', strtotime( $value ) );
			else if( $input['type'] == 'check' )
				$value	= in_array( $input['value'], $checkValues ) ? "ja" : "nein";
			else if( in_array( $input['type'], array( 'select', 'choice' ) ) )
				$value	= $input['valueLabel'];
			else if( $input['type'] == 'radio' && strlen( $value ) )
				$value	= $input['valueLabel'].'<br/><tt>('.$input['value'].')</tt>';

			if( !strlen( $value ) )
				$value		= '<em class="muted">keine Angabe</em>';
			$listInfo[]	= (object) array( 'label' => $input['label'], 'value' => $value );
			unset( $inputs[$name] );
		}

		$dataInfo		= '';
		if( $listInfo ){
			$dataInfo	= UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'h3', 'Angaben' ),
				$this->renderTable( $listInfo, TRUE ),
			) );
		}
		return $dataInfo;
	}

	protected function renderTable( $rows ){
		$list	= array();
		foreach( $rows as $row ){
			$list[]	= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'th', $row->label ),
				UI_HTML_Tag::create( 'td', $row->value ),
			) );
		}
		return UI_HTML_Tag::create( 'table', array(
			UI_HTML_Elements::ColumnGroup( array( '50%', '50%' ) ),
			UI_HTML_Tag::create( 'tbody', $list ),
		), array( 'class' => 'table table-striped table-fixed table-bordered' ) );
	}

	public function setFill( $fill ){
		$this->fill		= $fill;
	}

	public function setForm( $form ){
		$this->form		= $form;
	}
}

