<?php
/**
 * Created by PhpStorm.
 * User: wuwentao
 * Date: 2017/6/20
 * Time: 13:48
 */

namespace Wwtg99\RestfulHelper;


use Illuminate\Database\Eloquent\Builder;

class ListQuery
{

    /**
     * @var array
     */
    protected $selectableFields = [];

    /**
     * @var array
     */
    protected $filterableFields = [];

    /**
     * @var Builder|string
     */
    protected $model;

    /**
     * @var array
     */
    protected $inputKeys = [
        'fields'=>'fields',
        'page'=>'page',
        'page_size'=>'page_size',
        'sort'=>'sort',
    ];

    /**
     * @var array
     */
    protected $outputKeys = [
        'data'=>'data',
        'total_count'=>'total_count',
        'page'=>'page',
        'page_size'=>'page_size',
        'total_page_count'=>'total_page_count',
    ];

    /**
     * ListQuery constructor.
     * @param Builder|string $model
     * @param array $options
     */
    public function __construct($model, $options = [])
    {
        $this->model = $model;
        if (isset($options['input_keys'])) {
            $this->inputKeys = array_intersect_key($options['input_keys'], $this->inputKeys);
        }
        if (isset($options['output_keys'])) {
            $this->outputKeys = array_intersect_key($options['output_keys'], $this->outputKeys);
        }
    }

    /**
     * @param array $request
     * @return array
     */
    public function lists($request = [])
    {
        $fields = $this->parseFields($request);
        $filters = $this->parseFilters($request);
        $sort = $this->parseSorts($request);
        list($page, $pageSize) = $this->parsePage($request);
        if ($this->model instanceof Builder) {
            $model = $this->model;
        } else {
            $model = DB::table($this->model);
        }
        if ($filters) {
            $model->where($filters);
        }
        $count = $model->count();
        $model->select($fields);
        if ($sort) {
            foreach ($sort as $s) {
                $model->orderBy($s[0], $s[1]);
            }
        }
        $model->limit($pageSize)->offset(($page - 1) * $pageSize);
        $data = $model->get();
        return [$this->outputKeys['data']=>$data, $this->outputKeys['total_count']=>$count, $this->outputKeys['page']=>$page, $this->outputKeys['page_size']=>$pageSize, $this->outputKeys['total_page_count']=>ceil($count / $pageSize)];
    }

    /**
     * @return array
     */
    public function getSelectableFields()
    {
        return $this->selectableFields;
    }

    /**
     * @param array $selectableFields
     * @return ListQuery
     */
    public function setSelectableFields($selectableFields)
    {
        $this->selectableFields = $selectableFields;
        return $this;
    }

    /**
     * @return array
     */
    public function getFilterableFields()
    {
        return $this->filterableFields;
    }

    /**
     * @param array $filterableFields
     * @return ListQuery
     */
    public function setFilterableFields($filterableFields)
    {
        $this->filterableFields = $filterableFields;
        return $this;
    }

    /**
     * @param array $request
     * @return array|string
     */
    protected function parseFields($request)
    {
        $fields = isset($request[$this->inputKeys['fields']]) ? $request[$this->inputKeys['fields']] : [];
        if ($fields) {
            $fields = explode(',', $fields);
            if ($this->selectableFields && is_array($this->selectableFields)) {
                $fields = array_intersect($this->selectableFields, $fields);
            }
            if ($fields) {
                return $fields;
            }
        }
        if ($this->selectableFields && is_array($this->selectableFields)) {
            $fields = $this->selectableFields;
        } else {
            $fields = '*';
        }
        return $fields;
    }

    /**
     * @param array $request
     * @return array|null
     */
    protected function parseFilters($request)
    {
        if ($this->filterableFields && is_array($this->filterableFields)) {
            $filters = [];
            foreach ($this->filterableFields as $filterableField) {
                if (isset($request[$filterableField])) {
                    $filters[] = [$filterableField, '=', $request[$filterableField]];
                }
                if (isset($request[$filterableField . '>'])) {
                    $filters[] = [$filterableField, '>=', $request[$filterableField . '>']];
                }
                if (isset($request[$filterableField . '<'])) {
                    $filters[] = [$filterableField, '<=', $request[$filterableField . '<']];
                }
                if (isset($request[$filterableField . '!'])) {
                    $filters[] = [$filterableField, '<>', $request[$filterableField . '!']];
                }
                if (isset($request[$filterableField . '*'])) {
                    $filters[] = [$filterableField, 'like', '%' . $request[$filterableField . '*'] . '%'];
                }
            }
            return $filters;
        }
        return null;
    }

    /**
     * @param array $request
     * @return array
     */
    protected function parseSorts($request)
    {
        $sort = isset($request[$this->inputKeys['sort']]) ? $request[$this->inputKeys['sort']] : [];
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
     * @param array $request
     * @return array
     */
    protected function parsePage($request)
    {
        if (isset($request[$this->inputKeys['page']])) {
            $page = $request[$this->inputKeys['page']];
        } else {
            $page = 1;
        }
        if (isset($request[$this->inputKeys['page_size']])) {
            $pageSize = $request[$this->inputKeys['page_size']];
        } else {
            $pageSize = 10;
        }
        return [$page, $pageSize];
    }
}