<?php

$iconCancel	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconVisit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-right icon-white' ) );
$iconEdit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil fa fa-pencil' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white fa fa-remove' ) );

$urlVisit	= './work/bookmark/visit/'.$bookmark->bookmarkId;
$linkTitle	= UI_HTML_Tag::create( 'a', $bookmark->title, array(
	'href'		=> $urlVisit,
	'target'	=> '_blank',
	'class'		=> 'autocut',
) );
$linkLabel	= preg_replace( "@^[a-z]{3,6}://@", '', $bookmark->url );
$linkUrl	= UI_HTML_Tag::create( 'a', $linkLabel, array(
	'href'		=> $urlVisit,
	'target'	=> '_blank',
) );
$title	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'big', $linkTitle )
), array( 'class' => 'title' ) );
$url	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'small', $linkUrl )
), array( 'class' => 'title', 'style' => 'color: green'  ) );
$description	= '';
if( $bookmark->description ){
	$description	= UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'small', $bookmark->description )
	), array( 'class' => 'title' ) );
}
$pageTitle	= '';
if( $bookmark->pageTitle !== $bookmark->title ){
	$pageTitle	= UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'small', $bookmark->pageTitle, array(
			'class' => 'muted',
			'title'	=> htmlentities( $bookmark->pageDescription, ENT_QUOTES, 'utf-8' ),
		) ),
	) );
}
$pageDescription	= '';
if( $bookmark->pageDescription ){
	$pageDescription	= UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'small', nl2br( $bookmark->pageDescription ), array(
			'class' => 'muted',
		) ),
	) );
}


$panelView	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'View' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			$title,
			$pageTitle,
			$url,
			$description,
			$pageDescription,
		), array( 'class' => NULL ) ),
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;zurück', array(
				'href'		=> './work/bookmark/',
				'class'		=> 'btn btn-small'
			) ),
			UI_HTML_Tag::create( 'a', $iconVisit.'&nbsp;visit', array(
				'href'		=> './work/bookmark/visit/'.$bookmark->bookmarkId,
				'target'	=> '_blank',
				'class'		=> 'btn btn-small btn-info'
			) ),
			UI_HTML_Tag::create( 'a', $iconEdit.'&nbsp;edit', array(
				'href'		=> './work/bookmark/edit/'.$bookmark->bookmarkId,
				'class'		=> 'btn btn-small'
			) ),
			UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
				'href'		=> './work/bookmark/remove/'.$bookmark->bookmarkId,
				'class'		=> 'btn btn-small btn-danger'
			) ),
		), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );


$panelInfo	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Info' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'dl', array(
			UI_HTML_Tag::create( 'dt', array(
				'createdAt'
			), array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'dd', array(
				date( 'd.m.Y H:i:s', $bookmark->createdAt )
			), array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'dt', array(
				'modifiedAt'
			), array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'dd', array(
				$bookmark->modifiedAt ? date( 'd.m.Y H:i:s', $bookmark->modifiedAt ) : '-'
			), array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'dt', array(
				'visits'
			), array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'dd', array(
				$bookmark->visits
			), array( 'class' => '' ) ),
		), array( 'class' => 'not-dl-horizontal' ) ),
/*		UI_HTML_Tag::create( 'div', array(
		), array( 'class' => 'buttonbar' ) ),*/
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

$list	= 'keine kommentare';
if( count( $comments ) ){
	$list	= array();
	foreach( $comments as $comment ){
		$list[]	= UI_HTML_Tag::create( 'li', $comment->content );
	}
	$list	= UI_HTML_Tag::create( 'ul', $list );
}

$panelComments	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Commments' ),
	UI_HTML_Tag::create( 'div', array(
		$list,
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'form', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'div', array(
						UI_HTML_Tag::create( 'textarea', NULL, array(
							'type'	=> 'text',
							'name'	=> 'comment',
							'class'	=> 'span12',
						) ),
					), array( 'class' => 'span10' ) ),
					UI_HTML_Tag::create( 'div', array(
						UI_HTML_Tag::create( 'button', 'save', array(
							'type'	=> 'submit',
							'name'	=> 'save',
							'class'	=> 'btn btn-primary',
						) )
					), array( 'class' => 'span2' ) ),
				), array( 'class' => 'row-fluid' ) ),
			), array( 'action' => './work/bookmark/comment/'.$bookmark->bookmarkId, 'method' => 'post' ) ),
		), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

$list	= 'keine kommentare';
if( count( $tags ) ){
	$list	= array();
	foreach( $tags as $tag ){
		$list[]	= UI_HTML_Tag::create( 'li', $tag->title );
	}
	$list	= UI_HTML_Tag::create( 'ul', $list );
}

$panelTags	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Tags' ),
	UI_HTML_Tag::create( 'div', array(
		$list,
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'form', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'div', array(
						UI_HTML_Tag::create( 'input', NULL, array(
							'type'	=> 'text',
							'name'	=> 'tag',
							'class'	=> 'span12',
						) ),
					), array( 'class' => 'span8' ) ),
					UI_HTML_Tag::create( 'div', array(
						UI_HTML_Tag::create( 'button', 'save', array(
							'type'	=> 'submit',
							'name'	=> 'save',
							'class'	=> 'btn btn-primary',
						) )
					), array( 'class' => 'span4' ) ),
				), array( 'class' => 'row-fluid' ) ),
			), array( 'action' => './work/bookmark/addTag/'.$bookmark->bookmarkId, 'method' => 'post' ) ),
		), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );


return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		$panelView,
		$panelComments,
	), array( 'class' => 'span9' ) ),
	UI_HTML_Tag::create( 'div', array(
		$panelInfo,
		$panelTags,
	), array( 'class' => 'span3' ) ),
), array( 'class' => 'row-fluid' ) ).'
<style>
small>a {
	color: green;
}
</style>';

?>
