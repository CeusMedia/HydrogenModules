<?php

$iconAdd	= HTML::Icon( 'plus', TRUE );
$iconSave	= HTML::Icon( 'ok', TRUE );

extract( $view->populateTexts( array( 'top', 'bottom', 'right' ), 'html/manage/my/company/edit/' ) );

//  --  PANEL: BRANCHES  --  //
$w				= (object) $words['branches'];
$listBranches	= '<div>'.$w->noEntries.'</div><br/>';
if( $company->branches ){
	$listBranches	= array();
	foreach( $company->branches as $branch ){
		$url	= './manage/my/company/branch/edit/'.$branch->branchId;
		$listBranches[]	= HTML::Li( HTML::Link( $url, $branch->title ), 'branch' );
	}
	$listBranches	= HTML::UlClass( 'list-branches', $listBranches );
}
$panelBranches	= '
<div class="content-panel">
	<h3>'.$w->legend.'</h3>
	<div class="content-panel-inner">
		'.$listBranches.'
		'.HTML::Buttons( HTML::LinkButton( './manage/my/company/branch/add/'.$company->companyId, $iconAdd.' '.$w->buttonAdd, 'btn btn-small btn-primary' ) ).'
	</div>
</div>';

//  --  PANEL: USERS  --  //
$w			= (object) $words['users'];
$listUsers	= '<div>'.$w->noEntries.'</div><br/>';
if( $company->users ){
	$listUsers	= array();
	foreach( $company->users as $user ){
		$label		= $user->username;
		$salutation	= $words['salutations'][(int) $user->salutation];
		if( $user->firstname || $user->surname )
			$label	.= ' ('.$salutation.$user->firstname.' '.$user->surname.')';
	 	$listUsers[]	= HTML::Li( $label, 'user' );
	}
	$listUsers	= HTML::UlClass( 'list-users', $listUsers );
}
$panelUsers	= '
<div class="content-panel">
	<h3>'.$w->legend.'</h3>
	<div class="content-panel-inner">
		'.$listUsers.'
		'.HTML::Buttons( HTML::LinkButton( './manage/my/user/add/'.$company->companyId, $iconAdd.' '.$w->buttonAdd, 'btn btn-small btn-primary', NULL, TRUE ) ).'
	</div>
</div>';

//  --  PANEL: EDIT  --  //
$w	= (object) $words['edit'];
$panelEdit	= '
<div class="content-panel">
	<h3>'.$w->legend.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/my/company/edit/'.$company->companyId.'" method="post">
			<div class="row-fluid">
				<div class="span6">
					'.HTML::Label( 'title', $w->labelTitle, 'mandatory' ).'
					'.HTML::Input( 'title', $company->title, 'span12 mandatory' ).'
				</div>
				<div class="span6">
					'.HTML::Label( 'sector', $w->labelSector ).'
					'.HTML::Input( 'sector', $company->sector, 'span12' ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span2">
					'.HTML::Label( 'postcode', $w->labelPostcode, 'mandatory' ).'
					'.HTML::Input( 'postcode', $company->postcode, 'span12 mandatory' ).'
				</div>
				<div class="span4">
					'.HTML::Label( 'city', $w->labelCity, 'mandatory' ).'
					'.HTML::Input( 'city', $company->city, 'span12 mandatory' ).'
				</div>
				<div class="span4">
					'.HTML::Label( 'street', $w->labelStreet, 'mandatory' ).'
					'.HTML::Input( 'street', $company->street, 'span12 mandatory' ).'
				</div>
				<div class="span2">
					'.HTML::Label( 'number', $w->labelNumber, 'mandatory' ).'
					'.HTML::Input( 'number', $company->number, 'span12 mandatory' ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					'.HTML::Label( 'url', $w->labelUrl ).'
					'.HTML::Input( 'url', $company->url, 'span12' ).'
				</div>
				<div class="span3">
					'.HTML::Label( 'phone', $w->labelPhone ).'
					'.HTML::Input( 'phone', $company->phone, 'span12' ).'
				</div>
				<div class="span3">
					'.HTML::Label( 'fax', $w->labelFax ).'
					'.HTML::Input( 'fax', $company->fax, 'span12' ).'
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-success">'.$iconSave.'&nbsp;'.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>';

$panelLogo	= $view->loadTemplateFile( 'manage/my/company/edit.logo.php' );

return '
<div class="row-fluid">
	<div class="span8">
		'.$panelEdit.'
		<div class="row-fluid">
			<div class="span7">
				'.$panelBranches.'
			</div>
			<div class="span5">
				'.$panelUsers.'
			</div>
		</div>
	</div>
	<div class="span4">
		'.$panelLogo.'
		'.$textRight.'
	</div>
</div>';
?>
