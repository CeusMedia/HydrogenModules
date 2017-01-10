<?php
class View_Helper_Input_File{
	static public function render( $name, $label, $required = FALSE, $buttonClass = 'btn-primary' ){
		$input		= UI_HTML_Tag::create( 'input', NULL, array(
			'type'		=> "file",
			'name'		=> $name,
			'class'		=> 'bs-input-file',
			'id'		=> 'input_'.$name,
		) );
		$toggle		= UI_HTML_Tag::create( 'a', $label, array(
			'class'		=> 'btn '.$buttonClass.' bs-input-file-toggle',
			'href'		=> "javascript:;"
		) );
		$info		= UI_HTML_Tag::create( 'input', NULL, array(
			'type'		=> 'text',
			'class'		=> 'span12 bs-input-file-info',
			'required'	=> $required ? 'required' : NULL
		) );
		$upload		= UI_HTML_Tag::create( 'div', $info.$input.$toggle, array(
			'class'		=> 'span12 input-append bs-input-file',
			'style'		=> 'position: relative;'
		) );
		$container	= UI_HTML_Tag::create( 'div', $upload, array(
			'class'		=> 'row-fluid'
		) );
		return $container;
	}
}
?>
