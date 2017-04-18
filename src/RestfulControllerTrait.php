<?php
/**
 * Created by PhpStorm.
 * User: wwt
 * Date: 2017/3/20
 * Time: 21:55
 */

namespace Wwtg99\RestfulHelper;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

trait RestfulControllerTrait
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $inputs = $this->parseIndexRequest(request());
        $data = $this->restIndex($inputs);
        return $this->responseRestIndex($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $inputs = $this->parseStoreRequest($request);
        $data = $this->restStore($inputs);
        return $this->responseRestStore($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $inputs = $this->parseShowRequest(request());
        $data = $this->restShow($inputs, $id);
        return $this->responseRestShow($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $inputs = $this->parseUpdateRequest($request);
        $data = $this->restUpdate($inputs, $id);
        return $this->responseRestUpdate($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $inputs = $this->parseDeleteRequest(request());
        $data = $this->restDelete($inputs, $id);
        return $this->responseRestDelete($data);
    }

    /**
     * Batch process.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function batch(Request $request)
    {
        $inputs = json_decode($request->getContent(), true);
        $data = [];
        if ($inputs) {
            $data = $this->batchProcess($inputs);
        }
        return $this->responseBatch($data);
    }

    /**
     * Parse index requests.
     *
     * @param Request $request
     * @return mixed
     */
    protected function parseIndexRequest(Request $request)
    {
        return $request->all();
    }

    /**
     * Execute index query.
     *
     * @param array $inputs
     * @return mixed
     */
    protected function restIndex($inputs)
    {
        if (isset($inputs[RestHelperTrait::$restQueryKeyFields]) && $inputs[RestHelperTrait::$restQueryKeyFields] == 'count') {
            return ['number'=>$this->getModel()->index($inputs)->count()];
        }
        return $this->getModel()->index($inputs)->get();
    }

    /**
     * Response index results.
     *
     * @param $data
     * @return mixed
     */
    protected function responseRestIndex($data)
    {
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Parse store requests.
     *
     * @param Request $request
     * @return array
     */
    protected function parseStoreRequest(Request $request)
    {
        if (property_exists($this, 'creatableFields')) {
            $fields = $this->creatableFields;
            $inputs = [];
            foreach ($fields as $field) {
                $v = $request->input($field);
                if (!is_null($v)) {
                    $inputs[$field] = $v;
                }
            }
            return $inputs;
        }
        return $request->all();
    }

    /**
     * Execute store query.
     *
     * @param array $inputs
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function restStore($inputs)
    {
        return $this->getModel()->create($inputs);
    }

    /**
     * Response store results.
     *
     * @param $data
     * @return mixed
     */
    protected function responseRestStore($data)
    {
        return response()->json($data, 201, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Parse show requests.
     *
     * @param Request $request
     * @return array
     */
    protected function parseShowRequest(Request $request)
    {
        return $request->all();
    }

    /**
     * Execute show query.
     *
     * @param array $inputs
     * @param $id
     * @return mixed
     */
    protected function restShow($inputs, $id)
    {
        return $this->getModel()->findOrFail($id);
    }

    /**
     * Response show results.
     *
     * @param $data
     * @return mixed
     */
    protected function responseRestShow($data)
    {
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Parse update requests.
     *
     * @param Request $request
     * @return array
     */
    protected function parseUpdateRequest(Request $request)
    {
        if (property_exists($this, 'updateableFields')) {
            $fields = $this->updateableFields;
            $inputs = [];
            if ($request->method() == 'PUT') {
                foreach ($fields as $field) {
                    $inputs[$field] = $request->input($field);
                }
            } else {
                foreach ($fields as $field) {
                    $v = $request->input($field);
                    if (!is_null($v)) {
                        $inputs[$field] = $v;
                    }
                }
            }
            return $inputs;
        }
        return $request->all();
    }

    /**
     * Execute update query.
     *
     * @param array $inputs
     * @param $id
     * @return mixed
     */
    protected function restUpdate($inputs, $id)
    {
        $re = $this->getModel()->find($id)->update($inputs);
        if ($re) {
            return $this->getModel()->find($id);
        }
        return false;
    }

    /**
     * Response update results.
     *
     * @param $data
     * @return mixed
     */
    protected function responseRestUpdate($data)
    {
        if ($data) {
            return response()->json($data, 201, [], JSON_UNESCAPED_UNICODE);
        } else {
            return response('', 400);
        }
    }

    /**
     * Parse delete requests.
     *
     * @param Request $request
     * @return array
     */
    protected function parseDeleteRequest(Request $request)
    {
        return $request->all();
    }

    /**
     * Execute delete query.
     *
     * @param array $inputs
     * @param $id
     * @return mixed
     */
    protected function restDelete($inputs, $id)
    {
        $model = $this->getModel()->find($id);
        if ($model) {
            $model->delete();
            return true;
        }
        return false;
    }

    /**
     * Response delete results.
     *
     * @param $data
     * @return mixed
     */
    protected function responseRestDelete($data)
    {
        return response('', 204);
    }

    /**
     * @param array $inputs
     * @return array
     */
    protected function batchProcess(array $inputs)
    {
        $res = [];
        foreach ($inputs as $method => $input) {
            switch (strtoupper($method)) {
                case 'GET': $res['GET'] = $this->batchGet($input); break;
                case 'CREATE': $res['CREATE'] = $this->batchPost($input); break;
                case 'UPDATE': $res['UPDATE'] = $this->batchUpdate($input); break;
                case 'DELETE': $res['DELETE'] = $this->batchDelete($input); break;
            }
        }
        return $res;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function responseBatch($data)
    {
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param $inputs
     * @return array
     */
    protected function batchGet($inputs)
    {
        $res = [];
        if (!is_array($inputs)) {
            $inputs = explode(',', $inputs);
        }
        foreach ($inputs as $id) {
            try {
                $d = $this->restShow([], $id);
                array_push($res, $d);
            } catch (ModelNotFoundException $e) {
                //not found
                array_push($res, ['code'=>404, 'error'=>$e->getMessage()]);
            }
        }
        return $res;
    }

    /**
     * @param $inputs
     * @return array
     */
    protected function batchPost($inputs)
    {
        $res = [];
        if (!is_array($inputs)) {
            $inputs = json_decode($inputs, true);
        }
        if ($inputs) {
            foreach ($inputs as $input) {
                try {
                    $d = $this->restStore($input);
                    if ($d) {
                        array_push($res, $d);
                    } else {
                        array_push($res, ['code' => '422', 'error' => '']);
                    }
                } catch (\Exception $e) {
                    array_push($res, ['code' => '422', 'error' => $e->getMessage()]);
                }
            }
        }
        return $res;
    }

    /**
     * @param $inputs
     * @return array
     */
    protected function batchUpdate($inputs)
    {
        $res = [];
        if (!is_array($inputs)) {
            $inputs = json_decode($inputs, true);
        }
        if ($inputs) {
            foreach ($inputs as $id => $input) {
                try {
                    $d = $this->restUpdate($input, $id);
                    if ($d) {
                        $res[$id] = $d;
                    } else {
                        $res[$id] = ['code' => 422, 'error' => ''];
                    }
                } catch (\Exception $e) {
                    $res[$id] = ['code' => 422, 'error' => $e->getMessage()];
                }
            }
        }
        return $res;
    }

    /**
     * @param $inputs
     * @return array
     */
    protected function batchDelete($inputs)
    {
        $res = [];
        if (!is_array($inputs)) {
            $inputs = explode(',', $inputs);
        }
        foreach ($inputs as $id) {
            try {
                $d = $this->restDelete([], $id);
                if ($d) {
                    array_push($res, ['code' => 204]);
                } else {
                    array_push($res, ['code' => 422, 'error' => '']);
                }
            } catch (\Exception $e) {
                array_push($res, ['code' => 422, 'error' => $e->getMessage()]);
            }
        }
        return $res;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    abstract protected function getModel();

}