<?php

namespace App\Http\Controllers\Auth;

use App\Builder\ReturnApi;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GoogleCallbackRequest;
use App\Services\Auth\SocialAuthService;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

#[Group(name: 'Autenticação Social', weight: 2)]
class SocialAuth extends Controller
{
    public function __construct(public SocialAuthService $socialAuthService) {}

    #[Endpoint(
        operationId: 'googleRedirect',
        title: 'Iniciar login com Google',
        description: 'Gera e retorna a URL de autorização do Google OAuth. O frontend deve redirecionar o usuário para `data.redirect_url`.',
    )]
   
    public function redirectToGoogle(): JsonResponse
    {
        try {
            $url = $this->socialAuthService->redirectToGoogle();

            return ReturnApi::success(['redirect_url' => $url], 'URL de autenticação gerada.');
        } catch (ApiException $e) {
            return ReturnApi::error($e->getMessage(), $e->data, $e->getCode());
        }
    }

    #[Endpoint(
        operationId: 'googleCallback',
        title: 'Callback Google OAuth',
        description: 'Recebido pelo Google após o usuário autorizar. Cria ou encontra o usuário, gera o JWT e redireciona para `{CLIENT_URL}/auth/callback?token={jwt}&refresh_expires_in={segundos}`. Em caso de falha, redireciona para `{CLIENT_URL}/auth/error?message={erro}`.',
    )]
    #[Response(status: 302, description: 'Sucesso: redireciona para `{CLIENT_URL}/auth/callback?token={jwt}&refresh_expires_in={segundos}`. Falha: redireciona para `{CLIENT_URL}/auth/error?message={erro}`.', mediaType: 'text/html')]
    public function handleGoogleCallback(GoogleCallbackRequest $request): JsonResponse|RedirectResponse
    {
        $frontendUrl = env('CLIENT_URL');

        try {
            $data = $this->socialAuthService->handleGoogleCallback();
                               
                                                        

            return ReturnApi::success($data, "{$frontendUrl}/auth/callback?token={$data['token']}&refresh_expires_in={$data['refresh_expires_in']}");
        } catch (ApiException $e) {
            return redirect("{$frontendUrl}/auth/error?message=".urlencode($e->getMessage()));
        } catch (\Exception $e) {
            Log::error('Google OAuth error: '.$e->getMessage(), ['exception' => $e]);
            return redirect("{$frontendUrl}/auth/error?message=".urlencode('Erro interno ao autenticar com o Google.'));
        }
    }
}
