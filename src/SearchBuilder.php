<?php

namespace bigdropinc\LaravelSimpleSearch;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class SearchBuilder
 * @package App\Filters
 */
abstract class SearchBuilder
{
    /**
     * @var Builder query
     */
    protected $query;
    /**
     * @var array $fillable
     */
    protected $fillable = [];
    /**
     * @var array $_attributes
     */
    protected $_attributes = [];
    /**
     * @var string $defaultSort
     */
    protected $defaultSort = '';

    protected $maxPerPage = 100;
    protected $perPageName = 'per_page';

    protected $sort;
    protected $perPage;

    /**
     * @param string| $baseQuery
     * @param array $attributes
     * @return Builder
     */
    public static function apply($baseQuery, $attributes = []): Builder
    {
        if (\is_string($baseQuery)) {
            $baseQuery = ($baseQuery)::query();
        }

        $object = new static();
        $object->query = $baseQuery;

        $object->setAttributes($attributes);
        $object->buildQuery();
        $object->sortQuery();
        $object->pagination();

        return $object->query;
    }

    protected function setAttributes($attributes)
    {
        $this->_attributes = $this->filterAttributes($attributes);
    }

    protected function sortQuery()
    {
        if (!$this->sort && $this->defaultSort) {
            $this->sort = $this->defaultSort;
        }

        if (!$this->sort) {
            return;
        }

        $property = ltrim($this->sort, '-');
        $direction = $this->sort[0] === '-' ? 'desc' : 'asc';
        $this->query->orderBy($property, $direction);
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function filterAttributes(array $attributes)
    {
        $this->sort = $attributes['sort'] ?? '';
        $this->perPage = $attributes[$this->perPageName] ?? $this->query->getModel()->getPerPage();

        $fillable = array_combine($this->fillable, $this->fillable);
        if (array_keys($this->fillable) !== range(0, \count($this->fillable) - 1)) {
            $aliases = array_filter(array_flip($this->fillable), 'is_string');
            $fillable = array_merge($fillable, $aliases);
        }

        $attributes = array_filter(array_intersect_ukey($attributes, $fillable, 'strcasecmp'));
        foreach ($attributes as $key => $value) {
            if ($fillable[$key] == $key) continue;
            $attributes[$fillable[$key]] = $attributes[$key];
            unset($attributes[$key]);
        }

        if ($this->sort) {
            $cutSort = ltrim($this->sort, '-');
            if (!isset($fillable[$cutSort])) {
                $this->sort = '';
            } else {
                $dir = $this->sort[0] === '-' ? '-' : '';
                $this->sort = $dir . $fillable[$cutSort];
            }
        }

        return $attributes;
    }

    protected function buildQuery()
    {
        foreach ($this->_attributes as $key => $value) {
            $key = strtolower($key);
            if (\in_array($key, get_class_methods($this), true)) {
                $this->$key($value);
            } else {
                $this->query->where($key, $value);
            }
        }
    }

    protected function pagination()
    {
        $this->perPage = (int)$this->perPage;
        $perPage = $this->perPage < $this->maxPerPage ? $this->perPage : $this->maxPerPage;

        $this->query->getModel()->setPerPage($perPage);
    }
}
