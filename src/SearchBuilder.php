<?php

namespace bigdropinc\LaravelSimpleSearch;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Class SearchBuilder
 * @package App\Filters
 */
abstract class SearchBuilder
{
    use Casts;
    /**
     * @var Builder query
     */
    protected $query;
    /**
     * @var array
     */
    protected $fillable = [];
    protected $excludeSort = [];
    /**
     * @var string
     */
    protected $defaultSort = '';
    protected $sort;

    protected $maxPerPage = 100;
    protected $perPageName = 'per_page';

    /**
     * @var array $_attributes
     */
    private $_attributes = [];
    private $_sourceAttributes = [];

    /**
     * SearchBuilder constructor.
     * @param $baseQuery
     * @param array $attributes
     */
    public function __construct($baseQuery, array $attributes = [])
    {
        if (\is_string($baseQuery)) {
            $baseQuery = ($baseQuery)::query();
        }

        $this->query = $baseQuery;

        $this->setAttributes($attributes);
        $this->buildQuery();
        $this->buildSort();
        $this->pagination();
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->_attributes)) {
            return $this->_attributes[$key] ?? null;
        }

        return $this->$key;
    }

    public function __set($key, $value)
    {
        if (array_key_exists($key, $this->_attributes)) {
            $this->_attributes[$key] = $value;
        } else {
            $this->$key = $value;
        }
    }

    public function __isset($key)
    {
        if (array_key_exists($key, $this->_attributes)) {
            return isset($this->_attributes[$key]);
        }

        return isset($this->$key);
    }

    /**
     * @param $baseQuery
     * @param array $attributes
     * @return Builder
     */
    public static function apply($baseQuery, $attributes = []): Builder
    {
        return (new static($baseQuery, $attributes))->getQuery();
    }

    /**
     * @return Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     * @return array
     */
    public function getSourceAttributes()
    {
        return $this->_sourceAttributes;
    }

    protected function setAttributes($attributes)
    {
        $this->_sourceAttributes = $attributes;

        $temp = [];
        foreach ($attributes as $key => $value) {
            if (!\in_array($key, $this->fillable) || empty($value)) {
                continue;
            }

            if ($this->hasCast($key)) {
                $temp[$key] = $this->castAttribute($key, $value);
            } else {
                $temp[$key] = $value;
            }
        }
        $this->_attributes = $temp;
    }

    protected function buildSort()
    {
        $sort = $this->getSourceAttributes()['sort'] ?? '';

        if (\in_array($sort, $this->fillable) && !\in_array($sort, $this->excludeSort)) {
            $this->sort = $sort;
        }

        if (!$this->sort && $this->defaultSort) {
            $this->sort = $this->defaultSort;
        }

        if (!$this->sort) {
            return;
        }

        $key = 'sort' . Str::studly($this->sort);
        if (\in_array($key, get_class_methods($this), true)) {
            $this->$key();
        } else {
            $property = ltrim($this->sort, '-');
            $direction = strpos($this->sort, '-') === 0 ? 'desc' : 'asc';
            $this->query->orderBy($property, $direction);
        }
    }

    protected function buildQuery()
    {
        foreach ($this->getAttributes() as $key => $value) {
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
        $perPage = (int)($this->getSourceAttributes()[$this->perPageName] ?? $this->query->getModel()->getPerPage());
        $perPage = $perPage < $this->maxPerPage ? $perPage : $this->maxPerPage;

        $this->query->getModel()->setPerPage($perPage);
    }
}
