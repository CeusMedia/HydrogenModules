<?php
class Job_Test extends Job_Abstract{

	public function reflect( $arg1 = NULL ){
		$this->out( 'Your argument(s): '.json_encode( func_get_args() ) );
	}

	public function date( $format = "r" ){
		$this->out( date( $format ) );
	}
}
?>
