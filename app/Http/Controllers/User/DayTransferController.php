<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\BaseResource;
use App\Http\Resources\User\DayTransferResource;
use App\Services\Interfaces\ITransfer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DayTransferController extends Controller
{

    private $transfer;

    /**
     * @param $transfer
     */
    public function __construct(ITransfer $transfer)
    {
        $this->transfer = $transfer;
    }


    /**
     * Display a listing of the resource.
     *
     * @return BaseResource|JsonResponse|AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();
            if ($user->role == "admin") {
                $res = $this->transfer->index($request);
            } else {
                $res = $this->transfer->getByColumns([
                    'user_id' => $user->id
                ])->get();
            }
            return DayTransferResource::paginable($res);
        } catch (Exception $exception) {
            return BaseResource::return();
        }
    }

    public function changeStatus($id)
    {
        try {
            $res = $this->transfer->getById($id);
            if ($res) {
                $res->update([
                    'status' => !$res->status
                ]);
                return DayTransferResource::create($res);
            }
            return BaseResource::return();
        } catch (Exception $exception) {
            return BaseResource::return();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return DayTransferResource|JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $res = $this->transfer->store($request);
            if ($res) {
                return DayTransferResource::create($res);
            }
            return BaseResource::return();
        } catch (Exception $exception) {
            return BaseResource::return();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return DayTransferResource|JsonResponse
     */
    public function show($id)
    {
        try {
            $res = $this->transfer->getById($id);
            if ($res) {
                return DayTransferResource::create($res);
            }
            return BaseResource::return();
        } catch (Exception $exception) {
            return BaseResource::return();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return DayTransferResource|JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $res = $this->transfer->update($request, $id);
            if ($res) {
                return DayTransferResource::create($res);
            }
            return BaseResource::return();
        } catch (Exception $exception) {
            return BaseResource::return();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return BaseResource|JsonResponse
     */
    public function destroy($id)
    {
        try {
            $res = $this->transfer->delete($id);
            if ($res) {
                return BaseResource::ok();
            }
            return BaseResource::return();
        } catch (Exception $exception) {
            return BaseResource::return();
        }
    }
}
