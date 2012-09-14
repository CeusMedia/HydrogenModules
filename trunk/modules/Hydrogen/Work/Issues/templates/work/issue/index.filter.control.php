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
$filters[]	= HTML::LiClass( 'column-clear',
	HTML::Label( 'status', $words['indexFilter']['labelStatus'] ).HTML::BR.
	HTML::Select( 'status[]', $optStatus, 'max rows-8', NULL, 'this.form.submit()' )
);
$filters[]	= HTML::LiClass( 'column-clear',
	HTML::Label( 'priority', $words['indexFilter']['labelPriority'] ).HTML::BR.
	HTML::Select( 'priority[]', $optPriority, 'max rows-7', NULL, 'this.form.submit()' )
);
$filters[]	= HTML::LiClass( 'column-clear',
	HTML::Label( 'type', $words['indexFilter']['labelType'] ).HTML::BR.
	HTML::Select( 'type[]', $optType, 'max rows-4', NULL, 'this.form.submit()' )
);
if( !empty( $projects ) ){
	$optProject	= array();
	foreach( $projects as $project )
		$optProject[$project->projectId]	= $project->title;
	$optProject		= numerizeWords( array( '' => '- alle -' ) + $optProject, $numberProjects );
	$optProject	= $view->renderOptions( $optProject, 'projectId', $session->get( 'filter-issue-projectId' ), 'issue-project');

	$filters[]	= HTML::LiClass( 'column-clear',
		HTML::Label( 'projectId', $words['indexFilter']['labelProject'] ).HTML::BR.
		HTML::Select( 'projectId[]', $optProject, 'max rows-4', NULL, 'this.form.submit()' )
	);
}
	
$filters[]	= HTML::LiClass( 'column-clear',
	HTML::Label( 'order', $words['indexFilter']['labelOrder'] ).HTML::BR.
	HTML::Select( 'order', $optOrder, 'max rows-1', NULL, 'this.form.submit()' )
);
$filters[]	= HTML::LiClass( 'column-clear',
	HTML::Label( 'direction', $words['indexFilter']['labelDirection'] ).HTML::BR.
	HTML::Select( 'direction', $optDirection, 'max rows-1', NULL, 'this.form.submit()' )
);
#$filters[]	= HTML::LiClass( 'column-clear','<hr/>' );

$filters[]	= HTML::LiClass( 'column-clear',
	HTML::DivClass( 'column-left-70',
		HTML::Label( 'title', $words['indexFilter']['labelTitle'] ).HTML::BR.
		HTML::Input( 'title', $title, 'max' )
	).
	HTML::DivClass( 'column-left-25',
		HTML::Label( 'issueId', $words['indexFilter']['labelIssueId'] ).HTML::BR.
		HTML::Input( 'issueId', $issueId, 'max numeric' )
	).HTML::DivClass( 'column-clear' )
);

return '
<form id="form_filter-issue" name="filterIssues" action="./work/issue/filter" method="post">
	<fieldset style="position: relative">
		<legend class="filter">'.$words['indexFilter']['legend'].'</legend>
		'.HTML::UlClass( 'input', $filters ).'
		<div class="buttonbar">
			'.UI_HTML_Elements::Button( 'filter', $words['indexFilter']['buttonFilter'], 'button filter' ).'
			'.UI_HTML_Elements::LinkButton( './work/issue/filter/reset', $words['indexFilter']['buttonReset'], 'button reset' ).'
		</div>
	</fieldset>
</form>
';

?>