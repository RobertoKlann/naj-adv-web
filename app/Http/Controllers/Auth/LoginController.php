<?php

namespace App\Http\Controllers\Auth;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Middleware\CheckJwtToken;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\DB;
use App\Models\EmpresaModel;

/*
|--------------------------------------------------------------------------
| Login Controller
|--------------------------------------------------------------------------
|
| This controller handles authenticating users for the application and
| redirecting them to your home screen. The controller uses a trait
| to conveniently provide its functionality to your applications.
|
*/
class LoginController extends Controller {

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/naj/home';

    public function username() {
        return 'login';
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('guest')->except('logout');
    }

    /**
     * @return view
     */
    public function index() {
        return $this->showLoginForm();
    }

    /**
     * {@inheritdoc}
     */
    protected function guard() {
        return Auth::guard('web');
    }

    public function sendLoginResponse(Request $request) {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        $is_nelson     = request()->get('is_nelson');
        $is_naj_antigo = request()->get('is_naj_antigo');

        //Se veio do naj antigo
        if($is_naj_antigo == 1) {
            $token = JWTAuth::fromUser($this->guard()->user());

            $responsecode = 200;
            $header = [
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            ];

            $licenciado = str_replace('&', 'E',  $this->getLicenciado());
            $nome = str_replace('&', 'E', $this->guard()->user()->nome);
            $apelido = str_replace('&', 'E', $this->guard()->user()->apelido);

            return response()->json(['token' => $token, 'licenciado' => $licenciado, 'id_usuario' => $this->guard()->user()->id, 'nome_usuario' => $nome, 'apelido_usuario' => $apelido, 'senha_provisoria' => $this->guard()->user()->senha_provisoria, 'usuario_tipo_id' => $this->guard()->user()->usuario_tipo_id] , $responsecode, $header, JSON_UNESCAPED_UNICODE);
        }

		if($is_nelson == 1) {
            $token = JWTAuth::fromUser($this->guard()->user());
			return response()->json(['token' => $token]);
		}

        $tete = $this->redirectPath();
        return $this->authenticated($request, $this->guard()->user())
                ?: redirect()->intended($this->redirectPath());
    }

    /**
     * Utilizado apenas para fins de teste e verificação de erro do token.
     */
    public function token() {
        $user = JWTAuth::parseToken()->authenticate();

        return $user;
    }

    /**
     * Método utilizado pelo naj antigo para validar o token.
     *
     * @return boolean
     */
    public function validaLogin() {
        $CheckJwtToken = new CheckJwtToken();

        return $CheckJwtToken->validaLogin();
    }

    private function getLicenciado() {
        $empresa = EmpresaModel::where('CODIGO', 1)->first();

        return $empresa->NOME;
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request) {
        return $request->only(
            $this->username(),
            'password',
            'status'
        );
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request) {
        $request->validate([
            $this->username() => 'required|string',
            'password'        => 'required|string',
            'status'          => 'required|string'
        ]);
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm() {
        if(env('URL_LOGIN_NAJ_ANTIGO')) {
            return redirect(env('URL_LOGIN_NAJ_ANTIGO'));
        }

        return view('auth.login');
    }

    protected function attemptLogin(Request $request) {
        $usuario = DB::select("
            SELECT *
              FROM usuarios
             WHERE TRUE
               AND login = '" . request()->get('login') . "'
               AND usuario_tipo_id IN(0, 1, 2, 4)
        ");

        if(is_array($usuario) && count($usuario) == 0) {
            return false;
        }

        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

}