<?php
class View_Helper_Mail_Facts{

	protected $changedFactClassPos  = 'label label-success';
	protected $changedFactClassNeg  = 'label label-important';
	protected $changedFactClassInfo	= 'label label-info';
	protected $facts				= array();
	protected $labels				= array();
	protected $format				= 0;
	protected $listClass			= 'dl-horizontal';
	protected $textLabelLength		= 23;

	const FORMAT_HTML				= 0;
	const FORMAT_TEXT				= 1;
/*
	public function __onInit(){
		$this->helperText	= new View_Helper_Mail_Text( $this->env );
	}*/

	public function add( $keyOrLabel, $valueAsHtml, $valueAsText = NULL, $direction = NULL ){
		$key	= $label	= $keyOrLabel;
		$valueAsText	= $valueAsText !== NULL ? $valueAsText : strip_tags( $valueAsHtml );
		if( !empty( $this->labels[$key] ) )
			$label	= $this->labels[$key];
		if( !empty( $this->labels['label'.ucFirst( $key )] ) )
			$label	= $this->labels['label'.ucFirst( $key )];
		$this->facts[]	= (object) array(
			'key'		=> $key,
			'label'		=> $label,
			'valueHtml'	=> $valueAsHtml,
			'valueText'	=> $valueAsText,
			'direction'	=> $direction,
		);
		return $this;
	}

	public function render(){
		if( !count( $this->facts ) )
			return '';
		if( $this->format == self::FORMAT_HTML )
			return $this->renderAsHtml();
		if( $this->format == self::FORMAT_TEXT )
			return $this->renderAsText();
	}

	protected function renderAsHtml(){
		$list	= array();
		foreach( $this->facts as $fact ){
			$value	= $fact->valueHtml;
			if( !is_null( $fact->direction ) ){
				$class	= $this->changedFactClassInfo;
				if( $fact->direction === TRUE || $fact->direction === 1 )
					$class	= $this->changedFactClassPos;
				else if( $fact->direction === FALSE || $fact->direction === -1 )
					$class	= $this->changedFactClassNeg;
				$value	= UI_HTML_Tag::create( 'span', $fact->valueHtml, array(
					'class'	=> $class
				) );
			}
			$term		= UI_HTML_Tag::create( 'dt', $fact->label );
			$definition	= UI_HTML_Tag::create( 'dd', $value.'&nbsp;' );
			$list[]		= $term.$definition;
		}
		return UI_HTML_Tag::create( 'dl', $list, array( 'class' => $this->listClass ) );
	}

	public function renderAsText(){
		$list	= array();
		foreach( $this->facts as $fact ){
			$label	= trim( strip_tags( $fact->label.':' ) );
			$label	= View_Helper_Mail_Text::fit( $label, $this->textLabelLength, STR_PAD_LEFT );
			$value	= View_Helper_Mail_Text::indent( $fact->valueText, $this->textLabelLength + 2, 76 - $this->textLabelLength - 2 );
			$list[]	= $label.'  '.$value;
		}
		return join( "\n", $list );
	}

	public function setFormat( $format ){
		if( !in_array( $format, array( self::FORMAT_HTML, self::FORMAT_TEXT ) ) )
			throw new RangeException( 'Invalid helper output format' );
		$this->format	= $format;
		return $this;
	}

	public function setLabels( $labels ){
		$this->labels	= $labels;
		return $this;
	}

	public function setListClass( $listClass ){
		$this->listClass	= $listClass;
		return $this;
	}

	public function setTextLabelLength( $integer ){
		$this->textLabelLength	= max( 0, min( $integer, 36 ) );
		return $this;
	}
}
?>
