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
     * @var array $params
     */
    protected $params = [];
    /**
     * @var string $defaultSort
     */
    protected $defaultSort = '';

    protected $sort;

    /**
     * @param string| $baseQuery
     * @param array $params
     * @return Builder
     */
    public static function apply($baseQuery, $params = []): Builder
    {
        if (\is_string($baseQuery)) {
            $baseQuery = ($baseQuery)::query();
        }

        $object = new static();
        $object->query = $baseQuery;

        $object->setParams($params);
        $object->buildQuery();
        $object->sortQuery();

        return $object->query;
    }

    protected function setParams($params)
    {
        $this->params = $this->filterParams($params);
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
     * @param array $params
     * @return array
     */
    protected function filterParams(array $params)
    {
        $this->sort = $params['sort'] ?? '';
        $fillable = array_combine($this->fillable, $this->fillable);
        if (array_keys($this->fillable) !== range(0, \count($this->fillable) - 1)) {
            $aliases = array_filter(array_flip($this->fillable), 'is_string');
            $fillable = array_merge($fillable, $aliases);
        }

        $params = array_filter(array_intersect_ukey($params, $fillable, 'strcasecmp'));
        foreach ($params as $key => $value) {
            if ($fillable[$key] == $key) continue;
            $params[$fillable[$key]] = $params[$key];
            unset($params[$key]);
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
        return $params;
    }

    protected function buildQuery()
    {
        foreach ($this->params as $key => $value) {
            $key = strtolower($key);
            if (\in_array($key, get_class_methods($this), true)) {
                $this->$key($value);
            } else {
                $this->query->where($key, $value);
            }
        }
    }
}