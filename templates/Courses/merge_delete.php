<?php

declare(strict_types=1);

/**
 * @var \App\View\AppView $this
 * @var string $displayField
 * @var \CAke\ORM\Entity $sourceRecord
 * @var array<int, string> $records
 */

$this->assign('title', __('Merge/Delete a Record'));

echo $this->element('Fr3nch13/Utilities.merge_delete', [
    'sourceRecord' => $sourceRecord,
    'displayField' => $displayField,
    'records' => $records,
    'stats' => $stats,
]);
