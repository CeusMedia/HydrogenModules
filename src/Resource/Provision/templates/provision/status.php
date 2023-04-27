<?php

$content	= '----';

if( $data->code == 2 ){
	$content	= $view->loadContentFile( 'html/provision/status/active.html', array(
		'key'			=> $data->active,
		'serverUrl'		=> $serverUrl,
		'keyEnd'		=> date( 'd.m.Y H:i:s', $data->active->endsAt ),
		'keyEndDate'	=> date( 'd.m.Y', $data->active->endsAt ),
		'keyEndTime'	=> date( 'H:i:s', $data->active->endsAt ),
	) );
}
if( $data->code == -1 ){
	$content	= $view->loadContentFile( 'html/provision/status/outdated.html', array(
		'key'			=> $data->outdated,
		'serverUrl'		=> $serverUrl,
		'keyEnd'		=> date( 'd.m.Y H:i:s', $data->outdated->endsAt ),
		'keyEndDate'	=> date( 'd.m.Y', $data->outdated->endsAt ),
		'keyEndTime'	=> date( 'H:i:s', $data->outdated->endsAt ),
	) );
}
if( $data->code == 0 ){
	$content	= $view->loadContentFile( 'html/provision/status/none.html', [
		'serverUrl'		=> $serverUrl.'manage/my/license/add/'.$productId,
	] );
}

return '
<h3><span class="muted">Zugangsbedingungen: </span>Status</h3>

'.$content.'
<hr/>
<xmp>Status: '.$status.'</xmp>
<xmp>License: '.$registerLicense.'</xmp>
<pre>Data: '.print_m( $data, NULL, NULL, TRUE ).'</pre>
';
