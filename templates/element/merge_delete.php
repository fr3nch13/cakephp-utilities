<?php

declare(strict_types=1);

/**
 * @var \App\View\AppView $this
 * @var string $displayField
 * @var \Cake\ORM\Entity $sourceRecord
 * @var array<int, string> $records
 * @var array<int, mixed>|null $stats
 */

use Cake\Utility\Inflector;
?>

<div class="merge-delete">
    <div class="form">
        <h3><?php echo __('Old Record: {0}', [$sourceRecord->get($displayField)]); ?></h3>
        <?php echo $this->Form->create($sourceRecord); ?>
            <fieldset>
                <?php
                echo $this->Form->control('id', [
                    'label' => __('Record to merge to.'),
                    'type' => 'select',
                    'required' => true,
                    'options' => $records,
                ]);
                echo $this->Form->hidden('referer', [
                    'value' => $this->getRequest()->referer(),
                ]);
                ?>
            </fieldset>
            <?php echo $this->Form->button(__('Submit')); ?>
        <?php echo $this->Form->end(); ?>
    </div>
<?php if (isset($stats) && is_array($stats)) : ?>
    <div class="stats">
        <h3><?php echo __('Items that will be merged.'); ?></h3>
    <?php foreach ($stats as $stat): ?>
        <div class="stat">
            <span class="name"><?php echo $stat['name']; ?></span>
            <span class="count"><?php echo $stat['count']; ?></span>
        </div>
    <?php endforeach; // $stats ?>
    </div>
<?php endif; // $stats ?>
</div>
