<?php

$table	= '<div class="alert alert-warning">No entries found.</div>';
if( $bookmarks ){
	$iconVisit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-right' ) );
	$iconView	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-eye-open fa fa-eye' ) );
	$iconEdit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil fa fa-pencil' ) );
	$rows	= array();
	foreach( $bookmarks as $bookmark ){
		$urlVisit	= './work/bookmark/visit/'.$bookmark->bookmarkId;
		$urlView	= './work/bookmark/view/'.$bookmark->bookmarkId;
		$linkTitle	= UI_HTML_Tag::create( 'a', $bookmark->title, array(
			'href'		=> $urlView,
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
				) ),
			) );
		}

		$tags	= array();
		foreach( $bookmark->tags as $tag ){
			$tags[]	= UI_HTML_Tag::create( 'span', $tag->title, array( 'class' => 'label label-info' ) );
		}
		$tags	= UI_HTML_Tag::create( 'ul', $tags, array( 'class' => 'inline list-inline' ) );

		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', array(
				$title,
				$pageTitle,
				$url,
				$description,
				$tags,
			), array( 'class' => NULL ) ),
			UI_HTML_Tag::create( 'td', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'a', $iconVisit, array(
						'href'		=> './work/bookmark/visit/'.$bookmark->bookmarkId,
						'target'	=> '_blank',
						'class'		=> 'btn btn-mini',
						'title'		=> 'visit',
					) ),
					'<br/>',
					UI_HTML_Tag::create( 'a', $iconView, array(
						'href'		=> './work/bookmark/view/'.$bookmark->bookmarkId,
						'class'		=> 'btn btn-mini',
						'title'		=> 'details',
					) ),
					'<br/>',
					UI_HTML_Tag::create( 'a', $iconEdit, array(
						'href'		=> './work/bookmark/edit/'.$bookmark->bookmarkId,
						'class'		=> 'btn btn-mini',
						'title'		=> 'edit',
					) ),
				), array( 'class' => 'not-btn-group pull-right' ) ),
			), array( 'class' => NULL ) ),
		), array() );
	}
	$table	= UI_HTML_Tag::create( 'table', $rows, array( 'class' => 'table table-striped' ) );
}

$panelList	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Table' ),
	UI_HTML_Tag::create( 'div', array(
		$table
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel content-panel-list content-panel-table' ) );


$form	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Table' ),
	UI_HTML_Tag::create( 'div', array(
		$table
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel content-panel-list content-panel-table' ) );


$panelAdd	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Add' ),
	UI_HTML_Tag::create( 'div', '
		<form action="./work/bookmark/add" method="POST">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_url">URL</label>
					<input type="text" name="url" id="input_url" required="required"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">Titel</label>
					<input type="text" name="title" id="input_title"/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary">save</button>
			</div>
		</form>
	', array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel content-panel-form' ) );


$panelFilter	= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Filter' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'form', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'	=> 'text',
						'name'	=> 'query',
						'class'	=> 'span12',
						'value'	=> htmlentities( $filterQuery, ENT_QUOTES, 'UTF-8' ),
					) ),
				), array( 'class' => 'span8' ) ),
				UI_HTML_Tag::create( 'div', array(
				), array( 'class' => 'span4' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'button', 'filter', array(
					'type'	=> 'submit',
					'name'	=> 'save',
					'class'	=> 'btn btn-small btn-primary',
				) ),
				UI_HTML_Tag::create( 'a', 'reset', array(
					'href'	=> './work/bookmark/filter/reset',
					'class'	=> 'btn btn-small btn-inverse',
				) ),
			), array( 'class' => 'buttonbar' ) ),
		), array( 'action' => './work/bookmark/filter', 'method' => 'post' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel content-panel-form' ) );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		$panelFilter,
	), array( 'class' => 'span3' ) ),
	UI_HTML_Tag::create( 'div', array(
		$panelList,
	), array( 'class' => 'span6' ) ),
	UI_HTML_Tag::create( 'div', array(
		$panelAdd,
	), array( 'class' => 'span3' ) ),
), array( 'class' => 'row-fluid' ) ).'
<style>
small>a {
	color: green;
}
</style>';
?>
