<?php
class View_Blog extends CMF_Hydrogen_View{
	
	public function add(){}
	
	public function article(){}

	public function author(){}
	
	public function edit(){}

	public function feed(){
		$words		= $this->getWords( 'feed' );
		$articles	= $this->getData( 'articles' );
		$debug		= $this->getData( 'debug' );
		$config		= $this->env->getConfig();
		$baseUrl	= $config->get( 'app.base.url' );
		$channel	= array(
			'title'			=> $config->get( 'app.name' ).': '.$words->title,
			'description'	=> $words->description,
			'link'			=> $baseUrl.'blog/'
		);
		$feed		= new XML_RSS_Builder();
		$feed->setChannelData( $channel );
		foreach( $articles as $article ){
#			print_m( $articles );
			$uri	= $baseUrl.'blog/article/'.$article->articleId;
			$data	= array(
				'title'			=> $article->title,
				'description'	=> array_shift( explode( "\n", strip_tags( $article->content ) ) ),
				'guid'			=> $uri,
				'link'			=> $uri,
			);
			if( $config->get( 'module.blog_compact.niceURLs' ) )
				$data['link']	.= '-'.View_Helper_Blog::getArticleTitleUrlLabel( $article );
			$timestamp	= max( $article->createdAt, $article->modifiedAt );
			if( $timestamp )
				$data['pubDate']	= date( "r", (double) $timestamp );
			$feed->addItem( $data );
		}
		$rss	= $feed->build( 'utf-8', '0.92' );
		if( $debug ){
			xmp( $rss );
			die;
		}
		header( 'Content-type: application/rss+xml' );
		print( $rss );
		exit;
	}

	protected function formatContent( $content, $articleId ){
		$config		= $this->env->getConfig();
		$path		= $config->get( 'path.images' ).$config->get( 'module.blog_compact.path.images' );
		View_Helper_BlogContentFormat::formatLinks( $content );
		View_Helper_BlogContentFormat::formatMapLinks( $content );
		View_Helper_BlogContentFormat::formatImages( $content, $path, $articleId );
		View_Helper_BlogContentFormat::formatImageSearch( $content );
		View_Helper_BlogContentFormat::formatIFrames( $content );
		View_Helper_BlogContentFormat::formatText( $content );
		View_Helper_BlogContentFormat::formatEmoticons( $content );
		View_Helper_BlogContentFormat::formatCurrencies( $content );
		View_Helper_BlogContentFormat::formatMapSearch( $content );
		View_Helper_BlogContentFormat::formatImdbLinks( $content );
		View_Helper_BlogContentFormat::formatWikiLinks( $content );
		return $content;
	}

	public function index(){}

	public function tag(){}

	protected function renderArticleAbstractList( $articles, $date = TRUE, $time = TRUE, $authors = TRUE, $linkAuthors = TRUE ){
		$list		= array();
		$config		= $this->env->getConfig();
		foreach( $articles as $article ){
			$url		= './blog/article/'.$article->articleId;
			if( $config->get( 'niceURLs' ) )
				$url	.= '-'.View_Helper_Blog::getArticleTitleUrlLabel( $article );
			$link		= UI_HTML_Elements::Link( $url, $article->title, 'blog-article-link' );

			$abstract	= array_shift( preg_split( "/\n/", $article->content ) );
			$abstract	= $this->formatContent( $abstract, $article->articleId );
			$abstract	= UI_HTML_Tag::create( 'div', $abstract, array( 'class' => 'blog-article-content' ) );

			$infoList	= View_Blog::renderInfoList( $article, $date, $time );
			$authorList	= $authors ? View_Blog::renderAuthorList( $article->authors, $linkAuthors ) : '';
			$tagList	= View_Blog::renderTagList( $article->tags );
			$info		= UI_HTML_Tag::create( 'div', $infoList.$authorList.$tagList, array( 'class' => "blog-article-info" ) );

			$content	= $info . $link . $abstract;
			$attributes	= array( 'class' => 'blog-article-list-item  blog-article-abstract' );
			$item		= UI_HTML_Tag::create( 'li', $content, $attributes );
			$articleList[$article->title]	= $item;
		}
		return UI_HTML_Tag::create( 'ul', join( $articleList ), array( 'class' => 'blog-article-list' ) );
	}

