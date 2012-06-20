<?php
class View_Manage_Issue extends CMF_Hydrogen_View{
	public function add(){
	}
	public function edit(){
	}
	public function index(){
	}

	protected function renderOptions( $options, $key, $values, $class = '' ){
		$list		= array();
		if( !is_array( $values ) )
			$values = $values ? array( $values ) : array();
		foreach( $options as $key => $value ){
			$selected	= !strlen( $key ) && !$values;
			if( strlen( $key ) )
				$selected	= in_array( $key, $values );
			$attributes	= array(
				'value'		=> $key,
				'class'		=> strlen( $key ) ? sprintf( $class, $key ) : '',
				'selected'	=>  $selected ? 'selected' : NULL,
			);
			$list[]	= UI_HTML_Tag::create( 'option', $value, $attributes );
		}
		return join( $list );
	}	
}
?>
