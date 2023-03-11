<?php
declare(strict_types=1);

/**
 * Check/Add Trait
 *
 * Common methods checking if a record exists, and adds the object if it doesn't.
 */

namespace Fr3nch13\Utilities\Model\Table;

use Fr3nch13\Utilities\Exception\MissingMethodException;

/**
 * Common methods checking if a record exists, and adds the object if it doesn't.
 *
 * @mixin \Fr3nch13\Utilities\Model\Behavior\SluggableBehavior
 */
trait CheckAddTrait
{
    /**
     * The default field name for the name field
     *
     * @var string
     */
    protected $checkAddName = 'name';

    /**
     * The default field name for the slug field
     *
     * @var string
     */
    protected $checkAddSlug = 'slug';

    /**
     * If we should use memory caching, always reach out to the database.
     * In benchmarking, it seems faster right now to disable memory caching.
     *
     * @var bool
     */
    public $useCaching = true;

    /**
     * Keeps track of the ids that have been created/checked previously.
     *
     * @var array<string, array<mixed>>
     */
    public $checkAddIds = [];

    /**
     * Hold the last record checked/created
     *
     * @var \Cake\ORM\Entity|null
     */
    protected $lastEntity = null;

    /**
     * Track the new count for each query
     *
     * @var int
     */
    public $checkAdd_new_cnt = 0;

    /**
     * Track the updated count for each query
     *
     * @var int
     */
    public $checkAdd_update_cnt = 0;

    /**
     * track successful cache read counts.
     *
     * @var int
     */
    public $cacheReadCnt = 0;

    /**
     * track successful database read counts.
     *
     * @var int
     */
    public $dbReadCnt = 0;

    /**
     * track successful database write counts.
     *
     * @var int
     */
    public $dbWriteCnt = 0;

    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initCheckAdd(array $config = []): void
    {
        if (!$this->behaviors()->has('Fr3nch13/Utilities.Sluggable')) {
            $this->addBehavior('Fr3nch13/Utilities.Sluggable', $config);
        }
    }

    /**
     * Sets the slug field, if different from the default
     *
     * @param string $field The field name to have the slug set to
     * @return void
     */
    public function setSlugField(string $field): void
    {
        if ($field) {
            $this->checkAddSlug = $field;
        }
        if ($this->behaviors()->has('Sluggable')) {
            $this->behaviors()
                ->get('Sluggable')
                ->setConfig('slug', $this->getSlugField());
        }
    }

    /**
     * Gets the slugField()
     *
     * @return string The field name for slug
     */
    public function getSlugField(): string
    {
        return $this->checkAddSlug;
    }

    /**
     * Sets the name field, if different from the default
     *
     * @param string $field The field name to have the name set to
     * @return void
     */
    public function setNameField(string $field): void
    {
        if ($field) {
            $this->checkAddName = $field;
        }
        if ($this->behaviors()->has('Sluggable')) {
            $this->behaviors()
                ->get('Sluggable')
                ->setConfig('field', $this->getNameField());
        }
    }

    /**
     * Gets the nameField()
     *
     * @return string The field name for name
     */
    public function getNameField(): string
    {
        return $this->checkAddName;
    }

    /**
     * Checks if a record exist by it's slug, if not, it creates the record
     *
     * @param mixed $name The name of the record
     * @param null|string $slug The unique slug to look for
     * @param array<string, mixed> $fields The list of other details to include in the new record
     * @param bool|array<mixed> $returnEntity If true, return the entity, otherwise return the entity id. If it's an array, treat it as options
     * @return \Cake\Datasource\EntityInterface|int Either the existing entity, or the newly created entity's id.
     * @throws \Cake\ORM\Exception\PersistenceFailedException
     * @throws \Fr3nch13\Utilities\Exception\MissingMethodException; if fixName method isn't found.
     * @TODO Use a more specific Exception when the save fails
     */
    public function checkAdd($name = null, $slug = null, $fields = [], $returnEntity = false)
    {
        try {
            // @phpstan-ignore-next-line
            $name = $this->fixName($name);
        } catch (\Throwable $e) {
            throw new MissingMethodException([self::class, 'fixName', '']);
        }

        $save = false;
        $new = false;

        $forceOnlySlug = false;
        if ($slug) {
            $forceOnlySlug = true;
        }

        if ($name && !$slug) {
            $slug = $this->slugify($name);
        }

        $options = [];
        if (is_array($returnEntity)) {
            $options = $returnEntity;
            $returnEntity = (isset($options['returnEntity']) && $options['returnEntity'] ? true : false);
        }

        if (isset($options['update']) && $options['update'] === true) {
            $save = true;
        }

        $entity = $this->getCheckAdd($slug);
        if ($entity !== null && !$save) {
            $this->setCheckAdd($slug, $entity);
            if ($returnEntity === true) {
                return $entity;
            }

            return $entity->get('id');
        }

        $this->dbReadCnt++;

        // try and find the entity by it's old slug setting first, incase the slug needs to be updated.
        $entity = null;
        if (isset($options['old_slug']) && $options['old_slug']) {
            /** @var \Cake\ORM\Entity|null $entity */
            $entity = $this->find('all')->where([$this->getSlugField() => $options['old_slug']])->first();
        }
        if ($entity === null) {
            /** @var \Cake\ORM\Entity|null $entity */
            $entity = $this->find('all')->where([$this->getSlugField() => $slug])->first();
        }

        if ($entity === null) {
            if (!$forceOnlySlug) {
                // unable to find by slug, see if we can find by name, if so, and the slug field is empty, update it.
                /** @var \Cake\ORM\Entity $entity|null */
                $entity = $this->find('all')->where([$this->getNameField() => $name])->first();
            }
            if (!$entity) {
                /** @var \Cake\ORM\Entity $entity */
                $entity = $this->newEntity([]);
                $new = true;
            }
            $save = true;
        }

        if ($save) {
            if ($entity->{$this->getSlugField()} != $slug) {
                $entity->set($this->getSlugField(), $slug);
            }
            if ($entity->{$this->getNameField()} != $name) {
                $entity->set($this->getNameField(), $name);
            }

            foreach ($fields as $fname => $fval) {
                if ($entity->{$fname} != $fval) {
                    $entity->{$fname} = $fval;
                }
            }
            if ($entity->isDirty()) {
                $dirty = $entity->getDirty();
                $this->saveOrFail($entity);
                $this->dbWriteCnt++;
                if ($new) {
                    $this->checkAdd_new_cnt++;
                } else {
                    // don't count the ones with the last_seen field is the only dirty.
                    if (count($dirty) == 1 && $dirty[0] == 'last_seen') {
                        // yes, i'm being sloppy.
                    } elseif (count($dirty) == 1 && $dirty[0] == 'slug') {
                    } elseif (
                        count($dirty) == 2 &&
                        in_array($dirty[0], ['last_seen', 'slug']) &&
                        in_array($dirty[1], ['last_seen', 'slug'])
                    ) {
                    } else {
                        $this->checkAdd_update_cnt++;
                    }
                }
            }
        }

        $this->setCheckAdd($slug, $entity);

        if ($returnEntity === true) {
            return $entity;
        }

        return $entity->get('id');
    }

