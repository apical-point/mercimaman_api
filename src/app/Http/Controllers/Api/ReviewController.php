<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Bases\ApiBaseController;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReviewController extends ApiBaseController
{

    public function __construct(
        Request $request
    ){
        parent::__construct();

        // キーのチェック
        $this->requestKeyCheck($request);
    }

    public function update(Request $request)
    {
        //Reviewをidから検索
        //update 'star', 'review', 'from_period', 'to_period', 'place'
        $attributes = $request->only(['star', 'review', 'place', 'from_period', 'to_period']);
        $id = $request->id;

        try {
            $review = Review::findOrFail($id);
            $review->update($attributes);
            return $this->sendResponse(__('messages.success_create'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->sendExceptionErrorResponse($e);
        }
    }

    public function delete(Request $request, int $id)
    {
        try {
            $review = Review::findOrFail($id);
            $review->delete();
            return $this->sendResponse(__('messages.success_delete'));
        } catch(\Exception $e) {
            Log::error($e);
            return $this->sendExceptionErrorResponse($e);
        }
    }
}
