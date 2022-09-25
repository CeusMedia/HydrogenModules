<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$wf		= (object) $words['tab-general-companies'];

$iconAdd		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconCompany	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-building' ) );

$list	= '<div class="alert alert-info former-hint">'.$wf->listNone.'</div>';
if( $module->companies ){
	$list	= [];
	foreach( $module->companies as $company ){
		$label	= $company->name;
		if( $company->site )
			$label	= HtmlTag::create( 'a', $label, array( 'href' => $company->site ) );
		$url	= './admin/module/editor/removeCompany/'.$moduleId.'/'.base64_encode( $company->name );
		$button	= HtmlElements::LinkButton( $url, '', 'button icon tiny remove', $wf->buttonRemoveConfirm );
		$list[]	= '<li class="company">'.$label.'<div style="float: right">'.$button.'</div></li>';
	}
	$list	= '<ul class="general-info">'.join( $list ).'</ul>';
}

$buttonOpen		= '<button type="button" class="btn btn-mini btn-success form-trigger former-button former-iconed former-tiny former-add">'.$iconAdd.'</button>';
$hideForMore	= ' style="display: none"';

$panelCompanies	= '
<form action="./admin/module/editor/addCompany/'.$module->id.'" method="post">
	<div>
		<h4 class="former-icon former-company">'.$iconCompany.'&nbsp;'.$wf->legend.'</h4>
		'.$list.'
		<ul class="input"'.$hideForMore.'>
			<li>
				<label for="input_name" class="mandatory">'.$wf->labelName.'</label><br/>
				<input type="text" name="name" id="input_name" class="max mandatory" value=""/>
			</li>
			<li>
				<label for="input_site">'.$wf->labelSite.'</label><br/>
				<input type="text" name="site" id="input_site" class="max" value=""/>
			</li>
		</ul>
		<div class="buttonbar"'.$hideForMore.'>
			'.HtmlElements::Button( 'addCompany', $wf->buttonAdd, 'button add' ).'
		</div>
		'.$buttonOpen.'
	</div>
</form>
';
return $panelCompanies;
?>
