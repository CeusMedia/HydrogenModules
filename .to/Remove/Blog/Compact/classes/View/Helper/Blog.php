<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Blog{

	static public function formatBlogLinks( Environment $env, $content ){
		$baseUri	= $env->getConfig()->get( 'app.base.url' );
		$matches	= [];
		preg_match_all( '/\[blog:([0-9]+)(\|(.*))?\]/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$model		= new Model_Article( $env );
			$article	= $model->get( (int) $matches[1][$i] );
			$title		= trim( $matches[3][$i] );
			$uri		= $baseUri.'blog/article/'.$matches[1][$i];
			if( $article ){
				$title	= $title ? $title : $article->title;
				$uri	= $baseUri.'blog/article/'.$matches[1][$i];
				if( $env->getConfig()->get( 'module.blog_compact.niceURLs' ) )
					$uri	.= '/'.self::getArticleTitleUrlLabel( $article );
			}
			else
				$title	= HtmlTag::create( 'strike', $title );
			$link		= HtmlElements::Link( $uri, $title, 'icon-label link-blog', '_blank' );
			$content	= str_replace( $matches[0][$i], $link, $content );
		}
		return $content;
	}

	static public function formatEmoticons( Environment $env, $content ){
		$path		= './images/emoticons/';
		$emoticons	= array(
			' :)'	=> '1.ico',
			' :('	=> '3.ico',
			' ;)'	=> '6.ico',
			' :D'	=> '7.ico',
			' <3'	=> '14.ico',
		);
		foreach( $emoticons as $key => $value ){
			$image		= HtmlElements::Image( $path.$value, trim( $key ), 16, 16 );
			$content	= str_replace( $key, ' '.$image, $content );
		}
		return $content;
	}

	static public function formatImages( Environment $env, $content ){
		$config		= $env->getConfig();
		$path		= $config->get( 'path.images' ).$config->get( 'module.blog_compact.path.images' );
		$matches	= [];
		preg_match_all( '/\[image:(\S+)(\|(.*))?\]\r?\n/U', $content, $matches );
		for( $i=0; $i<count( $matches[0] ); $i++ ){
			$title		= trim( $matches[3][$i] );
			$fileName	= $matches[1][$i];
			$uri		= $path.$fileName;
			$thumb		= './blog/thumb/'.$fileName;
			$image		= HtmlElements::Image( $thumb, $title, 'thumb' );
			if( file_exists( $uri ) ){
				$attributes	= array(
					'href'	=> $uri,
					'class'	=> 'no-thickbox layer-image',
					'title'	=> $title,
					'rel'	=> 'blog-article-gallery',
				);
				$image		= HtmlTag::create( 'a', $image, $attributes );
			}
			$container	= HtmlTag::create( 'div', $image, array( 'class'=>'image' ) );
			$content	= str_replace( $matches[0][$i], $container, $content );
		}
		return $content;
	}

	static public function formatIFrames( Environment $env, $content ){
		$matches	= [];
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
			$frame		= HtmlTag::create( 'iframe', '', $attributes );
			$content	= str_replace( $matches[0][$i], $frame, $content );
		}
		return $content;
	}

	static public function getArticleTitleUrlLabel( $article ){
		$cut	= array( '?', '!', ':', '.', ';', '"', "'" );
		$label	= str_replace( $cut, '', strip_tags( $article->title ) );							//  remove inappropriate characters and HTML tags
		$label	= trim( preg_replace( '/ +/', '_', $label ) );										//  shorten spaces to one and trim
		return rawurlencode( $label.'.html' );														//  return encoded URL component
	}

	static public function getFeedUrl( Environment $env, $limit = NULL ){
		$limit	= ( $limit !== NULL ) ? '/'.abs( (int) $limit ) : '';
		return $env->getConfig()->get( 'app.base.url' ).'blog/feed'.$limit;
	}

	static public function renderArticleLink( Environment $env, $article, $version = 0 ){
		$label		= str_replace( '&', '&amp;', $article->title );
		$keywords	= "";
		if( $env->getConfig()->get( 'module.blog_compact.niceURLs' ) )
			$keywords	= self::getArticleTitleUrlLabel( $article );
		$attributes	= array(
			'class'	=> 'not-icon-label not-link-blog',
			'href'	=> 'blog/article/'.$article->articleId.'/'.$version.'/'.$keywords,
		);
		$icon	= HtmlTag::create( 'b', '', array( 'class' => 'fa fa-comment fa-fw' ) ).'&nbsp;';
		return HtmlTag::create( 'a', $icon.$label, $attributes );
	}

	static public function renderLatestArticles( Environment $env, $limit, $offset = 0 ){
		$list		= [];
		$model		= new Model_Article( $env );
		$conditions	= array( 'status' => 1, 'createdAt' => '<= '.time() );
		$latest		= $model->getAll( $conditions, array( 'createdAt' => 'DESC' ), array( $offset, $limit ) );
		foreach( $latest as $article ){
			$link	= self::renderArticleLink( $env, $article );
			$list[]	= HtmlTag::create( 'li', $link, array( 'class' => 'blog-item autocut' ) );
		}
		return HtmlTag::create( 'ul', $list, array( 'class' => 'list-latest-articles' ) );
	}

	static public function renderTagLink( Environment $env, $tagName ){
		$attributes	= array(
			'href'	=> './blog/tag/'.rawurlencode( str_replace( '&', '%26', $tagName ) ),
			'class'	=> 'not-icon-label not-link-tag'
		);
		$icon	= HtmlTag::create( 'b', '', array( 'class' => 'fa fa-tag fa-fw' ) ).'&nbsp;';
		return HtmlTag::create( 'a', $icon.$tagName, $attributes );
	}

	static public function renderTopTags( Environment $env, $limit, $offset = 0, $states = array( 1 ) ){
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
			GROUP BY at.tagId, t.title
			ORDER BY nr DESC
			LIMIT '.$offset.', '.$limit;
		$tags	= $env->getDatabase()->query( $query )->fetchAll( PDO::FETCH_OBJ );
		$list	= [];
		foreach( $tags as $relation ){
			$nr		= HtmlTag::create( 'span', $relation->nr, array( 'class' => 'badge badge-info pull-right not-number-indicator' ) );
			$link	= self::renderTagLink( $env, $relation->title );
			$list[]	= HtmlTag::create( 'li', $nr.$link );
		}
		if( !$list )
			return NULL;
		return HtmlTag::create( 'ul', $list, array( 'class' => 'top-tags' ) );
	}

	static public function renderFlopTags( Environment $env, $limit, $offset = 0, $states = array( 1 ) ){
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
			GROUP BY at.tagId, t.title
			ORDER BY nr ASC
			LIMIT '.$offset.', '.$limit;
		$tags	= $env->getDatabase()->query( $query )->fetchAll( PDO::FETCH_OBJ );
		$list	= [];
		foreach( $tags as $relation ){
			$nr		= HtmlTag::create( 'span', $relation->nr, array( 'class' => 'badge badge-info pull-right not-number-indicator' ) );
			$link	= self::renderTagLink( $env, $relation->title );
			$list[]	= HtmlTag::create( 'li', $nr.$link );
		}
		if( !$list )
			return NULL;
		return HtmlTag::create( 'ul', $list, array( 'class' => 'top-tags' ) );
	}
}
?>
