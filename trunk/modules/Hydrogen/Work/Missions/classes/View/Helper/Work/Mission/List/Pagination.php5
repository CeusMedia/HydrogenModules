<?php
class View_Helper_Work_Mission_List_Pagination extends CMF_Hydrogen_View_Helper_Abstract{

	public function render( $total, $limit = NULL, $page = 0, $reverse = FALSE ){
		if( !$total )
			return "";
		$limit			= abs( (int) $limit ) >= 10 ? abs( $limit ) : 10;
		$offset			= abs( (int) $page ) * $limit;
		$attrBtnPrev	= array( 'type' => 'button', 'class' => 'btn disabled' );
		$attrBtnNext	= array( 'type' => 'button', 'class' => 'btn disabled' );
		$pages			= ceil( $total / $limit );
		$page		= $reverse ? $pages - $page - 1 : $page;
		if( $page ){
			$prevPage	= $reverse ? $page + 1 : $page - 1;
			$attrBtnPrev['onclick']	= 'WorkMissionsList.setPage('.$prevPage.')';
			$attrBtnPrev['class']	= 'btn';
		}
		if( $total > ( $offset + $limit ) ){
			$nextPage	= $reverse ? $page - 1 : $page + 1;
			$attrBtnNext['onclick']	= 'WorkMissionsList.setPage('.$nextPage.')';
			$attrBtnNext['class']	= 'btn';
		}
		$buttonPrev	= UI_HTML_Tag::create( 'button', '<', $attrBtnPrev );
		$buttonNext	= UI_HTML_Tag::create( 'button', '>', $attrBtnNext );
		$buttonPos	= UI_HTML_Tag::create( 'button', 'Seite '.( $page + 1 ).' / '. $pages, array(
			'type'	=> 'button',
			'class'	=> 'btn disabled'
		) );
		$buttons	= UI_HTML_Tag::create( 'div', $buttonPrev.$buttonNext.$buttonPos, array( 'class' => 'btn-group' ) );
		return $buttons;
	}
}
?>
