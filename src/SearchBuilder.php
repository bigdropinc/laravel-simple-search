<?php

namespace bigdropinc\LaravelSimpleSearch;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Class SearchBuilder
 * @package App\Filters
 *
 * @property string sort
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
    protected $defaultSort = '-id';
    protected $sortDir = 'asc';

    protected $maxPerPage = 100;
    protected $perPageName = 'per_page';

    protected $defaultTableAlias = '';
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
        $this->configuration($attributes);
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

    protected function customQuery()
    {

    }

    private function configuration($attributes)
    {
        $this->_sourceAttributes = $attributes;
        $this->_attributes = $this->filterAttributes($attributes);

        $this->customQuery();
        $this->buildConditions();
        $this->buildSort();
        $this->pagination();
    }

    private function filterAttributes($attributes)
    {
        $temp = [];
        foreach ($attributes as $key => $value) {
            if (!\in_array($key, $this->fillable) || null === $value) {
                continue;
            }

            if ($this->hasCast($key)) {
                $temp[$key] = $this->castAttribute($key, $value);
            } else {
                $temp[$key] = $value;
            }
        }
        return $temp;
    }

    private function getAttributeAlias($name)
    {
        return isset($this->fillable[$name]) ? $this->fillable[$name] : $name;
    }

    private function buildConditions()
    {
        foreach ($this->getAttributes() as $attr => $value) {
            $key = Str::camel($attr);
            if (\in_array($key, get_class_methods($this), true)) {
                $this->$key($value);
            } else {
                $attr = $this->getAttributeAlias($attr);
                $column = $this->defaultTableAlias ? $this->defaultTableAlias . '.' . $attr : $attr;
                $this->query->where($column, $value);
            }
        }
    }

    private function buildSort()
    {
        $sort = $this->getSourceAttributes()['sort'] ?? '';
        $wdSort = ltrim($sort, '-');

        if (!\in_array($wdSort, $this->fillable) || \in_array($wdSort, $this->excludeSort)) {
            $sort = $this->defaultSort;
            $wdSort = ltrim($sort, '-');
        }

        if (!$wdSort) {
            return;
        }

        $this->sortDir = strpos($sort, '-') === 0 ? 'desc' : 'asc';

        $key = 'sort' . Str::studly($wdSort);
        if (\in_array($key, get_class_methods($this), true)) {
            $this->$key();
        } else {
            $wdSort = $this->getAttributeAlias($wdSort);
            $column = $this->defaultTableAlias ? $this->defaultTableAlias . '.' . $wdSort : $wdSort;

            $this->query->orderBy($column, $this->sortDir);
        }
    }

    private function pagination()
    {
        $perPage = (int)($this->getSourceAttributes()[$this->perPageName] ?? $this->query->getModel()->getPerPage());
        $perPage = $perPage < $this->maxPerPage ? $perPage : $this->maxPerPage;

        $this->query->getModel()->setPerPage($perPage);
    }
}
