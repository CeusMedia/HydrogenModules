<?php
$count	= (object) array(
	'sources'	=> 0,
	'total'		=> 0,
	'lines'		=> 0,
	'found'		=> 0,
	'todos'		=> 0
);
foreach( $data as $source ){
	$count->sources++;
	$count->total += $source['data']['total'];
	$count->lines += $source['data']['lines'];
	$count->found += $source['data']['found'];
	$count->todos += $source['data']['todos'];
}

$indicator	= new UI_HTML_Indicator();

$panelFacts	= '
<fieldset>
	<legend>Facts</legend>
	<dl>
		<dt>Files</dt><dd>'.$count->found.' / ' .$count->total.'</dd>
		<dt>Lines</dt><dd>'.$count->todos.' / ' .$count->lines.'</dd>
	</dl>
</fieldset>
';

$rows	= array();
foreach( $data as $source ){
#	print_m( array_keys( $source ) );
	$files	= array();
	foreach( $source['data']['files'] as $file ){
		$lines	= array();
		foreach( $file['lines'] as $nr => $line )
			$lines[]	= $nr.': '.$line;
		$lines		= $lines ? '<xmp>'.join( "\n", $lines ).'</xmp>' : ''; 
		$files[]		= '<h4>'.$file['fileName'].'</h4>'.$lines.'';
	}
	$rows[]	= '<h3>'.$source['title'].' <small style="font-size: 0.8em">('.$source['path'].')</small></h3>'.join( $files );
#	print_m( $source );
}
$panelList	= '
<fieldset>
	<legend>Todos</legend>
	'.UI_HTML_Tag::create( 'div', join( $rows ) ).'
</fieldset>';
return $panelFacts.$panelList;
return 'Dev:Todo:Index';
?>