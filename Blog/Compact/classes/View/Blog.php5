<?php
class View_Blog extends CMF_Hydrogen_View{

	public function __onInit(){
		$converters	= array(
			"formatText",
			"formatLinks",
			"formatImageSearch",
			"formatMapSearch",
			"formatCurrencies",
			"formatWikiLinks",
			"formatYoutubeLinks",
			"formatImdbLinks",
			"formatDiscogsLinks",
			"formatMyspaceLinks",
			"formatMapLinks",
			"formatBreaks",
			"formatCodeBlocks",
			"formatLists",
		);
#		foreach( $converters as $converter )
#			View_Helper_ContentConverter::register( "View_Helper_ContentConverter", $converter );
	}

	public function add(){}
	
	public function article(){}

	public function author(){}

	public function dev(){
		if( ( $content = $this->getData( 'content' ) ) ){
			$content	= View_Helper_ContentConverter::render( $this->env, $content );
			$attributes	= array( 'class' => 'blog-article blog-article-content' );
			$content	= UI_HTML_Tag::create( 'div', $content, $attributes );
		}
		else if( ( $files = $this->getData( 'files' ) ) ){
			$list	= array();
			arsort( $files );
			foreach( $files as $fileName => $timestamp ){
				$url	= './blog/dev/'.$fileName;
				$link	= UI_HTML_Tag::create( 'a', $fileName, array( 'href' => $url ) );
				$date	= UI_HTML_Tag::create( 'span', date( 'y-m-d', $timestamp ) );
				$list[]	= UI_HTML_Tag::create( 'li', ' <small><em>'.$date.'</em></small> '.$link );
			}
			$heading	= UI_HTML_Tag::create( 'h4', 'Artikel in Vorbereitung' );
			$content	= $heading.UI_HTML_Tag::create( 'ul', join( $list ) );
		}
		return $content;
	}
	
	public function edit(){}

