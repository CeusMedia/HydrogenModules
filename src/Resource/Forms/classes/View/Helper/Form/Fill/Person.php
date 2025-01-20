<?php
declare(strict_types=1);

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Form_Fill_Person
{
	protected Environment $env;

	protected ?Entity_Form_Fill $fill	= NULL;

	protected ?Entity_Form $form		= NULL;

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

	protected string $heading			= 'Person';

	public function __construct( Environment $env )
	{
		$this->env		= $env;
	}

	/**
	 *	@param		array<string>		$fields
	 *	@return		static
	 */
	public function addFields( array $fields ): static
	{
		$this->fields	= array_merge( $this->fields, $fields );
		return $this;
	}

	/**
	 *	@return		array<string>
	 */
	public function getFields(): array
	{
		return $this->fields;
	}

	/**
	 *	@return		string
	 */
	public function getHeading(): string
	{
		return $this->heading;
	}

	/**
	 *	@param		array<string>		$fields
	 *	@return		static
	 */
	public function removeFields( array $fields ): static
	{
		$this->fields	= array_diff( $this->fields, $fields );
		return $this;
	}

	/**
	 *	@return		string
	 */
	public function render(): string
	{
		if( NULL === $this->fill )
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
			$dataPerson	= HtmlTag::create( 'div', [
				HtmlTag::create( 'h3', 'Person' ),
				$this->renderTable( $listInfo ),
			] );
		}
		return $dataPerson;
	}

	/**
	 *	@param		array<string>		$fields
	 *	@return		static
	 */
	public function setFields( array $fields ): static
	{
		$this->fields	= $fields;
		return $this;
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
	 *	@param		array<string,string>	$facts
	 *	@param		bool					$horizontal
	 *	@return		string
	 */
	protected function renderFacts( array $facts, bool $horizontal = FALSE ): string
	{
		$list	= [];
		foreach( $facts as $label => $value ){
			$list[]	= HtmlTag::create( 'dt', $label );
			$list[]	= HtmlTag::create( 'dd', $value.'&nbsp;' );
		}
		if( $list )
			return HtmlTag::create( 'dl', $list, ['class' => $horizontal ? 'dl-horizontal' : NULL] );
		return '';
	}

	/**
	 *	@param		array<object>	$rows
	 *	@return		string
	 */
	protected function renderTable( array $rows ): string
	{
		$list	= [];
		foreach( $rows as $row ){
			$list[]	= HtmlTag::create( 'tr', [
				HtmlTag::create( 'th', $row->label ),
				HtmlTag::create( 'td', $row->value ),
			] );
		}
		return HtmlTag::create( 'table', [
			HtmlElements::ColumnGroup( ['50%', '50%'] ),
			HtmlTag::create( 'tbody', $list ),
		], ['class' => 'table table-striped table-fixed table-bordered table-condensed'] );
	}
}
