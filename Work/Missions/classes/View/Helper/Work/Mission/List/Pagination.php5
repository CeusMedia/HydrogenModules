<?php
class View_Helper_Work_Mission_List_Pagination extends CMF_Hydrogen_View_Helper_Abstract{

	public function render( $total, $limit = NULL, $page = 0 ){
		$limit			= abs( (int) $limit ) >= 10 ? abs( $limit ) : 10;
		$offset			= abs( (int) $page ) * $limit;
		$attrBtnPrev	= array( 'type' => 'button', 'class' => 'btn disabled' );
		$attrBtnNext	= array( 'type' => 'button', 'class' => 'btn disabled' );
		if( $total > ( $offset + $limit ) ){
			$attrBtnPrev['onclick']	= 'WorkMissionsList.setPage('.( $page + 1 ).')';
			$attrBtnPrev['class']	= 'btn';
		}
		if( $page ){
			$attrBtnNext['onclick']	= 'WorkMissionsList.setPage('.( $page - 1 ).')';
			$attrBtnNext['class']	= 'btn';
		}
		$pages		= ceil( $total / $limit );
		$buttonPrev	= UI_HTML_Tag::create( 'button', '<', $attrBtnPrev );
		$buttonNext	= UI_HTML_Tag::create( 'button', '>', $attrBtnNext );
		$buttonPos	= UI_HTML_Tag::create( 'button', 'Seite '.( $pages - $page ).' / '. $pages, array(
			'type'	=> 'button',
			'class'	=> 'btn disabled'
		) );
		$buttons	= UI_HTML_Tag::create( 'div', $buttonPrev.$buttonNext.$buttonPos, array( 'class' => 'btn-group' ) );
		return $buttons;
	}
}
?>
