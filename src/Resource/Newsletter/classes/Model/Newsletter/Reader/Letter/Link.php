<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2025 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2025 Ceus Media (https://ceusmedia.de/)
 */
class Model_Newsletter_Reader_Letter_Link extends Model
{
	protected string $name			= 'newsletter_reader_letter_links';

	protected array $columns		= [
		'newsletterReaderLetterLinkId',
		'newsletterReaderLetterId',
		'newsletterReaderId',
		'newsletterLinkId',
		'newsletterId',
		'timestamp',
	];

	protected string $primaryKey	= 'newsletterReaderLetterLinkId';

	protected array $indices		= [
		'newsletterReaderLetterId',
		'newsletterReaderId',
		'newsletterLinkId',
		'newsletterId',
		'timestamp',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}
