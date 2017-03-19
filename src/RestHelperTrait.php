<?php
/**
 * Created by PhpStorm.
 * User: wwt
 * Date: 2017/3/18
 * Time: 22:23
 */

namespace Wwtg99\RestfulHelper;


trait RestHelperTrait
{

    public static $restQueryKeyLimit = 'limit';

    public static $restQueryKeyOffset = 'offset';

    public static $restQueryKeyPage = 'page';

    public static $restQueryKeyPageSize = 'page_size';

    public static $restQueryKeyFields = 'fields';

    public static $restQueryKeySort = 'sort';

    /**
     * Parse fields, filters, sorts and pagination from query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $inputs
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIndex($query, $inputs = null)
    {
        if (!$inputs && function_exists('request')) {
            $inputs = request()->all();
        }
        list($limit, $offset) = $this->parseRestPage($inputs);
        $fields = $this->parseRestFields($inputs);
        $filters = $this->parseRestFilter($inputs);
        $sorts = $this->parseRestSort($inputs);
        $query->select($fields);
        if ($sorts) {
            foreach ($sorts as $sort) {
                $query->orderBy($sort[0], $sort[1]);
            }
        }
        if ($filters) {
            foreach ($filters as $f => $v) {
                $query->where($f, $v);
            }
        }
        if ($limit) {
            $query->limit($limit);
        }
        if ($offset) {
            $query->offset($offset);
        }
        return $query;
    }

    /**
     * Parse limit, offset or page, page_size from query.
     *
     * @param array $query
     * @return array
     */
    protected function parseRestPage($query)
    {
        $limit = (isset($query[static::$restQueryKeyLimit]) && is_int($query[static::$restQueryKeyLimit])) ?
            (int)$query[static::$restQueryKeyLimit] : null;
        $offset = (isset($query[static::$restQueryKeyOffset]) && is_int($query[static::$restQueryKeyLimit])) ?
            (int)$query[static::$restQueryKeyOffset] : null;
        $page = (isset($query[static::$restQueryKeyPage]) && is_int($query[static::$restQueryKeyLimit])) ?
            (int)$query[static::$restQueryKeyPage] : null;
        $pageSize = (isset($query[static::$restQueryKeyPageSize]) && is_int($query[static::$restQueryKeyLimit])) ?
            (int)$query[static::$restQueryKeyPageSize] : 15;
        if (is_null($limit) && is_null($offset) && !is_null($page)) {
            $limit = $pageSize;
            $offset = ($page - 1) * $pageSize;
        }
        return [$limit, $offset];
    }

    /**
     * Parse select fields from query.
     *
     * @param array $query
     * @return array|string
     */
    protected function parseRestFields($query)
    {
        $fields = isset($query[static::$restQueryKeyFields]) ? $query[static::$restQueryKeyFields] : null;
        if ($fields) {
            $fields = explode(',', $fields);
            if (property_exists($this, 'selectableFields') && $this->selectableFields && is_array($this->selectableFields)) {
                $fields = array_intersect($this->selectableFields, $fields);
            }
            return $fields;
        }
        if (property_exists($this, 'selectableFields') && $this->selectableFields && is_array($this->selectableFields)) {
            $fields = $this->selectableFields;
        } else {
            $fields = '*';
        }
        return $fields;
    }

    /**
     * Parse sort from query.
     *
     * @param array $query
     * @return array|null
     */
    protected function parseRestSort($query)
    {
        $sort = isset($query[static::$restQueryKeySort]) ? $query[static::$restQueryKeySort] : null;
        if ($sort) {
            $res = [];
            $sort = explode(',', $sort);
            foreach ($sort as $item) {
                $item = trim($item);
                if (substr($item, 0, 1) == '-') {
                    array_push($res, [substr($item, 1), 'desc']);
                } else {
                    array_push($res, [$item, 'asc']);
                }
            }
            return $res;
        }
        return $sort;
    }

    /**
     * Parse filter from query.
     *
     * @param array $query
     * @return array|null
     */
    protected function parseRestFilter($query)
    {
        if (property_exists($this, 'filterableFields') && $this->filterableFields && is_array($this->filterableFields)) {
            $filters = [];
            foreach ($this->filterableFields as $filterableField) {
                if (isset($query[$filterableField])) {
                    $filters[$filterableField] = $query[$filterableField];
                }
            }
            return $filters;
        }
        return null;
    }

}