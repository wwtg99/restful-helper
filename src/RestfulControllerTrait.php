<?php
/**
 * Created by PhpStorm.
 * User: wwt
 * Date: 2017/3/20
 * Time: 21:55
 */

namespace Wwtg99\RestfulHelper;


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
     * @param Request $request
     * @return mixed
     */
    protected function parseIndexRequest(Request $request)
    {
        return $request->all();
    }

    /**
     * @param array $inputs
     * @return mixed
     */
    protected function restIndex($inputs)
    {
        return $this->getModel()->index($inputs)->get();
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function responseRestIndex($data)
    {
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
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
     * @param array $inputs
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function restStore($inputs)
    {
        return $this->getModel()->create($inputs);
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function responseRestStore($data)
    {
        return response()->json($data, 201, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function parseShowRequest(Request $request)
    {
        return $request->all();
    }

    /**
     * @param array $inputs
     * @param $id
     * @return mixed
     */
    protected function restShow($inputs, $id)
    {
        return $this->getModel()->findOrFail($id);
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function responseRestShow($data)
    {
        return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
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
     * @param Request $request
     * @return array
     */
    protected function parseDeleteRequest(Request $request)
    {
        return $request->all();
    }

    /**
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
     * @param $data
     * @return mixed
     */
    protected function responseRestDelete($data)
    {
        return response('', 204);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    abstract protected function getModel();

}