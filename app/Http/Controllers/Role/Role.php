<?php

namespace App\Http\Controllers\Role;

use App\Builder\ReturnApi;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleAddPermissionsRequest;
use App\Http\Requests\Role\RoleDeleteRequest;
use App\Http\Requests\Role\RoleIndexRequest;
use App\Http\Requests\Role\RoleShowRequest;
use App\Http\Requests\Role\RoleStoreRequest;
use App\Http\Requests\Role\RoleUpdateRequest;
use App\Services\Role\RoleService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class Role extends Controller
{
    public function __construct(public RoleService $roleService) {}

    public function index(RoleIndexRequest $request): JsonResponse
    {
        try {
            $data = $this->roleService->index($request->validated());

            return ReturnApi::success($data, 'Cargos listados com sucesso.');
        } catch (\Exception $e) {
            throw new ApiException('Erro ao listar cargos: '.$e->getMessage());
        }
    }

    public function show(RoleShowRequest $request): JsonResponse
    {
        try {
            $data = $this->roleService->show($request->validated());

            return ReturnApi::success($data, 'Role encontrada.');
        } catch (ModelNotFoundException $e) {
            throw new ApiException('Cargo não encontrado.', 404);
        } catch (\Exception $e) {
            throw new ApiException('Erro ao exibir role: '.$e->getMessage());
        }
    }

    public function store(RoleStoreRequest $request): JsonResponse
    {
        try {
            $data = $this->roleService->store($request->validated());

            return ReturnApi::success($data, 'Cargo cadastrado com sucesso.', 201);
        } catch (\Exception $e) {
            throw new ApiException('Erro ao cadastrar cargo: '.$e->getMessage());
        }
    }

    public function update(RoleUpdateRequest $request): JsonResponse
    {
        try {
            $data = $this->roleService->update($request->validated());

            return ReturnApi::success($data, 'Role atualizada com sucesso.');
        } catch (ModelNotFoundException $e) {
            throw new ApiException('Cargo não encontrado.', 404);
        } catch (ApiException $e) {
            throw new ApiException('Erro ao atualizar cargo: '.$e->getMessage());
        }
    }

    public function addPermissions(RoleAddPermissionsRequest $request): JsonResponse
    {
        try {
            $data = $this->roleService->addPermissions($request->validated());

            return ReturnApi::success($data, 'Permissões adicionadas ao cargo com sucesso.');
        } catch (ModelNotFoundException $e) {
            throw new ApiException('Cargo não encontrado.', 404);
        } catch (\Exception $e) {
            throw new ApiException('Erro ao adicionar permissões: '.$e->getMessage());
        }
    }

    public function delete(RoleDeleteRequest $request): JsonResponse
    {
        try {
            $this->roleService->delete($request->validated());

            return ReturnApi::success(null, 'Role excluída com sucesso.', 200);
        } catch (ModelNotFoundException $e) {
            throw new ApiException('Cargo não encontrado.', 404);
        } catch (ApiException $e) {
            throw new ApiException('Erro ao excluir cargo: '.$e->getMessage());
        }
    }
}
