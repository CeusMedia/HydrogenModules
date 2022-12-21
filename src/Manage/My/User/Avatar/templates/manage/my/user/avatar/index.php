<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w			= (object) $words['index'];
$tabs		= View_Manage_My_User::renderTabs( $env, 'avatar' );

$iconFile	= HtmlTag::create( 'i', '', ['class' => 'icon-folder-open icon-white'] );

$helperUpload	= new View_Helper_Input_File( $env );
$helperUpload->setName( 'upload' );
$helperUpload->setLabel( $iconFile );
$helperUpload->setRequired( TRUE );

$maxSize	= Alg_UnitParser::parse( $moduleConfig->get( 'image.upload.maxFileSize' ), 'M' );
$maxSize	= Logic_Upload::getMaxUploadSize( ['config' => $maxSize] );
$maxSize	= Alg_UnitFormater::formatBytes( $maxSize );

$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'icon-trash icon-white'] );
$imageAvatar	= '';
$buttonRemove	= '';
if( $avatar ){
	$imageAvatar	= View_Helper_UserAvatar::renderStatic( $env, $user, 256 );
	$imageAvatar	= '<div class="thumbnail" style="max-width: 256px">'.$imageAvatar.'</div>';
	$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, array(
		'href'	=> './manage/my/user/avatar/remove',
		'class'	=> 'btn btn-inverse btn-small'
	) );
}

extract( $view->populateTexts( ['top', 'bottom', 'info.avatar', 'info.gravatar'], 'html/manage/my/user/avatar/', array(
	'maxFileSize'	=> $maxSize,
	'minImageSize'	=> $moduleConfig->get( 'image.upload.minSize' ),
) ) );

$panelAvatar	= '';
if( $moduleConfig->get( 'use.avatar' ) ){
	$panelAvatar	= '
		<div class="content-panel">
			<h4>Avatar</h4>
			<div class="content-panel-inner">
				<form action="./manage/my/user/avatar/upload" method="post" enctype="multipart/form-data">
					<div class="row-fluid">
						<div class="span6">
							'.$textInfoAvatar.'
							<div class="row-fluid">
								<div class="span12">
									<label for="input_upload">'.$w->labelUpload.'</label>
									'.$helperUpload->render().'
								</div>
							</div>
						</div>
						<div class="span4 offset1" style="text-align: center">
							'.$imageAvatar.'
							'.$buttonRemove.'
						</div>
					</div>
					<div class="buttonbar">
						<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
					</div>
				</form>
			</div>
		</div>';
}
$panelGravatar	= '';
if( $moduleConfig->get( 'use.gravatar' ) && $env->getModules()->has( 'UI_Helper_Gravatar' ) ){
	$gravatar	= new View_Helper_Gravatar( $env );
	$gravatar->setUser( $user );
	$gravatar->setSize( 256 );
	$gravatar	= $gravatar->render();

	$panelGravatar	= '
		<div class="content-panel content-panel-info">
			<h4>Gravatar</h4>
			<div class="content-panel-inner">
				<div style="float: right; padding: 0 1em 2em 2em; width: 50%; max-width: 256px">
					<div class="thumbnail" data-style="max-width: 128px">'.$gravatar.'</div>
				</div>
				'.$textInfoGravatar.'
				<div class="clearfix"></div>
			</div>
		</div>
';
}

if( $moduleConfig->get( 'use.avatar' ) && $moduleConfig->get( 'use.gravatar' ) )
	$content	= $tabs.'
	<div class="row-fluid">
		<div class="span8">
			'.$panelAvatar.'
		</div>
		<div class="span4">
			'.$panelGravatar.'
		</div>
	</div>';
else {
	$content	= $tabs.'
	<div class="row-fluid">
		<div class="span12">
			'.$panelAvatar.'
			'.$panelGravatar.'
		</div>
	</div>';
}

return $content;
?>
