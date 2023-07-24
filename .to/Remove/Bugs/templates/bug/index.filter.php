<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

//  --  FILTER  --  //

$session	= $this->env->getSession();


//$script	= '$(document).ready(function(){});';
//$this->env->page->js->addScript( $script );

$title	= $this->env->getSession()->get( 'filter-bug-title' );
$limit	= $this->env->getSession()->get( 'filter-bug-limit' );
$bugId	= $this->env->getSession()->get( 'filter-bug-bugId' );

$optOrder	= ['' => '-'];
foreach( $words['indexFilterOrders'] as $column => $label )
	$optOrder[$column]	= $label;
$optOrder['_selected']	= $this->env->getSession()->get( 'filter-bug-order' );

$optDirection	= [];
foreach( $words['indexFilterDirections'] as $key => $label ){
	$selected	= $key == $session->get( 'filter-bug-direction' );
	$class		= 'direction direction'.$key;
	$optDirection[]	= HtmlElements::Option( $key, $label, $selected, FALSE, $class );
}
$optDirection	= join( $optDirection );

$mode	= (int) $session->get( 'bug-filter-panel-mode' );

if( $mode == 1 ){

	$words['types']			= ['' => '- alle -'] + $words['types'];
	$words['severities']	= ['' => '- alle -'] + $words['severities'];
	$words['priorities']	= ['' => '- alle -'] + $words['priorities'];
	$words['states']		= ['' => '- alle -'] + $words['states'];

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
						'.HtmlElements::Input( 'title', $title, 'm' ).'
					</td>
					<td>
						<label for="username">'.$words['indexFilter']['labelBugId'].'</label><br/>
						'.HtmlElements::Input( 'bugId', $bugId, 'xs numeric' ).'
					</td>
					<td>
						<label for="status">'.$words['indexFilter']['labelProject'].'</label><br/>
						'.HtmlElements::Select( 'project', '<option>- alle -</option>', 'm', NULL, 'filter-bugs' ).'
					</td>
					<td>

					</td>
				</tr>
				<tr>
					<td>
						<label for="status">'.$words['indexFilter']['labelStatus'].'</label><br/>
						'.HtmlElements::Select( 'status[]', $optStatus, 'm rows-8', NULL, 'filter-bugs' ).'
					</td>
					<td>
						<label for="priority">'.$words['indexFilter']['labelPriority'].'</label><br/>
						'.HtmlElements::Select( 'priority[]', $optPriority, 'm rows-7', NULL, 'filter-bugs' ).'
					</td>
					<td>
						<label for="severity">'.$words['indexFilter']['labelSeverity'].'</label><br/>
						'.HtmlElements::Select( 'severity[]', $optSeverity, 'm rows-5', NULL, 'filter-bugs' ).'
					</td>
					<td>
						<label for="type">'.$words['indexFilter']['labelType'].'</label><br/>
						'.HtmlElements::Select( 'type[]', $optType, 'm rows-4', NULL, 'filter-bugs' ).'
					</td>
				</tr>
				<tr>
					<td>
						<label for="order">'.$words['indexFilter']['labelOrder'].'</label><br/>
						'.HtmlElements::Select( 'order', $optOrder, 'm rows-1', NULL, 'filter-bugs' ).'
					</td>
					<td>
						<label for="direction">'.$words['indexFilter']['labelDirection'].'</label><br/>
						'.HtmlElements::Select( 'direction', $optDirection, 'm', NULL, 'filter-bugs' ).'
					</td>
					<td>
						<label for="limit">'.$words['indexFilter']['labelLimit'].'</label><br/>
						'.HtmlElements::Input( 'limit', $limit, 'xs numeric' ).'
					</td>
					<td></td>
				</tr>
			</table>
			<div class="buttonbar">
				'.HtmlElements::Button( 'filter', $words['indexFilter']['buttonFilter'], 'button filter' ).'
				'.HtmlElements::LinkButton( './bug/filter/reset', $words['indexFilter']['buttonReset'], 'button reset' ).'
				'.HtmlElements::LinkButton( './bug/filter/mode/0', $words['indexFilter']['buttonCollapse'], 'button less' ).'
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
						'.HtmlElements::Input( 'title', $title, 'm' ).'
					</td>
					<td>
						<label for="username">'.$words['indexFilter']['labelBugId'].'</label><br/>
						'.HtmlElements::Input( 'bugId', $bugId, 'xs numeric' ).'
					</td>
					<td>
						<label for="status">'.$words['indexFilter']['labelProject'].'</label><br/>
						'.HtmlElements::Select( 'project', '<option>- alle -</option>', 'm', NULL, 'filter-bugs' ).'
					</td>
					<td></td>
				</tr>
				<tr>
					<td>
						<label for="order">'.$words['indexFilter']['labelOrder'].'</label><br/>
						'.HtmlElements::Select( 'order', $optOrder, 'm rows-1', NULL, 'filter-bugs' ).'
					</td>
					<td>
						<label for="direction">'.$words['indexFilter']['labelDirection'].'</label><br/>
						'.HtmlElements::Select( 'direction', $optDirection, 'm', NULL, 'filter-bugs' ).'
					</td>
					<td>
						<label for="limit">'.$words['indexFilter']['labelLimit'].'</label><br/>
						'.HtmlElements::Input( 'limit', $limit, 'xs numeric' ).'
					</td>
					<td></td>
				</tr>
			</table>
			<div class="buttonbar">
				'.HtmlElements::Button( 'filter', $words['indexFilter']['buttonFilter'], 'button filter' ).'
				'.HtmlElements::LinkButton( './bug/filter/reset', $words['indexFilter']['buttonReset'], 'button reset' ).'
				'.HtmlElements::LinkButton( './bug/filter/mode/1', $words['indexFilter']['buttonExpand'], 'button more' ).'
			</div>
		</fieldset>
	</form>
	';
	
}
?>