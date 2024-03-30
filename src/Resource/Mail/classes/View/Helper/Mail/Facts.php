<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Mail_Facts
{
	protected string $changedFactClassPos  = 'label label-success';
	protected string $changedFactClassNeg  = 'label label-important';
	protected string $changedFactClassInfo	= 'label label-info';
	protected array $facts					= [];
	protected array $labels					= [];
	protected int $format					= 0;
	protected string $listClass				= 'dl-horizontal';
	protected int $textLabelLength			= 23;

	public const FORMAT_HTML				= 0;
	public const FORMAT_TEXT				= 1;

	public const FORMATS					= [
		self::FORMAT_HTML,
		self::FORMAT_TEXT,
	];

	/**
	 *	@param		string			$keyOrLabel
	 *	@param		string			$valueAsHtml
	 *	@param		string|NULL		$valueAsText
	 *	@param		$direction
	 *	@return		self
	 */
	public function add( string $keyOrLabel, string $valueAsHtml, ?string $valueAsText = NULL, $direction = NULL ): self
	{
		$key	= $label	= $keyOrLabel;
		$valueAsText	= $valueAsText ?? strip_tags( $valueAsHtml );
		if( !empty( $this->labels[$key] ) )
			$label	= $this->labels[$key];
		if( !empty( $this->labels['label'.ucFirst( $key )] ) )
			$label	= $this->labels['label'.ucFirst( $key )];
		$this->facts[]	= (object) [
			'key'		=> $key,
			'label'		=> $label,
			'valueHtml'	=> $valueAsHtml,
			'valueText'	=> $valueAsText,
			'direction'	=> $direction,
		];
		return $this;
	}

	/**
	 *	@return		string
	 */
	public function render(): string
	{
		if( count( $this->facts ) ) {
			if ($this->format === self::FORMAT_HTML)
				return $this->renderAsHtml();
			if ($this->format === self::FORMAT_TEXT)
				return $this->renderAsText();
		}
		return '';
	}

	/**
	 *	@param		int		$format
	 *	@return		self
	 */
	public function setFormat( int $format ): self
	{
		if( !in_array( $format, self::FORMATS, TRUE ) )
			throw new RangeException( 'Invalid helper output format' );
		$this->format	= $format;
		return $this;
	}

	/**
	 *	@param		array		$labels
	 *	@return		self
	 */
	public function setLabels( array $labels ): self
	{
		$this->labels	= $labels;
		return $this;
	}

	/**
	 *	@param		string		$listClass
	 *	@return		self
	 */
	public function setListClass( string $listClass ): self
	{
		$this->listClass	= $listClass;
		return $this;
	}

	/**
	 *	@param		int		$integer
	 *	@return		self
	 */
	public function setTextLabelLength( int $integer ): self
	{
		$this->textLabelLength	= max( 0, min( $integer, 36 ) );
		return $this;
	}

	//  --  PROTECTED  --  //

/*	protected function __onInit(): void
	{
		$this->helperText	= new View_Helper_Mail_Text( $this->env );
	}*/

	/**
	 *	@return		string
	 */
	protected function renderAsHtml(): string
	{
		$list	= [];
		foreach( $this->facts as $fact ){
			$value	= $fact->valueHtml;
			if( !is_null( $fact->direction ) ){
				$class	= $this->changedFactClassInfo;
				if( $fact->direction === TRUE || $fact->direction === 1 )
					$class	= $this->changedFactClassPos;
				else if( $fact->direction === FALSE || $fact->direction === -1 )
					$class	= $this->changedFactClassNeg;
				$value	= HtmlTag::create( 'span', $fact->valueHtml, [
					'class'	=> $class
				] );
			}
			$term		= HtmlTag::create( 'dt', $fact->label );
			$definition	= HtmlTag::create( 'dd', $value.'&nbsp;' );
			$list[]		= $term.$definition;
		}
		return HtmlTag::create( 'dl', $list, ['class' => $this->listClass] );
	}

	/**
	 *	@return		string
	 */
	protected function renderAsText(): string
	{
		$list	= [];
		foreach( $this->facts as $fact ){
			$label	= trim( strip_tags( $fact->label.':' ) );
			$label	= View_Helper_Mail_Text::fit( $label, $this->textLabelLength, STR_PAD_LEFT );
			$value	= View_Helper_Mail_Text::indent(
				$fact->valueText,
				$this->textLabelLength + 2,
				76 - $this->textLabelLength - 2
			);
			$list[]	= $label.'  '.$value;
		}
		return join( "\n", $list );
	}
}