	public function feed(){
		$words		= $this->getWords( 'feed' );
		$articles	= $this->getData( 'articles' );
		$debug		= $this->getData( 'debug' );
		$config		= $this->env->getConfig();
		$baseUrl	= $config->get( 'app.base.url' );
		$module		= new ADT_List_Dictionary( $config->getAll( 'module.blog_compact.' ) );
		$channel	= array(
			'link'		=> $baseUrl.'blog',
			'language'	=> $module->get( 'feed.language' ),
			'generator'	=> 'cmClasses::XML_RSS_Builder/'.CMC_VERSION,
			'title'		=> $words->title,
		);
		if( $config->get( 'app.name' ) )
			$channel['title']	= $config->get( 'app.name' ).': '.$words->title;
		if( $words->description )
			$channel['description']	= $words->description;
		if( $words->category )
			$channel['category']	= $words->category;
		if( $words->copyright )
			$channel['copyright']	= $words->copyright;
		if( $config->get( 'module.blog_compact.feed.editor' ) )
			$channel['managingEditor']	= $module->get( 'feed.editor' );
		if( $config->get( 'app.email' ) ){
			$channel['webMaster']	= $config->get( 'app.email' );
			if( $config->get( 'app.author' ) )
				$channel['webMaster']	.=' ('.$config->get( 'app.author' ).')';
		}

		if( $module->get( 'feed.image' ) ){
			$channel['imageUrl']	= $module->get( 'feed.image' );
			$channel['imageLink']	= $this->env->getConfig()->get( 'app.base.url' );
			$channel['imageTitle']	= $config->get( 'app.name' );
			if( $module->get( 'feed.image.width' ) )
				$channel['imageWidth']	= $module->get( 'feed.image.width' );
			if( $module->get( 'feed.image.height' ) )
				$channel['imageHeight']	= $module->get( 'feed.image.height' );
		}

		$feed		= new XML_RSS_Builder();
		$feed->setChannelData( $channel );
		foreach( $articles as $article ){
			$uri	= $baseUrl.'blog/article/'.$article->articleId;
			$content	= array_shift( explode( "\n", strip_tags( $article->content ) ) );
			$content	= View_Helper_ContentConverter::formatText( $this->env, $content );
			$content	= View_Helper_ContentConverter::formatLinks( $this->env, $content );
			$content	= View_Helper_ContentConverter::formatWikiLinks( $this->env, $content );
			$content	= View_Helper_ContentConverter::formatYoutubeLinks( $this->env, $content );
			$content	= View_Helper_ContentConverter::formatMapLinks( $this->env, $content );
			$content	= View_Helper_ContentConverter::formatMapSearch( $this->env, $content );
			$content	= View_Helper_ContentConverter::formatImageSearch( $this->env, $content );
			$content	= View_Helper_ContentConverter::formatImdbLinks( $this->env, $content );
			$content	= View_Helper_Blog::formatBlogLinks( $this->env, $content );
			if( $this->env->getModules()->has( 'Gallery_Compact' ) )
				$content	= View_Helper_Gallery::formatGalleryLinks( $this->env, $content );
				
			$data	= array(
				'title'			=> $article->title,
				'description'	=> $content,
				'guid'			=> $uri,
				'link'			=> $uri,
				'category'		=> 'Blog-Artikel',
				'source'		=> $baseUrl.'blog/feed',
			);
			if( $module->get( 'niceURLs' ) )
				$data['link']	.= '-'.View_Helper_Blog::getArticleTitleUrlLabel( $article );
			$timestamp	= $article->createdAt;
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

	public function index(){}

	public function tag(){}

	protected function renderArticleAbstractList( $articles, $date = TRUE, $time = TRUE, $authors = TRUE, $linkAuthors = TRUE ){
		$list		= array();
		$config		= $this->env->getConfig();
		foreach( $articles as $article ){
			$url		= './blog/article/'.$article->articleId;
			if( $config->get( 'module.blog_compact.niceURLs' ) )
				$url	.= '-'.View_Helper_Blog::getArticleTitleUrlLabel( $article );
			$label		= str_replace( '&', '&amp;', $article->title );
			$link		= UI_HTML_Elements::Link( $url, $label, 'icon-label link-blog' );

			$abstract	= array_shift( preg_split( "/\n/", $article->content ) );
			$abstract	= View_Helper_ContentConverter::render( $this->env, $abstract );
			$abstract	= UI_HTML_Tag::create( 'div', $abstract, array( 'class' => 'blog-article-content' ) );

			$infoList	= View_Blog::renderInfoList( $article, $date, $time );
			$authorList	= $authors ? View_Blog::renderAuthorList( $this->env, $article->authors, $linkAuthors ) : '';
			$tagList	= View_Blog::renderTagList( $this->env, $article->tags );
			$info		= UI_HTML_Tag::create( 'div', $infoList.$authorList.$tagList, array( 'class' => "blog-article-info" ) );

			$content	= $link . $info. $abstract;
			$attributes	= array( 'class' => 'blog-article-list-item  blog-article-abstract' );
			$item		= UI_HTML_Tag::create( 'li', $content, $attributes );
			$list[$article->title]	= $item;
		}
		if( !$list )
			return NULL;
		return UI_HTML_Tag::create( 'ul', join( $list ), array( 'class' => 'blog-article-list' ) );
	}

	static public function renderInfoList( $article, $date = TRUE, $time = TRUE ){
		$infoList	= array();
		$attrItem	= array( 'class' => 'blog-article-info-list-item' );
		if( $date && $article->createdAt ){
			$date		= date( 'd.m.Y', $article->createdAt );
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

	static public function renderAuthorList( $env, $authors, $linked = FALSE ){
		$authorList	= array();
		if( !$authors )
			return '';
		foreach( $authors as $author ){
			$url		= './blog/author/'.rawurlencode( $author->username );
			$label		= UI_HTML_Tag::create( 'span', $author->username, array( 'class' => 'link-author' ) );
			if( $linked )
				$label		= UI_HTML_Tag::create( 'a', $author->username, array( 'href' => $url, 'class' => 'link-author' ) );
			$authorList[]	= UI_HTML_Tag::create( 'li', $label, array( 'class' => 'blog-article-author-list-item' ) );
		}
		return UI_HTML_Tag::create( 'ul', join( $authorList ), array( 'class' => 'blog-article-author-list' ) );
	}

	static public function renderTagList( $env, $tags ){
		$tagList	= array();
		if( $tags ){
			foreach( $tags as $tag ){
				$url	= './blog/tag/'.rawurlencode( str_replace( '&', '%26', $tag->title ) );
				$tagList[]	= UI_HTML_Tag::create( 'a', $tag->title, array( 'href' => $url ) );
			}
			$span	= UI_HTML_Tag::create( 'span', join( ' ', $tagList ), array( 'class' => 'icon-label link-tag' ) );
			return UI_HTML_Tag::create( 'span', $span, array( 'class' => 'blog-article-tag-list' ) );
		}
		return '';

		$tagList	= array();
		foreach( $tags as $tag ){
			$link		= View_Helper_Blog::renderTagLink( $env, $tag->title );
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