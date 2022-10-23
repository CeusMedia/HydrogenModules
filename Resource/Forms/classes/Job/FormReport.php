<?php
class Job_FormReport extends Job_Abstract
{
	public function reportFailedTranfers()
	{
		$query	= <<<EOT
SELECT
    FROM_UNIXTIME(ff.createdAt) AS created,
    FROM_UNIXTIME(fft_latest.timestamp) AS failed,
	ff.fillId AS fillId,
-- 	f.formId,
-- 	ff.fillId AS formFillId,
-- 	fft_latest.transferRuleId,
-- 	ftt.transferTargetId,
-- 	fft_latest.fillTransferId,
-- 	f.formStatus,
-- 	ff.status AS formfillStatus,
-- 	fft_latest.fillTransferStatus,
    f.title AS formTitle,
-- 	ff.data,
    ftr.transferRuleTitle,
    ftt.transferTargetTitle,
    fft_latest.fillTransferMessage
FROM
	form_fills AS ff,
	(	SELECT
			fft.fillId,
			fft.formFillTransferId AS fillTransferId,
			fft.formTransferRuleId AS transferRuleId,
			MAX(fft.createdAt) AS timestamp,
			fft.status AS fillTransferStatus,
			fft.message AS fillTransferMessage
		FROM form_fill_transfers AS fft
		WHERE fft.status IN (2,3)
		GROUP BY fillId
	) AS fft_latest,
	(	SELECT
			formId,
            status AS formStatus,
            title
		FROM forms
-- 		WHERE status IN (1)
	) AS f,
	(	SELECT
			formTransferRuleId AS transferRuleId,
			formTransferTargetId AS transferTargetId,
            title as transferRuleTitle
		FROM form_transfer_rules
	) AS ftr,
	(	SELECT
			formTransferTargetId as transferTargetId,
            title AS transferTargetTitle
		FROM form_transfer_targets
	) AS ftt
WHERE
	ff.fillId = fft_latest.fillId AND
	ff.formId = f.formId AND
	fft_latest.transferRuleId = ftr.transferRuleId AND
    ftr.transferTargetId = ftt.transferTargetId AND
	ff.status = 1 AND
    ff.createdAt > UNIX_TIMESTAMP(DATE_SUB(now(), INTERVAL 1 WEEK)) AND
	1
ORDER BY ff.createdAt DESC
-- LIMIT 0, 10
;
EOT;

		print($query);
		$dbc	= $this->env->getDatabase();
		$result	= $dbc->exec( $query );
		var_export( $result );
	}
}
