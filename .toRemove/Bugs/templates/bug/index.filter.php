<?php
//  --  FILTER  --  //

$session	= $this->env->getSession();


//$script	= '$(document).ready(function(){});';
//$this->env->page->js->addScript( $script );

$title	= $this->env->getSession()->get( 'filter-bug-title' );
$limit	= $this->env->getSession()->get( 'filter-bug-limit' );
$bugId	= $this->env->getSession()->get( 'filter-bug-bugId' );

$optOrder	= array( '' => '-' );
foreach( $words['indexFilterOrders'] as $column => $label )
	$optOrder[$column]	= $label;
$optOrder['_selected']	= $this->env->getSession()->get( 'filter-bug-order' );

$optDirection	= array();
foreach( $words['indexFilterDirections'] as $key => $label ){
	$selected	= $key == $session->get( 'filter-bug-direction' );
	$class		= 'direction direction'.$key;
	$optDirection[]	= UI_HTML_Elements::Option( $key, $label, $selected, FALSE, $class );
}
$optDirection	= join( $optDirection );

$mode	= (int) $session->get( 'bug-filter-panel-mode' );

if( $mode == 1 ){

	$words['types']			= array( '' => '- alle -' ) + $words['types'];
	$words['severities']	= array( '' => '- alle -' ) + $words['severities'];
	$words['priorities']	= array( '' => '- alle -' ) + $words['priorities'];
	$words['states']		= array( '' => '- alle -' ) + $words['states'];

	$optType		= $this->renderOptions( $words['types'], 'type', $session->get( 'filter-bug-type' ), 'bug-type type-%1$d');
	$optSeverity	= $this->renderOptions( $words['severities'], 'severity', $session->get( 'filter-bug-severity' ), 'bug-severity severity-%1$d');
	$optPriority	= $this->renderOptions( $words['priorities'], 'priority', $session->get( 'filter-bug-priority' ), 'bug-priority priority-%1$d');
	$optStatus		= $this->renderOptions( $words['states'], 'status', $session->get( 'filter-bug-status' ), 'bug-status status-%1$d');
	
	return '
	<form id="form_filter-bugs" name="filterBugs" action="./bug/filter" method="post">
		<fieldset style="position: relative">
			<legend class="filter">'.$words['indexFilter']['legend'].'</legend>
			<table>
				<colgroup>
					<col width="25%"/>
					<col width="25%"/>
					<col width="25%"/>
					<col width="25%"/>
				</colgroup>
				<tr>
					<td>
						<label for="username">'.$words['indexFilter']['labelTitle'].'</label><br/>
						'.UI_HTML_Elements::Input( 'title', $title, 'm' ).'
					</td>
					<td>
						<label for="username">'.$words['indexFilter']['labelBugId'].'</label><br/>
						'.UI_HTML_Elements::Input( 'bugId', $bugId, 'xs numeric' ).'
					</td>
					<td>
						<label for="status">'.$words['indexFilter']['labelProject'].'</label><br/>
						'.UI_HTML_Elements::Select( 'project', '<option>- alle -</option>', 'm', NULL, 'filter-bugs' ).'
					</td>
					<td>

					</td>
				</tr>
				<tr>
					<td>
						<label for="status">'.$words['indexFilter']['labelStatus'].'</label><br/>
						'.UI_HTML_Elements::Select( 'status[]', $optStatus, 'm rows-8', NULL, 'filter-bugs' ).'
					</td>
					<td>
						<label for="priority">'.$words['indexFilter']['labelPriority'].'</label><br/>
						'.UI_HTML_Elements::Select( 'priority[]', $optPriority, 'm rows-7', NULL, 'filter-bugs' ).'
					</td>
					<td>
						<label for="severity">'.$words['indexFilter']['labelSeverity'].'</label><br/>
						'.UI_HTML_Elements::Select( 'severity[]', $optSeverity, 'm rows-5', NULL, 'filter-bugs' ).'
					</td>
					<td>
						<label for="type">'.$words['indexFilter']['labelType'].'</label><br/>
						'.UI_HTML_Elements::Select( 'type[]', $optType, 'm rows-4', NULL, 'filter-bugs' ).'
					</td>
				</tr>
				<tr>
					<td>
						<label for="order">'.$words['indexFilter']['labelOrder'].'</label><br/>
						'.UI_HTML_Elements::Select( 'order', $optOrder, 'm rows-1', NULL, 'filter-bugs' ).'
					</td>
					<td>
						<label for="direction">'.$words['indexFilter']['labelDirection'].'</label><br/>
						'.UI_HTML_Elements::Select( 'direction', $optDirection, 'm', NULL, 'filter-bugs' ).'
					</td>
					<td>
						<label for="limit">'.$words['indexFilter']['labelLimit'].'</label><br/>
						'.UI_HTML_Elements::Input( 'limit', $limit, 'xs numeric' ).'
					</td>
					<td></td>
				</tr>
			</table>
			<div class="buttonbar">
				'.UI_HTML_Elements::Button( 'filter', $words['indexFilter']['buttonFilter'], 'button filter' ).'
				'.UI_HTML_Elements::LinkButton( './bug/filter/reset', $words['indexFilter']['buttonReset'], 'button reset' ).'
				'.UI_HTML_Elements::LinkButton( './bug/filter/mode/0', $words['indexFilter']['buttonCollapse'], 'button less' ).'
			</div>
		</fieldset>
	</form>
	';
}
else
{
	return '
	<form id="form_filter-bugs" name="filterBugs" action="./bug/filter" method="post">
		<fieldset style="position: relative">
			<legend class="filter">'.$words['indexFilter']['legend'].'</legend>
			<table>
				<colgroup>
					<col width="25%"/>
					<col width="25%"/>
					<col width="25%"/>
					<col width="25%"/>
				</colgroup>
				<tr>
					<td>
						<label for="username">'.$words['indexFilter']['labelTitle'].'</label><br/>
						'.UI_HTML_Elements::Input( 'title', $title, 'm' ).'
					</td>
					<td>
						<label for="username">'.$words['indexFilter']['labelBugId'].'</label><br/>
						'.UI_HTML_Elements::Input( 'bugId', $bugId, 'xs numeric' ).'
					</td>
					<td>
						<label for="status">'.$words['indexFilter']['labelProject'].'</label><br/>
						'.UI_HTML_Elements::Select( 'project', '<option>- alle -</option>', 'm', NULL, 'filter-bugs' ).'
					</td>
					<td></td>
				</tr>
				<tr>
					<td>
						<label for="order">'.$words['indexFilter']['labelOrder'].'</label><br/>
						'.UI_HTML_Elements::Select( 'order', $optOrder, 'm rows-1', NULL, 'filter-bugs' ).'
					</td>
					<td>
						<label for="direction">'.$words['indexFilter']['labelDirection'].'</label><br/>
						'.UI_HTML_Elements::Select( 'direction', $optDirection, 'm', NULL, 'filter-bugs' ).'
					</td>
					<td>
						<label for="limit">'.$words['indexFilter']['labelLimit'].'</label><br/>
						'.UI_HTML_Elements::Input( 'limit', $limit, 'xs numeric' ).'
					</td>
					<td></td>
				</tr>
			</table>
			<div class="buttonbar">
				'.UI_HTML_Elements::Button( 'filter', $words['indexFilter']['buttonFilter'], 'button filter' ).'
				'.UI_HTML_Elements::LinkButton( './bug/filter/reset', $words['indexFilter']['buttonReset'], 'button reset' ).'
				'.UI_HTML_Elements::LinkButton( './bug/filter/mode/1', $words['indexFilter']['buttonExpand'], 'button more' ).'
			</div>
		</fieldset>
	</form>
	';
	
}
?>