<?php

use CeusMedia\HydrogenFramework\Logic\Shared;

class Logic_Newsletter_Import_Deduplication extends Shared
{
	protected Model_Newsletter_Reader $modelReader;
	protected Model_Newsletter_Reader_Group $modelReaderGroup;

	protected array $matchers = [
		'reader'	=> ['email', 'firstname', 'surname'],
		'address'	=> [],
	];

	public function findReader( Entity_Newsletter_Reader $reader ): int|string
	{
		$conditions	= [];
		foreach( $this->matchers['reader'] as $column )
			$conditions[$column] = $reader->get( $column );
		$readers	= $this->modelReader->getAll( $conditions );

		if( [] === $readers )
			return -4;
		if( count( $readers ) > 1 )
			return -3;

		$reader	= current( $readers );
		return match( $reader->status ){
			Model_Newsletter_Reader::STATUS_DEACTIVATED => -2,
			Model_Newsletter_Reader::STATUS_UNREGISTERED => -1,
			Model_Newsletter_Reader::STATUS_REGISTERED => 0,
			Model_Newsletter_Reader::STATUS_CONFIRMED => $reader->newsletterReaderId,
		};
	}

	public function findReaderGroup( Entity_Newsletter_Reader|int|string $reader, /*Entity_Newsletter_Group|*/int|string $group ): int|string
	{
		$readerId	= is_object( $reader ) ? $reader->newsletterReaderId : $reader;
		$groupId	= is_object( $group ) ? $group->newsletterGroupId : $group;
		$relation	= $this->modelReaderGroup->getByIndices( [
			'newsletterReaderId'	=> $readerId,
			'newsletterGroupId'		=> $groupId,
		] );
		if( NULL !== $relation )
			return $relation->newsletterReaderGroupId;
		return 0;
	}

	public function findReaderAddress( $readerId, Entity_Address $address ): int|string
	{
		return 0;
	}

	protected function __onInit(): void
	{
		$this->modelReader		= new Model_Newsletter_Reader( $this->env );
		$this->modelReaderGroup	= new Model_Newsletter_Reader_Group( $this->env );
	}
}