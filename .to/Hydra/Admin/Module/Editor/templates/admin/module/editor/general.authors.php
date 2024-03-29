<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$wf		= (object) $words['tab-general-authors'];

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconAuthor		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-user'] );

$list	= '<div class="alert alert-info former-hint">'.$wf->listNone.'</div>';
if( $module->authors ){
	$list	= [];
	foreach( $module->authors as $author ){
		$label	= $author->name;
		if( $author->email )
			$label	= HtmlTag::create( 'a', $label, ['href' => 'mailto:'.$author->email] );
		$url	= './admin/module/editor/removeAuthor/'.$moduleId.'/'.base64_encode( $author->name );
		$button	= HtmlElements::LinkButton( $url, '', 'button icon tiny remove', $wf->buttonRemoveConfirm );
		$list[]	= '<li class="author">'.$label.'<div style="float: right">'.$button.'</div></li>';
	}
	$list	= '<ul class="general-info">'.join( $list ).'</ul>';
}

$buttonOpen		= '<button type="button" class="btn btn-mini btn-success form-trigger former-button former-iconed former-tiny former-add">'.$iconAdd.'</button>';
$hideForMore	= ' style="display: none"';


$panelAuthors	= '
<form action="./admin/module/editor/addAuthor/'.$module->id.'" method="post">
	<div class="">
		<h4 class="former-icon former-author">'.$iconAuthor.'&nbsp;'.$wf->legend.'</h4>
		'.$list.'
		<ul class="input"'.$hideForMore.'>
			<li>
				<label for="input_name" class="mandatory">'.$wf->labelName.'</label><br/>
				<input type="text" name="name" id="input_name" class="max mandatory" value=""/>
			</li>
			<li>
				<label for="input_email">'.$wf->labelEmail.'</label><br/>
				<input type="text" name="email" id="input_email" class="max" value=""/>
			</li>
		</ul>
		<div class="buttonbar"'.$hideForMore.'>
			'.HtmlElements::Button( 'addAuthor', $wf->buttonAdd, 'button add' ).'
		</div>
		'.$buttonOpen.'
	</div>
</form>';
return $panelAuthors;
