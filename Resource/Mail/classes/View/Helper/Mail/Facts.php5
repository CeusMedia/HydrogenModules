<?php
class View_Helper_Mail_Facts{

	protected $changedFactClassPos  = 'label label-success';
	protected $changedFactClassNeg  = 'label label-important';
	protected $changedFactClassInfo	= 'label label-info';
	protected $facts	= array();
	protected $labels	= array();
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
	}

	public function render( $classList = 'dl-horizontal' ){
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
			$definition	= UI_HTML_Tag::create( 'dd', $value );
			$list[]		= $term.$definition;
		}
		return UI_HTML_Tag::create( 'dl', $list, array( 'class' => $classList ) );
	}

	public function renderAsText(){
		$list	= array();
		foreach( $this->facts as $fact ){
			$label	= trim( strip_tags( $fact->label.':' ) );
			$label	= View_Helper_Mail_Text::fit( $label, 23, STR_PAD_LEFT );
			$value	= View_Helper_Mail_Text::indent( $fact->valueText, 25, 46 );
			$list[]	= $label.'  '.$value;
		}
		return join( "\n", $list );
	}

	public function setLabels( $labels ){
		$this->labels	= $labels;
	}
}
?>

