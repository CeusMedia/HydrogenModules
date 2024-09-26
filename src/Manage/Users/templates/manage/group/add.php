<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var View_Manage_Group $view */
/** @var array<string,array<string|int,string|int>> $words */
/** @var object $group */

$optType	= [];
foreach( $words['types'] as $key => $label ){
	$selected	= $key == $group->get( 'type' );
	$class		= 'group-type type'.$group->get( 'access' );
	$optType[]	= HtmlElements::Option( (string) $key, $label, $selected, FALSE, $class );
}
$optType	= join( $optType );


extract( $view->populateTexts( ['add.top', 'add.bottom', 'add.right'], 'html/manage/group/' ) );


$panelAdd	= '
<div class="content-panel content-panel-form">
	<h3>'.$words['add']['heading'].'</h3>
	<div class="content-panel-inner">
		<form name="addGroup" action="./manage/group/add" method="post">
			<div class="row-fluid">
				<div class="span6">
					<label for="title">'.$words['add']['labelTitle'].'</label>
					'.HtmlElements::Input( 'title', $role->get( 'title' ), 'span12' ).'
				</div>
				<div class="span3">
					<label for="type">'.$words['add']['labelType'].'</label>
					'.HtmlElements::Select( 'type', $optType, 'span12' ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span">
					<label for="description">'.$words['add']['labelDescription'].'</label>
					'.HtmlTag::create( 'textarea', $role->get( 'description' ), ['class' => 'span12', 'name' => 'description', 'rows' => 4] ).'
				</div>
			</div>
			<div class="buttonbar">
				<div class="btn-toolbar">
					'.HtmlElements::LinkButton( './manage/role', '<i class="icon-arrow-left"></i> '.$words['add']['buttonCancel'], 'btn btn-small' ).'
					'.HtmlElements::Button( 'saveRole','<i class="icon-ok icon-white"></i> '. $words['add']['buttonSave'], 'btn btn-primary' ).'
				</div>
			</div>
		</form>
	</div>
</div>';

return $textAddTop.'
<div class="row-fluid">
	<div class="span9">
		'.$panelAdd.'
	</div>
	<div class="span3">
		'.$textAddRight.'
	</div>
</div>
'.$textAddBottom;
