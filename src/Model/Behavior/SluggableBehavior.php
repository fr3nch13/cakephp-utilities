<?php
declare(strict_types=1);

/**
 * Sluggable Behavior
 *
 * Creates slugs for records that don't have a uniue id.
 */

namespace Fr3nch13\Utilities\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\Utility\Text;

/**
 * Used mainly for when you're importing data from an external source like
 * an excel file, csv file, etc. where each record doesn't have an explicit unique id.
 */
class SluggableBehavior extends Behavior
{
    /**
     * Default config settings.
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [
        'field' => 'name',
        'slug' => 'slug',
        'updateSlug' => true,
    ];

    /**
     * Gets the Model callbacks this behavior is interested in.
     *
     * @return array<string, mixed>
     */
    public function implementedEvents(): array
    {
        return [
            'Model.beforeMarshal' => 'beforeMarshal',
            'Model.beforeSave' => 'beforeSave',
        ];
    }

    /**
     * Preparing the data
     *
     * @param \Cake\Event\Event<mixed> $event the event instance
     * @param \ArrayObject<string, mixed> $data data to be used in the marshal
     * @param \ArrayObject<string, mixed> $options options for the marshal
     * @return void
     */
    public function beforeMarshal(\Cake\Event\Event $event, \ArrayObject $data, \ArrayObject $options): void
    {
        $config = $this->getConfig();
        if (isset($data[$config['field']]) && $data[$config['field']]) {
            $data[$config['slug']] = $this->sluggableSlugify($data[$config['field']]);
        }
    }

    /**
     * Manipulate data before saving it.
     *
     * @param \Cake\Event\Event<mixed> $event The even tracking object.
     * @param \Cake\Datasource\EntityInterface $entity The entity to slug.
     * @param \ArrayObject<string, mixed> $options Passed options.
     * @return void
     */
    public function beforeSave(
        \Cake\Event\EventInterface $event,
        \Cake\Datasource\EntityInterface $entity,
        \ArrayObject $options
    ): void {
        $config = $this->getConfig();
        // if name is changed, update the slug
        if (
            ($config['updateSlug'] || $entity->isNew())
            && $entity->has($config['field'])
            && $entity->isDirty($config['field'])
            && !$entity->isDirty($config['slug'])
        ) {
            $entity->set($config['slug'], $this->sluggableSlugify($entity->get($config['field'])));
        }
    }

    /**
     * Manually regenerate the slug.
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity to slug.
     * @return \Cake\Datasource\EntityInterface The updated entity.
     */
    public function sluggableRegenSlug(\Cake\Datasource\EntityInterface $entity): \Cake\Datasource\EntityInterface
    {
        $config = $this->getConfig();
        $entity->set($config['slug'], $this->sluggableSlugify($entity->get($config['field'])));

        return $entity;
    }

    /**
     * Creates a slug from the input
     *
     * @param mixed $input The input to create the slug from
     * @return string the slugged string
     */
    public function sluggableSlugify($input = null): string
    {
        if ($input === null) {
            return '';
        }
        if (is_object($input)) {
            $input = (array)$input;
        }
        if (is_array($input)) {
            $input = implode(' ', $input);
        }
        if (is_int($input)) {
            $input = (string)$input;
        }
        $input = trim(strtolower($input));
        $input = Text::slug($input);
        $input = sha1($input);

        return $input;
    }
}
