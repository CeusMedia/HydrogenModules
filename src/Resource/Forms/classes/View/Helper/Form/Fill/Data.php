<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
class View_Helper_Form_Fill_Data
{
	protected Environment $env;
	protected object $fill;
	protected string $form;
	protected int $mode;

	public const MODE_NORMAL		= 0;
	public const MODE_EXTENDED		= 1;

	public function __construct( Environment $env )
	{
		$this->env		= $env;
	}

	public function render(): string
	{
		if( !$this->fill )
			throw new DomainException( 'No fill given' );
		if( !$this->form )
			throw new DomainException( 'No form given' );
		$inputs		= json_decode( $this->fill->data, TRUE );

		foreach( $inputs as $name => $input ){
			if( in_array( trim( $name ), ['gender', 'firstname', 'surname', 'email', 'phone', 'street', 'city', 'postcode', 'country'] ) ){
				if( $input['label'] ){
					unset( $inputs[$name] );
				}
			}
		}

		$checkValues	= ['true', 'ja', 'yes'];
		$listInfo		= [];
//print_m( $inputs );die;
		foreach( $inputs as $name => $input ){
			$value	= $input['value'];
			$text	= $input['text'] ?? '';

			if( $input['type'] == 'date' && strlen( $value ) )
				$value	= date( 'd.m.Y', strtotime( $value ) );
			else if( $input['type'] == 'check' )
				$value	= in_array( $input['value'], $checkValues ) ? "ja" : "nein";
			else if( in_array( $input['type'], ['select', 'choice'] ) ){
				$value	= $input['valueLabel'];
				if( $this->mode === self::MODE_EXTENDED && $input['valueLabel'] !== $input['value'] )
					$value .= ' <small><tt>('.$input['value'].')</tt></small>';
			}
			else if( $input['type'] == 'radio' && strlen( $value ) ){
				$value	= $input['valueLabel'];
				if( $this->mode === self::MODE_EXTENDED )
					$value .= '<br/><tt>('.$input['value'].')</tt>';
			}

			if( !strlen( $value ) )
				$value		= '<em class="muted">keine Angabe</em>';
			else if( preg_match( '/iban/i', $name ) )
				$value = join( ' ', str_split( $value, 4 ) );

			$listInfo[]	= (object) ['label' => $input['label'], 'value' => $value, 'text' => $text];
			unset( $inputs[$name] );
		}

		$dataInfo		= '';
		if( $listInfo ){
			$dataInfo	= HtmlTag::create( 'div', array(
				HtmlTag::create( 'h3', 'Angaben' ),
				$this->renderTable( $listInfo ),
			) );
		}
		return $dataInfo;
	}

	protected function renderTable( $rows ): string
	{
		$list	= [];
		foreach( $rows as $row ){
			$text	= '';
			if( !empty( $row->text ) )
				$text	= '<br/>'.HtmlTag::create( 'small', $row->text, ['class' => 'muted'] );
			$list[]	= HtmlTag::create( 'tr', array(
				HtmlTag::create( 'th', $row->label.$text ),
				HtmlTag::create( 'td', $row->value ),
			) );
		}
		return HtmlTag::create( 'table', array(
			HtmlElements::ColumnGroup( ['50%', '50%'] ),
			HtmlTag::create( 'tbody', $list ),
		), ['class' => 'table table-striped table-fixed table-bordered table-condensed'] );
	}

	public function setFill( object $fill ): self
	{
		$this->fill		= $fill;
		return $this;	}

	public function setForm( string $form ): self
	{
		$this->form		= $form;
		return $this;
	}

	public function setMode( int $mode ): self
	{
		$this->mode = $mode;
		return $this;
	}
}

