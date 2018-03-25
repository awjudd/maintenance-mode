<?php

namespace MisterPhilip\MaintenanceMode\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;

class MaintenanceModeException extends \Illuminate\Foundation\Http\Exceptions\MaintenanceModeException implements Responsable
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        $viewName = config('maintenancemode.view');
        $downFile = storage_path('framework/down');
        if (file_exists($downFile)) {
            $downData = json_decode(file_get_contents($downFile));

            if ($downData->view != null) {
                $viewName = $downData->view;
            }
        }

        return new Response(view($viewName));
    }
}