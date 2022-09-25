<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconFilter		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search' ) );
$iconReset		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search-minus' ) );

$buttonSubmit	= HtmlTag::create( 'button', $iconFilter.' filtern', array(
	'type'	=> 'submit',
	'name'	=> 'filter',
	'class'	=> 'btn btn-small btn-info'
) );
$buttonReset	= HtmlTag::create( 'a', $iconReset.'&nbsp;leeren', array(
	'href'	=> './manage/form/block/filter/reset',
	'class'	=> 'btn btn-small btn-inverse'
) );

$optIdentifier	= array( '' => '- alle -');
foreach( $identifiers as $identifier )
	$optIdentifier[$identifier]	= $identifier;
$optIdentifier	= UI_HTML_Elements::Options( $optIdentifier, $filters->get( 'identifier' ) );

return '
<div class="content-panel">
	<h3>Filter</h3>
	<div class="content-panel-inner">
		<form action="./manage/form/block/filter" method="post">
			<div class="row-fluid">
				<div class="span4">
					<label for="input_blockId">ID</label>
					<input type="text" name="blockId" id="input_blockId" class="span12" value="'.htmlentities( $filters->get( 'blockId' ), ENT_QUOTES, 'utf-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">Titel <small class="muted">(ungefähr)</small></label>
					<input type="text"  name="title" id="input_title" class="span12" value="'.htmlentities( $filters->get( 'title' ), ENT_QUOTES, 'utf-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_identifier">Shortcode <small class="muted">(ungefähr)</small></label>
					<input type="text" name="identifier" id="input_identifier" class="span12" value="'.htmlentities( $filters->get( 'identifier' ), ENT_QUOTES, 'utf-8' ).'"/>
				</div>
			</div>
			<div class="buttonbar">
				<div class="btn-group">
					'.$buttonSubmit.'
					'.$buttonReset.'
				</div>
			</div>
		</form>
	</div>
</div>';