    /**
     * Checks the checkadd cache and returns the id, or false
     *
     * @param string $slug The slug to look up
     * @param null|string $alias The model alias so look up.
     * @return \Cake\ORM\Entity|null This id if found, or false if not
     */
    public function getCheckAdd(string $slug, $alias = null): ?\Cake\ORM\Entity
    {
        if (!$this->getUseCache()) {
            return null;
        }
        if (!$slug) {
            return null;
        }
        if (!$alias) {
            $alias = $this->getAlias();
        }

        if (!array_key_exists($alias, $this->checkAddIds)) {
            return null;
        }

        if (!array_key_exists($slug, $this->checkAddIds[$alias])) {
            return null;
        }
        $this->cacheReadCnt++;

        return $this->checkAddIds[$alias][$slug];
    }

    /**
     * Adds the id to the checkadd cache
     *
     * @param string $slug The slug to look up
     * @param \Cake\ORM\Entity $entity The object record that was added
     * @param null|string $alias The model alias so look up.
     * @return \Cake\ORM\Entity
     */
    public function setCheckAdd(string $slug, \Cake\ORM\Entity $entity, ?string $alias = null): \Cake\ORM\Entity
    {
        if (!$this->getUseCache()) {
            $this->setLastEntity($entity);

            return $entity;
        }
        if (!$alias) {
            $alias = $this->getAlias();
        }
        if (!array_key_exists($alias, $this->checkAddIds)) {
            $this->checkAddIds[$alias] = [];
        }
        $this->checkAddIds[$alias][$slug] = $entity;
        $this->setLastEntity($entity);

        return $this->checkAddIds[$alias][$slug];
    }

    /**
     * Sets tracking on the last entity that was added to the cache
     *
     * @param \Cake\ORM\Entity $entity The last entity added to the cache
     * @return void
     */
    public function setLastEntity(\Cake\ORM\Entity $entity): void
    {
        $this->lastEntity = $entity;
    }

    /**
     * Returns the last entity added to the cache
     *
     * @return null|\Cake\ORM\Entity The last entity added to the cache
     */
    public function getLastEntity(): ?\Cake\ORM\Entity
    {
        return $this->lastEntity;
    }

    /**
     * Gets if we're using memory caching.
     *
     * @return bool If we're using caching.
     */
    public function getUseCache(): bool
    {
        return $this->useCaching ? true : false;
    }

    /**
     * Sets if we're using memory caching.
     *
     * @param bool $toCache Set whether or not to use memory caching.
     * @return void
     */
    public function setUseCache(bool $toCache = false): void
    {
        $this->useCaching = ($toCache ? true : false);
    }

    /**
     * Creates a slug from the input
     *
     * @param mixed $input The input to create the slug from
     * @return string the slugged string
     * @throws \Fr3nch13\Utilities\Exception\MissingMethodException; if sluggableSlugify method isn't found.
     */
    public function slugify($input = null): string
    {
        try {
            // @phpstan-ignore-next-line
            return $this->sluggableSlugify($input);
        } catch (\Throwable $e) {
            throw new MissingMethodException([
                self::class,
                'sluggableSlugify',
                ' Either add the SluggableBehavior, or add that method directly.',
            ]);
        }
    }
}
