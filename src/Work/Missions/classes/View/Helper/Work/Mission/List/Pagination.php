<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

class View_Helper_Work_Mission_List_Pagination extends Abstraction
{
	public function render( $total, $limit = NULL, $page = 0, $reverse = FALSE ): string
	{
		if( !$total )
			return '';
		$limit			= abs( (int) $limit ) >= 10 ? abs( $limit ) : 10;
		$offset			= abs( (int) $page ) * $limit;
		$attrBtnPrev	= ['type' => 'button', 'class' => 'btn disabled'];
		$attrBtnNext	= ['type' => 'button', 'class' => 'btn disabled'];
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
		$buttonPrev	= HtmlTag::create( 'button', '<', $attrBtnPrev );
		$buttonNext	= HtmlTag::create( 'button', '>', $attrBtnNext );
		$buttonPos	= HtmlTag::create( 'button', 'Seite '.( $page + 1 ).' / '. $pages, array(
			'type'	=> 'button',
			'class'	=> 'btn disabled'
		) );
		$buttons	= HtmlTag::create( 'div', $buttonPrev.$buttonNext.$buttonPos, ['class' => 'btn-group'] );
		return $buttons;
	}
}