	static public function renderInfoList( $article, $date = TRUE, $time = TRUE ){
		$infoList	= array();
		$attrItem	= array( 'class' => 'blog-article-info-list-item' );
		if( $date && $article->createdAt ){
			$date		= date( 'Y-m-d', $article->createdAt );
			$label		= UI_HTML_Tag::create( 'span', $date, array( 'class' => 'blog-article-date' ) );
			$infoList[]	= UI_HTML_Tag::create( 'li', $label, $attrItem );
		}
		if( $time && $article->createdAt ){
			$time		= date( 'H:i', $article->createdAt );
			$label		= UI_HTML_Tag::create( 'span', $time, array( 'class' => 'blog-article-time' ) );
			$infoList[]	= UI_HTML_Tag::create( 'li', $label, $attrItem );
		}
		$attrList	= array( 'class' => 'blog-article-info-list' );
		return UI_HTML_Tag::create( 'ul', join( $infoList ), $attrList );
	}

	static public function renderAuthorList( $authors, $linked = FALSE ){
		$authorList	= array();
		foreach( $authors as $author ){
			$url		= './blog/author/'.urlencode( $author->username );
			$label		= UI_HTML_Tag::create( 'span', $author->username, array( 'class' => 'link-author' ) );
			if( $linked )
				$label		= UI_HTML_Tag::create( 'a', $author->username, array( 'href' => $url, 'class' => 'link-author' ) );
			$authorList[]	= UI_HTML_Tag::create( 'li', $label, array( 'class' => 'blog-article-author-list-item' ) );
		}
		return UI_HTML_Tag::create( 'ul', join( $authorList ), array( 'class' => 'blog-article-author-list' ) );
	}

	static public function renderTagList( $tags ){
		$tagList	= array();
		foreach( $tags as $tag ){
			$url		= './blog/tag/'.urlencode( urlencode( $tag->title ) );
			$link		= UI_HTML_Tag::create( 'a', $tag->title, array( 'href' => $url, 'class' => 'link-tag' ) );
			$tagList[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => 'blog-article-tag-list-item' ) );
		}
		return UI_HTML_Tag::create( 'ul', join( $tagList ), array( 'class' => 'blog-article-tag-list' ) );
	}

	/**
	 *	Renders scaled image if not existing and returns it directly (binary) to the browser.
	 *	@access		public
	 *	@return		void
	 *	@todo		configure thumb dimensions by module
	 */
	public function thumb(){

		$path	= $this->getData( 'path' );
		$file	= $this->getData( 'file' );

		$data		= pathinfo( $file );
		$thumb		= $path.'/'.$data['filename'].'.thumb.'.$data['extension'];
		$url		= $path.$file;

		$image		= new UI_Image( $thumb );
		$response	= new Net_HTTP_Response();
		$response->addHeaderPair( 'Content-type', $image->getMimeType() );
		$response->addHeaderPair( 'Last-modified', date( 'r', filemtime( $url ) ) );
		$response->addHeaderPair( 'Cache-control', 'max-age: '.( 24*60*60 ) );
		$response->addHeaderPair( 'Expires', date('r', time()+24*60*60 ) );
		if( !file_exists( $thumb ) ){
			if( !function_exists( 'imagecreatetruecolor' ) )
				$response->setBody( file_get_contents( $url ) );
			else{
				$a	= new UI_Image_ThumbnailCreator( $url, $thumb, 100 );
				$a->thumbizeByLimit( 240, 180 );
			}
		}
		$response->setBody( file_get_contents( $thumb ) );
		Net_HTTP_Response_Sender::sendResponse( $response );
		exit;
	}
}
?>