<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

/** @var array<string,array<string,string>> $words */
$wg	= (object) $words['general'];
$wf	= (object) $words['fields'];
$wm	= (object) $words['modal.editEdge'];

$optGraph	= [];
foreach( $graphs as $graph )
	$optGraph[$graph->graphId]	= $graph->title;
$optGraph	= HtmlElements::Options( $optGraph );

$optNode	= [];
foreach( $nodes as $node )
	$optNode[$node->nodeId]	= $node->label ?: $node->ID;
$optFromNode	= HtmlElements::Options( $optNode, $edge->fromNodeId );
$optToNode		= HtmlElements::Options( $optNode, $edge->toNodeId );

$panelDetails	= '
<div class="content-panel">
	<h4>'.$wm->headingDetails.'</h4>
	<div class="content-panel-inner">
<!--		<div class="row-fluid">
			<div class="span12">
				<label for="input_graphId">Graph</label>
				<select name="graphId" id="input_graphId" class="span12">'.$optGraph.'</select>
			</div>
		</div>-->
		<div class="row-fluid">
			<div class="span6">
				<label for="input_fromNodeId">'.$wf->labelFromNode.'</label>
				<select name="fromNodeId" id="input_fromNodeId" class="span12">'.$optFromNode.'</select>
			</div>
			<div class="span6">
				<label for="input_toNodeId">'.$wf->labelToNode.'</label>
				<select name="toNodeId" id="input_toNodeId" class="span12">'.$optToNode.'</select>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<label for="input_label">'.$wf->labelLabel.' <small class="muted">'.$wf->hintShouldBeUnique.'</small></label>
				<input type="text" name="label" id="input_label" class="span12" value="'.htmlentities( $edge->label, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
	</div>
</div>';

$panelStyle	= '
<div class="content-panel">
	<h4>'.$wm->headingStyle.'</h4>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span3">
				<label for="input_color">'.$wf->labelLineColor.' <small class="muted"></small></label>
				<input type="text" name="color" id="input_color" class="span12" value="'.htmlentities( $edge->color, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span3">
				<label for="input_fontcolor">'.$wf->labelFontColor.' <small class="muted"></small></label>
				<input type="text" name="fontcolor" id="input_fontcolor" class="span12" value="'.htmlentities( $edge->fontcolor, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span3">
				<label for="input_fontsize">'.$wf->labelFontSize.' <small class="muted"></small></label>
				<input type="text" name="fontsize" id="input_fontsize" class="span12" value="'.htmlentities( $edge->fontsize, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span3">
				<label for="input_arrowhead">'.$wf->labelArrowHead.' <small class="muted"></small></label>
				<input type="text" name="arrowhead" id="input_arrowhead" class="span12" value="'.htmlentities( $edge->arrowhead, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span3">
				<label for="input_arrowsize">'.$wf->labelArrowSize.' <small class="muted"></small></label>
				<input type="text" name="arrowsize" id="input_arrowsize" class="span9" value="'.htmlentities( $edge->arrowsize, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
		</div>
	</div>
</div>';

$nodeIndex	= [];
foreach( $nodes as $node )
	$nodeIndex[$node->nodeId]	= $node->label ?: '<tt><em>'.$node->ID.'</em></tt>';

$label	= $nodeIndex[$edge->fromNodeId].'&nbsp;&rarr;&nbsp;'.$nodeIndex[$edge->toNodeId];
if( $edge->label )
	$label	= $edge->label.'&nbsp;<small class="muted">('.$label.')</small>';

return '
<form action="./work/graph/editEdge/'.( $nodeId ? $edgeId.'/'.$nodeId : $edgeId ).'" method="post">
	<div id="modalEditEdge" class="modal hide not-fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><span class="muted">'.$wg->prefixEdge.'</span>'.$label.'</h3>
		</div>
		<div class="modal-body">
			<div class="row-fluid">
				<div class="span12">
					'.$panelDetails.'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					'.$panelStyle.'
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<a class="btn" data-dismiss="modal">'.$wg->buttonClose.'</a>
			<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> '.$wg->buttonSave.'</button>
		</div>
	</div>
</form>
';
