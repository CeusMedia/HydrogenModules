<?php
class View_Helper_Text
{
	public static function applyFormat( string $text ): string
	{
		$text	= preg_replace( '/(_{4,})/', '<hr/>', $text );
		$text	= preg_replace( '/\/\/(.+)\/\//U', '<em>\\1</em>', $text );
		$text	= preg_replace( '/\*\*(.+)\*\*/U', '<b>\\1</b>', $text );
		return $text;
	}

	/**
	 *	Builds internal Links.
	 *	@static
	 *	@access		public
	 *	@param		string		$content		Content to be realized with internal Links
	 *	@return		string
	 */
	static public function applyLinks( string $content ): string
	{
		$content	= preg_replace( "@\[article:([^\|]+)\|([^\]]+)\]@i", "<a href='article.html;article_id,\\1'>\\2</a>", $content );
		$content	= preg_replace( "@\[category:([^\|]+)\|([^\]]+)\]@i", "<a href='article.html;categoryId,\\1'>\\2</a>", $content );
		$content	= preg_replace( "@\[link:(http://)?([^\|]+)\|([^\]]+)\]@i", "<a href='https://\\2' rel='nofollow'>\\3</a>", $content );
		return $content;
	}

	public static function applyExpandable( string $text, int $length = 0, ?string $labelMore = NULL, ?string $labelLess = NULL ): string
	{
		if( $length && strlen( $text ) > $length ){
			$count	= -1;
			$list	= [];
			$parts	= explode( " ", $text );
			foreach( $parts as $part ){
				$count	+= strlen( $part ) + 1;
				if( $count > $length )
					break;
				$list[]	= $part;
			}
			$text	= '
<div class="text_more">
  '.nl2br( $text ).'
  <a href="#" onclick="return ViewHelperText.toggleLongText(this);">'.$labelLess.'</a>
</div>
<div class="text_less">
  '.nl2br( implode( " ", $list ) ).'
  <a href="#" onclick="return ViewHelperText.toggleLongText(this);">'.$labelMore.'</a>
</div>';
		}
		else
			$text	= nl2br( $text );
		return $text;
	}
}

