<?php

use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Pagination
{
	/**	@var		object		$env		... */
	protected $env;

	/**	@var		integer		$total		... */
	protected $total;

	/**	@var		integer		$limit		... */
	protected $limit;

	/**	@var		integer		$page		... */
	protected $page;

	/**	@var		integer		$count		... */
	protected $count;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		object		$env		...
	 *	@param		integer		$total		...
	 *	@param		integer		$limit		...
	 *	@param		integer		$page		...
	 *	@param		integer		$count		...
	 *	@return		void
	 */
	public function __construct( Environment $env = NULL, $total = NULL, $limit = NULL, $page = NULL, $count = NULL )
	{
		$this->env		= $env;
		$this->total	= $total;
		$this->limit	= $limit;
		$this->page		= $page;
		$this->count	= $count;
	}

	/**
	 *	...
	 *	@access		public
	 *	@todo		remove parameters in favour of full construction
	 *	@todo		replace module check against composer package check
	 */
	public function render( string $baseUri, int $total, int $limit, int $page, bool $wrapIntoButtonbar = TRUE ): string
	{
		if( $this->env && $this->env->getModules()->has( 'Resource_Library_cmModules' ) ){
			if( $total <= $limit )
				return "";
			$control = new PageControl( $baseUri, $page, ceil( $total / $limit ) );
			if( !$wrapIntoButtonbar )
				return $control->render();
			return HtmlTag::create( 'div', $control->render(), ['class' => 'buttonbar'] );
		}
		return $this->renderOld( $baseUri, $total, $limit, $page );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		integer		$total		...
	 *	@param		integer		$limit		...
	 *	@param		integer		$page		...
	 *	@param		integer		$count		...
	 *	@return		string					...
	 */
	public function renderListNumbers( int $total, int $limit, int $page, int $count ): string
	{
		$label	= $count;
		if( $total > $limit ){
			$spanTotal	= $this->renderListNumber( 'total', $total );
			$spanRange	= $this->renderListNumber( 'range', $page * $limit + 1 );
			if( $count > 1 ){
				$spanFrom	= $this->renderListNumber( 'from', $page * $limit + 1 );
				$spanTo		= $this->renderListNumber( 'to', $page * $limit + $count );
				$spanRange	= $this->renderListNumber( 'range', $spanFrom.'&minus;'.$spanTo );
			}
			$label	= $spanRange.' / '.$spanTotal;
		}
		return HtmlTag::create( 'small', '('.$label.')', ['class' => 'list-numbers muted'] );
	}

	protected function renderOld( string $baseUri, $number, $limit, $page ): string
	{
		$pages		= ceil( $number / $limit );
		if( $pages < 2 )
			return '';
		$list	= [];
		if( $page != 0 ){
			$url	= $baseUri;
			if( $page != 1 )
				$url	= $baseUri.'/'.( $page - 1 );
			$link	= HtmlTag::create( 'a', '&laquo;', ['href' => $url] );
		}
		else
			$link	= HtmlTag::create( 'span', '&laquo;' );
		$list[]	= HtmlTag::create( 'li', $link );
		for( $i=0; $i<$pages; $i++ ){
			if( $page == $i ){
				$link	= HtmlTag::create( 'span', $i + 1, ['class' => 'current'] );
			}
			else{
				$url	= $baseUri;
				if( $i != 0 )
					$url	= $baseUri.'/'.$i;
				$link	= HtmlTag::create( 'a', $i + 1, ['href' => $url, 'class' => ''] );
			}
			$list[]	= HtmlTag::create( 'li', $link );
		}
		if( $page == ( $pages - 1 ) )
			$link	= HtmlTag::create( 'span', '&raquo;' );
		else{
			$url	= $baseUri.'/'.( $page + 1 );
			$link	= HtmlTag::create( 'a', '&raquo;', ['href' => $url] );
		}
		$list[]	= HtmlTag::create( 'li', $link );

		$list	= HtmlTag::create( 'ul', join( $list ), ['class' => 'pagination'] );
		return $list;
		return HtmlTag::create( 'div', $list, ['class' => 'pagination'] );
	}

	protected function renderListNumber( string $type, $value ): string
	{
		return HtmlTag::create( 'span', $value, [
			'class'	=> 'list-number-'.$type
		] );
	}
}
