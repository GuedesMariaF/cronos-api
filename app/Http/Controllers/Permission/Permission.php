<?php

namespace App\Http\Controllers\Permission;

use App\Builder\ReturnApi;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Services\Permission\PermissionService;
use Illuminate\Http\JsonResponse;

class Permission extends Controller
{
    public function __construct(public PermissionService $permissionService) {}

    public function index(): JsonResponse
    {
        try {
            $data = $this->permissionService->indexGroupedByModule();

            return ReturnApi::success($data, 'Permissões listadas com sucesso.');
        } catch (\Exception $e) {
            throw new ApiException('Erro ao listar permissões: '.$e->getMessage());
        }
    }
}
