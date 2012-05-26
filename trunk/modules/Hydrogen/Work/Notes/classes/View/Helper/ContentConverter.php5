<?php
class View_Helper_ContentConverter{

	protected $a	= array();
	protected $b	= array();

	protected function replaceCodeBlocks( $m ){
	#	print_m( $m );
		$this->a[]	= array(
			'type'		=> trim( $m[2] ),
			'content'	=> trim( $m[3] )
		);
		return "###a:".( count( $this->a ) - 1 )."###";
	}

	public function convertLists( $content ){
		$lines	= explode( "\n", $content );
		$list	= array();
		$state	= 0;
		$index	= -1;
		foreach( $lines as $line ){
			$cut	= substr( $line, 0, 2 );
			if( $cut == '- ' ){
				if( !$state ){
					$index++;
					$this->b[$index]	= array( 'type' => 'ul', 'lines' => array() );
					$state	= 1;
				}
				$this->b[$index]['lines'][]	= substr( $line, 2 );
				continue;
			}
			else if( $cut == '# ' ){
				if( !$state ){
					$index++;
					$this->b[$index]	= array( 'type' => 'ol', 'lines' => array() );
					$state	= 1;
				}
				$this->b[$index]['lines'][]	= substr( $line, 2 );
				continue;
			}
			else if( $state ){
				$state	= 0;
				$list[]	= '###b:'.$index.'###';
			}
			$list[]	= trim( $line );
		}
		if( $state )
			$list[]	= '###b:'.$index.'###';
		return implode( "\n", $list );
	}

	public function convert( $content ){
		$lines		= explode( "\n", $content );
		$pattern	= "/(\r?\n)+code:?(\w+)?>(.*)<code(\r?\n)+/siU";
		$callback	= array( $this, 'replaceCodeBlocks' );
		$content	= preg_replace_callback( $pattern, $callback, $content );
		$content	= $this->convertLists( $content );
		$content	= str_replace( "\n\n", "\n", $content );
		$lines		= explode( "\n", $content );
		$list		= array();
		foreach( $lines as $line )
			if( trim( $line ) )
				$list[]	= $line;
		$content	= '<p>'.implode( '</p><p>', $list ).'</p>';
#		$content	= nl2br( $content );
#		print_m( $this->a );
		foreach( $this->a as $nr => $aa ){
			$attributes	= array( 'class' => $aa['type'] ? $aa['type'] : 'code' );
			$new		= UI_HTML_Tag::create( 'xmp', $aa['content'], $attributes );
			$content	= preg_replace( '/###a:'.$nr.'###/', $new, $content );
		}
		foreach( $this->b as $nr => $bb ){
			$list	= array();
			foreach( $bb['lines'] as $line )
				$list[]	= UI_HTML_Tag::create( 'li', $line );
			if( $bb['type'] == 'ol' )
				$list	= UI_HTML_Tag::create( 'ol', join( $list ) );
			if( $bb['type'] == 'ul' )
				$list	= UI_HTML_Tag::create( 'ul', join( $list ) );
			$content	= preg_replace( '/###b:'.$nr.'###/', $list, $content );
		}
		return $content;
	}
}
?>