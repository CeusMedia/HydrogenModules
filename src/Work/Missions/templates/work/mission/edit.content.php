<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array $words */
/** @var Entity_Mission $mission */
/** @var string $format */

/*  THIS TEMPLATE IS USED FOR MARKDOWN - NOT FOR HTML  */
if( 'HTML' === strtoupper( $format ) )
	return '';

$mode	= 'splitted';
$mode	= 'tabbed';

$w	= (object) $words['edit-content'];

$linkMarkdown	= HtmlTag::create( 'a', $w->hintMarkdownLabel, [
	'href'		=> $w->hintMarkdownLink,
	'target'	=> '_blank',
] );

if( 'tabbed' === $mode ){
	$hintMarkdown	= HtmlTag::create( 'small', sprintf( $w->hintMarkdown, $linkMarkdown ), ['class' => 'muted'] );
	$panelContentTabbed	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<ul class="nav nav-tabs">
			<li class="active"><a href="#tab1" data-toggle="tab">'.$w->tabEdit.'</a></li>
			<li><a href="#tab2" data-toggle="tab">'.$w->tabView.'</a></li>
			<li><a href="#tab3" data-toggle="tab">'.$w->tabConvert.'</a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="tab1">
				<div id="mirror-container">
					'.HtmlTag::create( 'textarea', htmlentities( $mission->content, ENT_QUOTES, 'UTF-8' ), [
						'id'		=> "input_content",
						'name'		=> "content",
						'rows'		=> "22",
						'class'		=> "span12 -max -cmGrowText -cmClearInput",
						'data-ace-flag-font-size'	=> "16",
						'data-ace-option-show-gutter'	=> "false"
					] ).'
					<p>
						'.$hintMarkdown.'
					</p>
				</div>
			</div>
			<div class="tab-pane" id="tab2">
				<div id="content-editor">
					<div id="mission-content-html"></div>
				</div>
			</div>
			<div class="tab-pane" id="tab3">
				<div class="row-fluid">
					<div class="span6">
						<p>
							'.sprintf( $w->convertFormat, '<a href="'.$w->hintMarkdownLink.'" target="_blank">Markdown</a>' ).'
						</p>
						<p>
							'.$w->convertDescription.'
						</p>
					</div>
					<div class="span6">
						<div class="alert alert-danger">
							<b>'.$w->convertWarningLabel.' </b>'.$w->convertWarning.'<br/>
						</div>
						<p>'.
							HtmlTag::create( 'a', $w->buttonConvertToHtml, [
								'href'	=> './work/mission/convertContent/'.$mission->missionId.'/markdown/html',
								'class'	=> 'btn btn-inverse',
							] ).'
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>';
	return $panelContentTabbed;
}

$panelContentSplit	= '
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel content-panel-form">
			<div class="content-panel-inner">
				<form>
					<h3>'.$w->tabEdit.'</h3>
					<div id="work-missions-loader" style=""><em class="muted">'.$w->messageLoading.'</em></div>
					'.HtmlTag::create( 'textarea', htmlentities( $mission->content, ENT_QUOTES, 'utf-8' ), [
						'id'		=> 'input_content',
						'name'		=> 'content',
						'rows'		=> 4,
						'class'		=> 'span12 -max -cmGrowText -cmClearInput',
						'style'		=> 'visibility: hidden;',
					] ).'
					<p>
						<span class="muted">'.sprintf( $w->hintMarkdown, $linkMarkdown ).'</span>
					</p>
				</form>
			</div>
		</div>
	</div>
	<div class="span6">
		<div class="content-panel content-panel-form">
			<div class="content-panel-inner">
				<h3>'.$w->headingContent.'</h3>
				<div id="content-editor">
					<div id="mission-content-html"></div>
				</div>
			</div>
		</div>
	</div>
</div>';
return $panelContentSplit;
