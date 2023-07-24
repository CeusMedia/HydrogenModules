<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Work_Mission_List_Pagination extends Abstraction
{
	public function __construct( Environment $env )
	{
		$this->setEnv( $env );
	}

	public function render( int $total, ?int $limit = NULL, int $page = 0, bool $reverse = FALSE ): string
	{
		if( !$total )
			return '';
		$limit			= abs( (int) $limit ) >= 10 ? abs( $limit ) : 10;
		$offset			= abs( $page ) * $limit;
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
		$buttonPos	= HtmlTag::create( 'button', 'Seite '.( $page + 1 ).' / '. $pages, [
			'type'	=> 'button',
			'class'	=> 'btn disabled'
		] );
		return HtmlTag::create( 'div', $buttonPrev.$buttonNext.$buttonPos, ['class' => 'btn-group'] );
	}
}
