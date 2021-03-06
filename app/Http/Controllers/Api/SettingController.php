<?php

namespace App\Http\Controllers\Api;

use App\Models\UserActionLog;
use Illuminate\Http\Request;
use \Response;
use \Auth;
use \App\Models\User;
use \App\Models\Setting;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Marcelgwerder\ApiHandler\Facades\ApiHandler;

class SettingController extends ApiController
{
    public function index()
    {
        return ApiHandler::parseMultiple(Setting::query(), ['id', 'name', 'key', 'value', 'description'])->getResponse();
    }
    public function show($id)
    {
        return Setting::find($id);
    }
    public function store(Request $request)
    {
        $data = $request->all();
        $obj = Setting::create($data);

        UserActionLog::saveAction($obj, "create");

        return $obj;
    }
    public function update(Request $request)
    {
        $data = $request->all();
        $obj = Setting::find($data['id']);
        $is_saved = $obj->update($data);

        if ($is_saved)
            UserActionLog::saveAction($obj, "update");

        return $obj;
    }
    public function destroy($id)
    {
        $obj = Setting::find($id);
        $is_destroyed = Setting::destroy($id);

        if ($is_destroyed)
            UserActionLog::saveAction($obj, "destroy");

        return $is_destroyed;
    }
}
