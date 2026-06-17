<?php

namespace App\Http\Controllers\TimeSpent;

use App\Builder\ReturnApi;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\TimeSpent\TimeSpentUpdateRequest;

use Illuminate\Http\JsonResponse;
use App\Services\TimeSpent\TimeSpentService;



class TimeSpentController extends Controller
{
    public function __construct(public TimeSpentService $timeSpentService)
    {}

    public function update(TimeSpentUpdateRequest $request): JsonResponse
    {
        try{
              $data= $this->timeSpentService->update($request->validated());
              return ReturnApi::success($data, 'Tempo gasto atualizado com sucesso.');
            }
        catch (\Exception $e){
            throw new ApiException('Erro ao atualizar tempo gasto: '.$e->getMessage());
        }
    }
}
