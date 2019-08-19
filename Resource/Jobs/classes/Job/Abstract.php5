<?php
class Job_Abstract{

	/**	@var	CMF_Hydrogen_Environment				$env		Environment object */
	protected $env;
	protected $logFile;
	protected $jobClass;
	protected $jobMethod;
	protected $jobModuleId;

	protected $commands			= array();
	protected $dryMode			= FALSE;
	protected $verbose			= FALSE;
	protected $parameters;

	protected $versionModule;
	protected $progress;

	/**	@var		Jobber								$manager		Job manager instance */
	protected $manager;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment			$env		Environment instance
	 *	@param		Jobber								$manager	Job manage instance
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment $env, $manager, $jobClassName = NULL, $jobModuleId = NULL ){
		$this->env			= $env;
		$this->manager		= $manager;
		$this->logFile		= $env->getConfig()->get( 'path.logs' ).'jobs.log';
		$this->parameters	= new ADT_List_Dictionary();
		$this->setJobClassName( $jobClassName );
		$this->setJobModuleId( $jobModuleId );
		$this->__onInit();
	}

	protected function __onInit(){
	}

	protected function getLogPrefix(){
		$label		= $this->jobClass;
		if( $this->jobMethod )
			$label	.= '.'.$this->jobMethod;
		return $label.': ';
	}

	public function noteArguments( $commands = array(), $parameters = array() ){
		$this->commands		= array_diff( $commands, array( 'dry', 'verbose' ) );
		$this->parameters	= new ADT_List_Dictionary( $parameters );
		$this->dryMode		= in_array( 'dry', (array) $commands );
		$this->verbose		= in_array( 'verbose', (array) $commands );
		return $this;
	}

	public function noteJob( $className, $jobName, $moduleId = NULL ){
		$this->setJobClassName( $className );
		$this->jobMethod	= $jobName;
		$this->setJobModuleId( $moduleId );
		return $this;
	}

	protected function log( $message ){
		$this->manager->log( $this->getLogPrefix().$message );
		return $this;
	}

	protected function logError( $message ){
		$this->manager->logError( $this->getLogPrefix().$message );
		return $this;
	}

	protected function logException( $exception ){
		$this->manager->logException( $exception );
		return $this;
	}

	public function out( $message = NULL ){
		print( $message."\n" );
		return $this;
	}

	protected function setJobClassName( $jobClassName ){
		$this->jobClass		= strlen( trim( $jobClassName ) ) ? $jobClassName : get_class( $this );
		return $this;
	}

	protected function setJobModuleId( $jobModuleId ){
		$this->jobModuleId		= strlen( trim( $jobModuleId ) ) ? $jobModuleId : NULL;
		$this->versionModule	= NULL;
		if( $this->jobModuleId && $this->env->getModules()->has( $this->jobModuleId ) ){
			$module	= $this->env->getModules()->get( $this->jobModuleId );
			$this->versionModule	= $module->versionInstalled;
		}
		return $this;
	}

	protected function showProgress( $count, $total, $sign = '.', $length = 60 ){
		if( class_exists( 'CLI_Output_Progress' ) ){
			if( $count === 0 ){
				$this->progress	= new CLI_Output_Progress();
				$this->progress->setTotal( $total )->start();
			}
			else if( $count === $total ){
				$this->progress->finish();
			}
			else{
				if( !$this->progress ){
					$this->progress	= new CLI_Output_Progress();
					$this->progress->setTotal( $total );
					$this->progress->start();
				}
				$this->progress->update( $count );
			}

		} else {
			echo $sign;
			if( $count % $length === 0 )
				echo str_pad( $count.'/'.$total, 18, " ", STR_PAD_LEFT ).PHP_EOL;
		}
		return $this;
	}

	protected function showErrors( $taskName, $errors ){
		if( is_array( $errors ) && count( $errors ) ){
			$this->out( 'Errors on '.$taskName.':' );
			foreach( $errors as $mailId => $message )
				$this->out( '- '.$mailId.': '.$message );
		}
		return $this;
	}
}
?>
