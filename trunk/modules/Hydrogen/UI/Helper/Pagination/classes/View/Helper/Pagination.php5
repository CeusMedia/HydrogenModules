<?php
class View_Helper_Pagination{	

	public function render( $baseUri, $number, $limit, $page = 0){
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
	}
}
?>