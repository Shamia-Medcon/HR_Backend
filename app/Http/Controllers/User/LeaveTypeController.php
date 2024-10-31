<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use App\Http\Resources\User\LeaveTypeResource;
use App\Services\Interfaces\IType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeaveTypeController extends Controller
{

    private $type;

    /**
     * @param $type
     */
    public function __construct(IType $type)
    {
        $this->type = $type;
    }


    public function index(Request $request)
    {
        try {
            $res = $this->type->index($request);
            return LeaveTypeResource::paginable($res);
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    public function emergency(Request $request)
    {
        try {
            $res = $this->type->getByColumns([
                'emergency' => true
            ])->first();
//            Log::error($res);
            if ($res) {
                return LeaveTypeResource::create($res);
            }
            return BaseResource::return();
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    public function store(Request $request)
    {
        try {
            $res = $this->type->store($request);
            if ($res) {
                return LeaveTypeResource::create($res);
            }
            return BaseResource::return();
        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    public function show($id)
    {
        try {
            $res = $this->type->getById($id);
            if ($res) {
                return LeaveTypeResource::create($res);
            }
            return BaseResource::return();

        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $res = $this->type->update($request, $id);
            if ($res) {
                return LeaveTypeResource::create($res);
            }
            return BaseResource::return();

        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }

    public function destroy($id): JsonResponse|BaseResource
    {
        try {
            $res = $this->type->delete($id);
            if ($res) {
                return BaseResource::ok();
            }
            return BaseResource::return();

        } catch (Exception $exception) {
            return BaseResource::exception($exception);
        }
    }


}
