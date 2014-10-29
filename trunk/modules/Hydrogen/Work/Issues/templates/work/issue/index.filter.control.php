<?php
//  --  FILTER  --  //
function numerizeWords( $words, $numbers = array() ){
	foreach( $words as $key => $label ){
		if( !strlen( $key ) ){
			$number	= ' ('.array_sum( $numbers ).')';
		}
		else{
			$number	= '(?)';
			if( isset( $numbers[$key] ) ){
				$number	= ' ('.$numbers[$key].')';
				if( $numbers[$key] == 0 )
					$number	= '';# (-)';
			}
		}
		$words[$key]	= $label.$number;
	}
	return $words;
}
$session	= $env->getSession();

$title		= $session->get( 'filter-issue-title' );
$limit		= $session->get( 'filter-issue-limit' ) ? $session->get( 'filter-issue-limit' ) : 10;
$issueId	= $session->get( 'filter-issue-issueId' );
$order		= $session->get( 'filter-issue-order' );
$direction	= 'DESC';#$session->get( 'filter-issue-direction' );

$optOrder	= array( '' => '-' );
foreach( $words['indexFilterOrders'] as $column => $label )
	$optOrder[$column]	= $label;
$optOrder['_selected']	= $order;

$optDirection	= array();
foreach( $words['indexFilterDirections'] as $key => $label ){
	$selected	= $key == $direction;
	$class		= 'direction direction'.$key;
	$optDirection[]	= UI_HTML_Elements::Option( $key, $label, $selected, FALSE, $class );
}
$optDirection	= join( $optDirection );

$words['types']			= numerizeWords( array( '' => '- alle -' ) + $words['types'], $numberTypes );
$words['severities']	= numerizeWords( array( '' => '- alle -' ) + $words['severities'], array() );
$words['priorities']	= numerizeWords( array( '' => '- alle -' ) + $words['priorities'], $numberPriorities );
$words['states']		= numerizeWords( array( '' => '- alle -' ) + $words['states'], $numberStates );

$optType		= $view->renderOptions( $words['types'], 'type', $session->get( 'filter-issue-type' ), 'issue-type type-%1$d');
$optSeverity	= $view->renderOptions( $words['severities'], 'severity', $session->get( 'filter-issue-severity' ), 'issue-severity severity-%1$d');
$optPriority	= $view->renderOptions( $words['priorities'], 'priority', $session->get( 'filter-issue-priority' ), 'issue-priority priority-%1$d');
$optStatus		= $view->renderOptions( $words['states'], 'status', $session->get( 'filter-issue-status' ), 'issue-status status-%1$d');

$filters		= array();

$filters[]	= HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span8',
		HTML::Label( 'title', $words['indexFilter']['labelTitle'] ).
		HTML::Input( 'title', $title, '-max span12' )
	).
	HTML::DivClass( 'span4',
		HTML::Label( 'issueId', $words['indexFilter']['labelIssueId'] ).
		HTML::Input( 'issueId', $issueId, '-max numeric span12' )
	)
);
$filters[]	= HTML::DivClass( 'row-fluid',
	HTML::Label( 'status', $words['indexFilter']['labelStatus'] ).
	HTML::Select( 'status[]', $optStatus, 'span12 -max rows-8', NULL, 'this.form.submit()' )
);
$filters[]	= HTML::DivClass( 'row-fluid',
	HTML::Label( 'priority', $words['indexFilter']['labelPriority'] ).
	HTML::Select( 'priority[]', $optPriority, '-max span12 rows-7', NULL, 'this.form.submit()' )
);
$filters[]	= HTML::DivClass( 'row-fluid',
	HTML::Label( 'type', $words['indexFilter']['labelType'] ).
	HTML::Select( 'type[]', $optType, '-max span12 rows-4', NULL, 'this.form.submit()' )
);
if( !empty( $projects ) ){
	$optProject	= array();
	foreach( $projects as $project )
		$optProject[$project->projectId]	= $project->title;
	$optProject		= numerizeWords( array( '' => '- alle -' ) + $optProject, $numberProjects );
	$optProject		= $view->renderOptions( $optProject, 'projectId', $session->get( 'filter-issue-projectId' ), 'issue-project');

	$filters[]	= HTML::DivClass( 'row-fluid',
		HTML::Label( 'projectId', $words['indexFilter']['labelProject'] ).
		HTML::Select( 'projectId[]', $optProject, '-max span12 rows-4', NULL, 'this.form.submit()' )
	);
}

$filters[]	= HTML::DivClass( 'row-fluid',
	HTML::DivClass( 'span7',
		HTML::Label( 'order', $words['indexFilter']['labelOrder'] ).
		HTML::Select( 'order', $optOrder, '-max span12 rows-1', NULL, 'this.form.submit()' )
	).
	HTML::DivClass( 'span5',
		HTML::Label( 'direction', $words['indexFilter']['labelDirection'] ).
		HTML::Select( 'direction', $optDirection, '-max span12 rows-1', NULL, 'this.form.submit()' )
	)
);

return '
<div class="content-panel">
	<h3 class="filter">'.$words['indexFilter']['legend'].'</h3>
	<div class="content-panel-inner">
		<form id="form_filter-issue" name="filterIssues" action="./work/issue/filter" method="post">
			'.join( $filters ).'
			<div class="buttonbar">
				<button type="submit" class="btn btn-small btn-primary" name="filter"><i class="icon-search icon-white"></i> '.$words['indexFilter']['buttonFilter'].'</button>
				<a href="./work/issue/filter/reset" class="btn btn-small btn-inverse"><i class="icon-zoom-out icon-white"></i> '.$words['indexFilter']['buttonReset'].'</a>
			</div>
		</form>
	</div>
</div>';
?>
