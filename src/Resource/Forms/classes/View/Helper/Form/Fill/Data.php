<?php
declare(strict_types=1);

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Form_Fill_Data
{
	public const MODE_NORMAL			= 0;
	public const MODE_EXTENDED			= 1;

	protected Environment $env;
	protected ?Entity_Form_Fill $fill	= NULL;
	protected ?Entity_Form $form		= NULL;
	protected int $mode					= 0;
	protected array $fields				= [
		'gender',
		'firstname',
		'surname',
		'email',
		'phone',
		'street',
		'city',
		'postcode',
		'country'
	];


	public function __construct( Environment $env )
	{
		$this->env		= $env;
	}

	/**
	 *	@return		string
	 */
	public function render(): string
	{
		if( NULL === $this->fill )
			throw new DomainException( 'No fill given' );
		if( NULL === $this->form )
			throw new DomainException( 'No form given' );
		$inputs		= json_decode( $this->fill->data, TRUE );

		foreach( $inputs as $name => $input ){
			if( in_array( trim( $name ), $this->fields ) ){
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
				if( self::MODE_EXTENDED === $this->mode && $input['valueLabel'] !== $input['value'] )
					$value .= ' <small><tt>('.$input['value'].')</tt></small>';
			}
			else if( $input['type'] == 'radio' && strlen( $value ) ){
				$value	= $input['valueLabel'];
				if( self::MODE_EXTENDED === $this->mode )
					$value .= '<br/><tt>('.$input['value'].')</tt>';
			}

			if( !strlen( $value ) )
				$value		= '<em class="muted">keine Angabe</em>';
			else if( preg_match( '/iban/i', $name ) )
				$value = join( ' ', str_split( $value, 4 ) );

			$listInfo[]	= (object) ['label' => $input['label'], 'value' => $value, 'text' => $text];
			unset( $inputs[$name] );
		}
		if( [] === $listInfo )
			return '';
		return HtmlTag::create( 'div', [
			HtmlTag::create( 'h3', 'Angaben' ),
			$this->renderTable( $listInfo ),
		] );
	}

	/**
	 *	@param		Entity_Form_Fill		$fill
	 *	@return		static
	 */
	public function setFill( Entity_Form_Fill $fill ): static
	{
		$this->fill		= $fill;
		return $this;
	}

	/**
	 *	@param		Entity_Form		$form
	 *	@return		static
	 */
	public function setForm( Entity_Form $form ): static
	{
		$this->form		= $form;
		return $this;
	}

	/**
	 *	@param		int		$mode
	 *	@return		static
	 */
	public function setMode( int $mode ): static
	{
		$this->mode = $mode;
		return $this;
	}

	/**
	 *	@param		array<object>		$rows
	 *	@return		string
	 */
	protected function renderTable( array $rows ): string
	{
		$list	= [];
		foreach( $rows as $row ){
			$text	= '';
			if( !empty( $row->text ) )
				$text	= '<br/>'.HtmlTag::create( 'small', $row->text, ['class' => 'muted'] );
			$list[]	= HtmlTag::create( 'tr', [
				HtmlTag::create( 'th', $row->label.$text ),
				HtmlTag::create( 'td', $row->value ),
			] );
		}
		return HtmlTag::create( 'table', [
			HtmlElements::ColumnGroup( ['50%', '50%'] ),
			HtmlTag::create( 'tbody', $list ),
		], ['class' => 'table table-striped table-fixed table-bordered table-condensed'] );
	}
}

