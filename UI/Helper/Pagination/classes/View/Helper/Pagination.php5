<?php
class View_Helper_Pagination{

	/**	@var		object		$env		... */
	protected $env;

	/**	@var		integer		$total		... */
	protected $total;

	/**	@var		integer		$total		... */
	protected $limit;

	/**	@var		integer		$total		... */
	protected $page;

	/**	@var		integer		$total		... */
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
	 *	@todo		implement, remove render attributes, change all calling modules
	 */
/*	public function __construct( $env, $total, $limit, $page, $count ){
		$this->env		= $env;
		$this->total	= $total;
		$this->limit	= $limit;
		$this->page		= $page;
		$this->count	= $count;
	}*/

	public function __construct( $env = NULL, $total = NULL, $limit = NULL, $page = NULL, $count = NULL ){
		$this->env	= $env;
		$this->total	= $total;
		$this->limit	= $limit;
		$this->page		= $page;
		$this->count	= $count;
	}


	/**
	 *	...
	 *	@access		public
	 *	@todo		remove parameters in favour of full construction
	 */
	public function render( $baseUri, $total, $limit, $page, $wrapIntoButtonbar = TRUE ){
		if( $this->env && $this->env->getModules()->has( 'Resource_Library_cmModules' ) ){
			if( $total <= $limit )
				return "";
			$control = new CMM_Bootstrap_PageControl( $baseUri, $page, ceil( $total / $limit ) );
			if( !$wrapIntoButtonbar )
				return $control->render();
			return UI_HTML_Tag::create( 'div', $control->render(), array( 'class' => 'buttonbar' ) );
		}
		return $this->renderOld( $baseUri, $total, $limit, $page );
	}

	protected function renderOld( $baseUri, $number, $limit, $page ){
		$pages		= ceil( $number / $limit );
		if( $pages < 2 )
			return '';
		$list	= array();
		if( $page != 0 ){
			$url	= $baseUri;
			if( $page != 1 )
				$url	= $baseUri.'/'.( $page - 1 );
			$link	= UI_HTML_Tag::create( 'a', '&laquo;', array( 'href' => $url ) );
		}
		else
			$link	= UI_HTML_Tag::create( 'span', '&laquo;' );
		$list[]	= UI_HTML_Tag::create( 'li', $link );
		for( $i=0; $i<$pages; $i++ ){
			if( $page == $i ){
				$link	= UI_HTML_Tag::create( 'span', $i + 1, array( 'class' => 'current' ) );
			}
			else{
				$url	= $baseUri;
				if( $i != 0 )
					$url	= $baseUri.'/'.$i;
				$link	= UI_HTML_Tag::create( 'a', $i + 1, array( 'href' => $url, 'class' => '' ) );
			}
			$list[]	= UI_HTML_Tag::create( 'li', $link );
		}
		if( $page == ( $pages - 1 ) )
			$link	= UI_HTML_Tag::create( 'span', '&raquo;' );
		else{
			$url	= $baseUri.'/'.( $page + 1 );
			$link	= UI_HTML_Tag::create( 'a', '&raquo;', array( 'href' => $url ) );
		}
		$list[]	= UI_HTML_Tag::create( 'li', $link );

		$list	= UI_HTML_Tag::create( 'ul', join( $list ), array( 'class' => 'pagination' ) );
		return $list;
		return UI_HTML_Tag::create( 'div', $list, array( 'class' => 'pagination' ) );
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
	public function renderListNumbers( $total, $limit, $page, $count ){
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
		return UI_HTML_Tag::create( 'small', '('.$label.')', array( 'class' => 'list-numbers muted' ) );
	}

	protected function renderListNumber( $type, $value ){
		return UI_HTML_Tag::create( 'span', $value, array(
			'class'	=> 'list-number-'.$type
		) );
	}
}
?>
