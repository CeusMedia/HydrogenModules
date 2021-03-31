<?php

function getRevision( $path = '' ){
	exec( 'svn info --xml '.$path, $a );
	$xml	= implode( "\n", $a );
	try {
		$parser	= new XML_DOM_Parser();
		$tree	= $parser->parse( $xml );
		$rev	= $tree->getChild( 'entry' )->getChild( 'commit' )->getAttribute( 'revision' );
		return (int) $rev;
	}
	catch( Exception $e ){
		return -1;
	}
}

function countCores(){
	exec( 'cat /proc/cpuinfo | grep processor | wc -l', $processors );
	return array_shift( $processors );
}

$diskTotal	= (double) disk_total_space( __DIR__ );
$diskFree	= (double) disk_free_space( __DIR__ );
$diskRatio	= round( $diskFree / $diskTotal * 100, 1 ); 

#$diskTotal	= 10;
#$diskFree	= 1.9;
$space		= min( 2, $diskFree / $diskTotal * 10 ) / 2;
$indicator	= new UI_HTML_Indicator();
$space		= $indicator->build( $space, 1 );

$configCMC	= parse_ini_file( CMC_PATH.'../cmClasses.ini', TRUE );
$versionCMC	= $configCMC['project']['version'];

$cores		= countCores();
$loads		= sys_getloadavg();
$load1		= array_shift( $loads );
$load1Relative	= 1 / ( 1 + $load1 / $cores );
$indicator	= new UI_HTML_Indicator();
$loadGraph	= $indicator->build( $load1Relative, 1 );

$panel	= '
<fieldset class="index-system">
	<legend class="info">Server</legend>
	<dl>
		<dt>CPU Load<div class="info-graph">'.$loadGraph.'</div></dt>
		<dd>'.$load1.' @ '.$cores.' cores</dd>
		<dt>Disk Space<div class="info-graph">'.$space.'</div></dt>
		<dd>'.$diskRatio.'% frei <small class="muted">('.Alg_UnitFormater::formatBytes( $diskFree, 1 ).' / '.Alg_UnitFormater::formatBytes( $diskTotal, 1 ).')</small></dd>
		<dt>Server Software</dt><dd>
			<ul>
				<li>'.$_SERVER['SERVER_SOFTWARE'].'</li>
				<li>PHP/'.phpversion().'</li>
				<li>cmClasses/'.$versionCMC.' <span class="small">(rev '.getRevision( CMC_PATH ).')</span></li>
				<li>cmFrameworks <span class="small">(rev '.getRevision( CMF_PATH ).')</span></li>
				<li>cmModules <span class="small">(rev '.getRevision( CMM_PATH ).')</span></li>
				<li>Hydra/'.$config->get( 'app.version' ).' <span class="small">(rev '.getRevision().')</span></li>
			</ul>
		</dd>
		<dt>Cache</dt><dd>
			<ul>
			</ul>
		</dd>
	</dl>
</fieldset>';
$env->clock->profiler->tick( 'Template: index/index - system' );
return $panel;
?>