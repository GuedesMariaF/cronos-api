<?php

namespace App\Http\Controllers\Auth;

use App\Builder\ReturnApi;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Dedoc\Scramble\Attributes\Endpoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Auth extends Controller
{
    public function __construct(public AuthService $authService) {}
    
    #[Endpoint(operationId: 'loginEmailOrUsernameAndPassword', title: 'Login', description: 'Autenticação com **senha** usando `login` = e-mail. O campo legado `email` é aceito no lugar de `login` quando `login` não é enviado. Em **200**, `data.user` segue o schema **User Resource** (`App\\Http\\Resources\\User\\UserResource`).')]

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $data = $this->authService->login($request->validated());
            return ReturnApi::success($data, "Usuário logado com sucesso");
        } catch (ApiException $e) {
            return ReturnApi::error($e->getMessage(), $e->data, $e->getCode());
        }
    }
    public function me(): JsonResponse
    {
        try {
            $data = $this->authService->me();
            return ReturnApi::success($data, "Dados do usuário");
        } catch (ApiException $e) {
            return ReturnApi::error($e->getMessage(), $e->data, $e->getCode());
        }
    }

    public function refreshToken(): JsonResponse
    {
        try {
            $data = $this->authService->refreshToken();
            return ReturnApi::success($data, "Token atualizado com sucesso");
        } catch (ApiException $e) {
            return ReturnApi::error($e->getMessage(), $e->data, $e->getCode());
        }
    }
}
