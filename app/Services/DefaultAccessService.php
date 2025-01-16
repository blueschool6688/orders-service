<?php

namespace App\Services;

use App\Models\DefaultAccess;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DefaultAccessService
{
    /**
     * @throws Exception
     */
    public function show($guard = 'web'): array
    {
        try {
            $array         = [];
            $defaultAccess = DefaultAccess::where(['user_id' => Auth::guard($guard)->id()])->get();
            if ($defaultAccess) {
                foreach ($defaultAccess as $default) {
                    $array[$default->name] = $default->default_id;
                }
            }
            return $array;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function storeOrUpdate($request = [], $guard = 'web'): array
    {
        try {
            if (!blank($request)) {
                foreach ($request as $key => $item) {
                    if ($key == 'branch_id') {
                        if (Auth::guard($guard)->user()->branch_id != '0') {
                            $item = Auth::guard($guard)->user()->branch_id;
                        }
                    }
                    $defaultAccess             = DefaultAccess::firstOrNew(['user_id' => Auth::guard($guard)->id(), 'name' => $key]);
                    $defaultAccess->default_id = $item;
                    $defaultAccess->save();
                }
            }
            return $this->show($guard);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
