<?php
declare(strict_types=1);

use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\HydrogenFramework\Environment\Resource\Module\Definition as ModuleDefinition;

class Job_Server_Log_Request extends Job_Abstract
{
	public function import(): void
	{
		/** @var ModuleDefinition $module */
		$module	= $this->env->getModules()->get( 'Server_Log_Request' );
		if( !$module->config['active']->value )
			return;
		if( !$module->config['delay']->value )
			return;
		$this->importFromFileToDatabase( $module );
	}

	protected function importFromFileToDatabase( ModuleDefinition $module ): void
	{
		$fileName	= $module->config['file']->value;
		$filePath	= $this->env->getConfig()->get( 'path.logs' ).$fileName;
		foreach( FileReader::loadArray( $filePath ) as $line ){
			if( '' === trim( $line ) )
				continue;
			$data	= json_decode( $line );
			$model	= new Model_Log_Request( $this->env );
			$model->add( $data );
		}
	}
}