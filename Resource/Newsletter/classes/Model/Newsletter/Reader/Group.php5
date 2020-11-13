<?php
/**
 *	...
 *	@category		...
 *	@package		...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2020 Ceus Media
 */
/**
 *	...
 *	@category		...
 *	@package		...
 *	@extends		CMF_Hydrogen_Model
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2020 Ceus Media
 */
class Model_Newsletter_Reader_Group extends CMF_Hydrogen_Model
{
	const STATUS_REMOVED		= -2;
	const STATUS_CANCELLED		= -1;
	const STATUS_ASSIGNED		= 0;
	const STATUS_REJOINED		= 1;

	const STATUSES				= array(
		self::STATUS_REMOVED,
		self::STATUS_CANCELLED,
		self::STATUS_ASSIGNED,
		self::STATUS_REJOINED,
	);

	protected $name		= 'newsletter_reader_groups';

	protected $columns	= array(
		'newsletterReaderGroupId',
		'newsletterReaderId',
		'newsletterGroupId',
		'status',
		'createdAt',
	);

	protected $primaryKey	= 'newsletterReaderGroupId';

	protected $indices		= array(
		'newsletterReaderId',
		'newsletterGroupId',
		'status',
	);

	protected $fetchMode	= PDO::FETCH_OBJ;
}
