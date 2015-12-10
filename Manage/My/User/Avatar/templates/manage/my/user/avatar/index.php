<?php
$w			= (object) $words['index'];
$tabs		= View_Manage_My_User::renderTabs( $env, 'avatar' );

$gravatar	= new View_Helper_Gravatar( $env );
$gravatar->setUser( $user );
$gravatar->setSize( 256 );
$gravatar	= $gravatar->render();

$iconFile	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-folder-open icon-white' ) );

$upload		= View_Helper_Input_File::render( 'upload', $iconFile, TRUE );


$maxSize	= Alg_UnitParser::parse( $config->get( 'maxSize' ), 'M' );
$maxSize	= Logic_Upload::getMaxUploadSize( array( 'config' => $maxSize ) );
$maxSize	= Alg_UnitFormater::formatBytes( $maxSize );

$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-trash icon-white' ) );
$imageAvatar	= '';
$buttonRemove	= '';
if( $avatar ){
	$imageAvatar	= View_Helper_UserAvatar::renderStatic( $env, $user, 256 );
	$imageAvatar	= '<div class="thumbnail" style="max-width: 256px">'.$imageAvatar.'</div>';
	$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
		'href'	=> './manage/my/user/avatar/remove',
		'class'	=> 'btn btn-inverse btn-small'
	) );
}

return $tabs.'
<div class="row-fluid">
	<div class="span8">
		<div class="content-panel" id="manageMyUserAvatar">
			<div class="content-panel-inner">
				<h4>Avatar</h4>
				<form action="./manage/my/user/avatar/upload" method="post" enctype="multipart/form-data">
					<div class="row-fluid">
						<div class="span6">
							<p>
								Das automatische Gravatar-Bild kann aber auch hier mit einem eigenen Bild überschrieben werden.
							</p>
							<div class="alert alert-info">
								Maximale Dateigröße: '.$maxSize.'<br/>
								Minimale Auflösung: '.$config->get( 'minWidth' ).' x '.$config->get( 'minHeight' ).' Pixel<br/>
							</div>
							<div class="row-fluid">
								<div class="span12">
									<label for="input_upload">Bild-Datei</label>
									'.$upload.'
								</div>
							</div>
						</div>
						<div class="span4 offset1" style="text-align: center">
							'.$imageAvatar.'
							'.$buttonRemove.'
						</div>
					</div>
					<div class="buttonbar">
						<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;speichern</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="span4">
		<div class="content-panel content-panel-info" id="manageMyUserAvatar">
			<h4>Gravatar</h4>
			<div style="float: right; padding: 0 1em 2em 2em; width: 50%; max-width: 256px">
				<div class="thumbnail" data-style="max-width: 128px">'.$gravatar.'</div>
			</div>
			<p>
				Wenn bei <a href="https://gravatar.com/">Gravatar</a> für die verwendete E-Mail-Adresse ein Bild hinterlegt wurde, wird dieses hier automatisch eingebunden.
			</p>
			<p>
				Um dieses Bild zu verändern, muss man sich bei <a href="https://gravatar.com/">Gravatar</a> einloggen.
			</p>
			<div class="clearfix"></div>
		</div>
	</div>
</div>
</div></div></div></div></div></div></div>';
?>
