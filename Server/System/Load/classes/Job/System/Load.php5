<?php
class Job_System_Load extends Job_Abstract{

	public function __onInit(){
		$this->options	= $this->env->getConfig()->getAll( 'module.system_server_load.', TRUE );
	}

	public function note(){
		if( !class_exists( 'RRDCreator' ) )
			return;
		$fileStore	= 'load.rrd';
		$fileImage	= 'load.png';
		if( !file_exists( $fileStore ) ){
			$store	= new RRDCreator( $fileStore, "now -10d", 1 );
			$store->addDataSource("load1m:COUNTER:600:U:U");
			$store->addDataSource("load5m:COUNTER:600:U:U");
			$store->addDataSource("load15m:COUNTER:600:U:U");
			$store->addArchive("AVERAGE:0.5:1:24");
			$store->addArchive("AVERAGE:0.5:6:10");
			$store->save();
		}
		$loads	= sys_getloadavg();
		$store	= new RRDUpdater( $fileStore );
		$store->update( array( "load" => $loads[0] * 100 ), time() );
		$store->update( array( "load" => $loads[1] * 100 ), time() );
		$store->update( array( "load" => $loads[2] * 100 ), time() );

		$renderer = new RRDGraph( $fileImage );
		$renderer->setOptions(
			array(
				"--start" => time() - 3600,
				"--end" => time(),
				"--vertical-label" => "%",
				"ABC:myspeed=$fileStore:load1m:AVERAGE",
				"BCD:myspeed=$fileStore:load5m:AVERAGE",
				"DEF:myspeed=$fileStore:load15m:AVERAGE",
				"CDEF:realspeed=myspeed,0.01,*",
				"LINE2:realspeed#FF0000"
			)
		);
		$renderer->save();
	}

	public function test(){

	}

}
 ?>
