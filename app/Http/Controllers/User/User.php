<?php

namespace App\Http\Controllers\User;

use App\Builder\ReturnApi;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserDeleteRequest;
use App\Http\Requests\User\UserIndexRequest;
use App\Http\Requests\User\UserShowRequest;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Services\User\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class User extends Controller
{
    public function __construct(public UserService $userService) {}

  
    public function index(UserIndexRequest $request): JsonResponse
    {
        try {
            $data = $this->userService->index($request->validated());

            return ReturnApi::success($data, 'Usuários listados com sucesso.');
        } catch (\Exception $e) {
            throw new ApiException('Erro ao listar usuários: '.$e->getMessage());
        }
    }

    public function show(UserShowRequest $request): JsonResponse
    {
        try {
            $data = $this->userService->show($request->validated());

            return ReturnApi::success($data, 'Usuário encontrado.');
        } catch (ModelNotFoundException $e) {
            throw new ApiException('Usuário não encontrado.', 404);
        } catch (\Exception $e) {
            throw new ApiException('Erro ao exibir usuário: '.$e->getMessage());
        }
    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        try {
            $data = $this->userService->store($request->validated());

            return ReturnApi::success($data, 'Usuário cadastrado com sucesso.', 201);
        } catch (\Exception $e) {
            throw new ApiException('Erro ao cadastrar usuário: '.$e->getMessage());
        }
    }

    public function update(UserUpdateRequest $request): JsonResponse
    {
        try {
            $data = $this->userService->update($request->validated());

            return ReturnApi::success($data, 'Usuário atualizado com sucesso.');
        } catch (ModelNotFoundException $e) {
            throw new ApiException('Usuário não encontrado.', 404);
        } catch (ApiException $e) {
            throw new ApiException('Erro ao atualizar usuário: '.$e->getMessage());
        }
    }

    public function delete(UserDeleteRequest $request): JsonResponse
    {
        try {
            $this->userService->delete($request->validated());

            return ReturnApi::success(null, 'Usuário excluído com sucesso.', 200);
        } catch (ModelNotFoundException $e) {
            throw new ApiException('Usuário não encontrado.', 404);
        } catch (ApiException $e) {
            throw new ApiException('Erro ao excluir usuário: '.$e->getMessage());
        }
    }
}
