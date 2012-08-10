<?php
class View_Helper_Blog{

	static public function formatBlogLinks( $env, $content ){
		$matches	= array();
		preg_match_all( '/\[blog:([0-9]+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$model		= new Model_Article( $env );
			$article	= $model->get( (int) $matches[1][$i] );
			$title		= trim( $matches[3][$i] );
			$uri		= './blog/article/'.$matches[1][$i];
			if( $article ){
				$title	= $title ? $title : $article->title;
				$uri	= './blog/article/'.$matches[1][$i].'/'.urlencode( $article->title );
			}
			else
				$title	= UI_HTML_Tag::create( 'strike', $title );
			$link		= UI_HTML_Elements::Link( $uri, $title, 'link-blog', '_blank' );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	static public function formatEmoticons( $env, $content ){
		$path		= './images/emoticons/';
		$emoticons	= array(
			' :)'	=> '1.ico',
			' :('	=> '3.ico',
			' ;)'	=> '6.ico',
			' :D'	=> '7.ico',
			' <3'	=> '14.ico',
		);
		foreach( $emoticons as $key => $value ){
			$image		= UI_HTML_Elements::Image( $path.$value, trim( $key ), 16, 16 );
			$content	= str_replace( $key, ' '.$image, $content );
		}
		return $content;
	}

	static public function formatImages( $env, $content ){
		$config		= $env->getConfig();
		$path		= $config->get( 'path.images' ).$config->get( 'module.blog_compact.path.images' );
		$matches	= array();
		preg_match_all( '/\[image:(\S+)(\|(.*))?\]\r?\n/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$title		= trim( $matches[3][$i] );
			$fileName	= $matches[1][$i];
			$uri		= $path.$fileName;
			$thumb		= './blog/thumb/'.$fileName;
			$image		= UI_HTML_Elements::Image( $thumb, $title, 'thumb' );
			if( file_exists( $uri ) ){
				$attributes	= array(
					'href'	=> $uri,
					'class'	=> 'no-thickbox layer-image',
					'title'	=> $title,
					'rel'	=> 'blog-article-gallery',
				);
				$image		= UI_HTML_Tag::create( 'a', $image, $attributes );
			}
			$container	= UI_HTML_Tag::create( 'div', $image, array( 'class'=>'image' ) );
			$content	= str_replace( $matches[0][$i], $container, $content );
		}
		return $content;
	}

	static public function formatIFrames( $env, $content ){
		$matches	= array();
		preg_match_all( '/\[iframe:(\S+)\]\r?\n/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$uri		= $matches[1][$i];
			$attributes	= array(
				'src'			=> $uri,
				'width'			=> 600,
				'height'		=> 460,
				'frameborder'	=> 0,
				'scrolling'		=> 'no',
				'marginheight'	=> 0,
				'marginwidth'	=> 0,
			);
			$frame		= UI_HTML_Tag::create( 'iframe', '', $attributes );
			$content	= str_replace( $matches[0][$i], $frame, $content );
		}
		return $content;
	}

	static public function formatGaleryLinks( $env, $content ){
		$matches	= array();
		preg_match_all( '/\[gallery:(\S+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$uri		= './gallery/'.$matches[1][$i];
			$title		= trim( $matches[3][$i] );
			$link		= UI_HTML_Elements::Link( $uri, $title, 'link-gallery ', '_blank' );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	static public function getArticleTitleUrlLabel( $article ){
		$cut	= array( '?', '!', ':', '.', ';', '"', "'" );
		$label	= str_replace( $cut, '', $article->title );											//  remove inappropriate characters
		$label	= str_replace( ' ', '-', $label );													//  replace whitespace by hyphen
		$label	= preg_replace( '/-+/', '-', $label );												//  shorten hyphens to one
		return urlencode( $label );																	//  return encoded URL component
	}
	
	static public function getFeedUrl( CMF_Hydrogen_Environment_Abstract $env, $limit = NULL ){
		$limit	= ( $limit !== NULL ) ? '/'.abs( (int) $limit ) : '';
		return $env->getConfig()->get( 'app.base.url' ).'blog/feed'.$limit;
	}

	static public function renderTopTags( CMF_Hydrogen_Environment_Abstract $env, $limit, $offset = 0, $states = array( 1 ) ){
		$states		= is_array( $states ) ? $states : array( $states );
		$prefix		= $env->getDatabase()->getPrefix();
		$query		= '
			SELECT
				COUNT(at.articleId) as nr,
				at.tagId,
				t.title
			FROM
				'.$prefix.'articles AS a,
				'.$prefix.'article_tags AS at,
				'.$prefix.'tags AS t
			WHERE
				at.tagId = t.tagId AND
				at.articleId = a.articleId AND
				a.status IN('.join( ", ", $states ) .')
			GROUP BY at.tagId
			ORDER BY nr DESC
			LIMIT '.$offset.', '.$limit;
		$tags	= $env->getDatabase()->query( $query )->fetchAll( PDO::FETCH_OBJ );
		$list	= array();
		foreach( $tags as $relation ){
			$url	= './blog/tag/'.urlencode( urlencode( $relation->title ) );
			$nr		= UI_HTML_Tag::create( 'span', $relation->nr, array( 'class' => 'number-indicator' ) );
			$link	= UI_HTML_Tag::create( 'a', $relation->title, array( 'href' => $url, 'class' => 'link-tag' ) );
			$list[]	= UI_HTML_Tag::create( 'li', $nr.$link );
		} 
		if( !$list )
			return NULL;
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'top-tags' ) );
	}
	
	static public function renderLatestArticles( CMF_Hydrogen_Environment_Abstract $env, $limit, $offset = 0 ){
		$list	= array();
		$model	= new Model_Article( $env );
		$latest	= $model->getAll( array( 'status' => 1 ), array( 'articleId' => 'DESC' ), array( $offset, $limit ) );
		foreach( $latest as $article ){
			$link	= UI_HTML_Tag::create( 'a', $article->title, array( 'href' => 'blog/article/'.$article->articleId.'' ) );
			$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => 'blog-item' ) );
		}
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'list-latest-articles' ) );
	}
}
?>