<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var View_Manage_Group $view */
/** @var array<string,array<string|int,string|int>> $words */
/** @var object $group */
/** @var int $userCount */

$optType	= [];
foreach( $words['types'] as $key => $label ){
	$selected		= $key == $group->type;
	$class			= 'group-type type'.$key;
	$optType[]	= HtmlElements::Option( (string) $key, $label, $selected, FALSE, $class );
}
$optType	= join( $optType );

$iconCancel		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconSave		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-check'] );
$iconAdd		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-plus'] );
$iconRemove		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-remove'] );
$iconSearch		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-magnifying-class'] );

$panelEdit	= '
<div class="content-panel content-panel-form">
	<h3>'.$words['edit']['heading'].'</h3>
	<div class="content-panel-inner">
		<form name="editGroup" action="./manage/group/edit/'.$group->groupId.'" method="post">
			<div class="row-fluid">
				<div class="span6">
					<label for="title">'.$words['edit']['labelTitle'].'</label>
					'.HtmlElements::Input( 'title', $group->title, 'span12' ).'
				</div>
				<div class="span3">
					<label for="access">'.$words['edit']['labelType'].'</label>
					'.HtmlElements::Select( 'type', $optType , 'span12' ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="description">'.$words['edit']['labelDescription'].'</label>
			<!--		'.HtmlElements::Textarea( 'description', $group->description, 'xl-l' ).'-->
					'.HtmlTag::create( 'textarea', $group->description, ['class' => 'span12', 'name' => 'description', 'rows' => 4] ).'
				</div>
			</div>
			<div class="buttonbar">
				<div class="btn-toolbar">
					'.HtmlElements::LinkButton( './manage/group', $iconCancel.' '.$words['edit']['buttonCancel'], 'btn btn-small' ).'
					'.HtmlElements::Button( 'saveGroup', $iconSave.' '.$words['edit']['buttonSave'], 'btn btn-primary' ).'
					&nbsp;&nbsp;|&nbsp;&nbsp;
					'.HtmlElements::LinkButton( './manage/group/remove/'.$group->groupId, $iconRemove.' '.$words['edit']['buttonRemove'], 'btn btn-small btn-danger', 'Wirklich?' ).'
					&nbsp;&nbsp;|&nbsp;&nbsp;
					'.HtmlElements::LinkButton( './manage/user/add?groupId='.$group->groupId, $iconAdd.' '.$words['edit']['buttonAddUser'], 'btn btn-info btn-small' ).'
					'.HtmlElements::LinkButton( './manage/user/filter?groupId='.$group->groupId, $iconSearch.' '.$words['edit']['buttonFilter'], 'btn btn-small' ).'
				</div>
			</div>
		</form>
	</div>
</div>
';

$panelRights	= $view->loadTemplateFile( 'manage/group/edit.rights.php' );
//$panelInfo		= $view->loadTemplateFile( 'manage/group/edit.info.php' );

$w				= (object) $words['info'];
$helperTime	= new View_Helper_TimePhraser( $env );
$createdAt		= $helperTime->convert( $group->createdAt, TRUE, $w->timePhrasePrefix, $w->timePhraseSuffix );
$modifiedAt		= $group->modifiedAt ? 'vor '.$helperTime->convert( $group->modifiedAt, TRUE ) : '-';
$panelInfo		= '
<div class="content-panel content-panel-info">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<dl class="not-dl-horizontal">
			<dt>'.$w->labelUserCount.'</dt>
			<dd>'.$userCount.'</dd>
			<dt>'.$w->labelCreatedAt.'</dt>
			<dd>'.$createdAt.'</dd>
			<dt>'.$w->labelModifiedAt.'</dt>
			<dd>'.$modifiedAt.'</dd>
		</dl>
	</div>
</div>';

extract( $view->populateTexts( ['index.top', 'index.bottom'], 'html/manage/group/' ) );

return $textIndexTop.'
<!--<h2><span class="muted">Rolle</span> '.$group->title.'</h2>-->
<div class="row-fluid">
	<div class="span9">
		'.$panelEdit.'
	</div>
	<div class="span3">
		'.$panelInfo.'
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		'.$panelRights.'
	</div>
</div>'.$textIndexBottom;
