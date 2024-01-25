<?php

namespace App\Http\Controllers;

use Auth;
use Exception;
use App\Http\Middleware\CheckPasswordProvisoria;
use App\Http\Controllers\NajWeb\HomeController;
use Illuminate\Support\Facades\DB;
use App\Exceptions\NajException;
use Illuminate\Support\Facades\URL;
use App\Models\UsuarioModel;
use JWTAuth;

/**
 * Controller base
 *
 * @author Deivid Staehr
 * @since 2019-11-26
 */
abstract class NajController extends Controller {

    /**
     * @var string
     */
    const STORE_ACTION = 'store';

    /**
     * @var string
     */
    const UPDATE_ACTION = 'update';

    /**
     * @var string
     */
    const DESTROY_ACTION = 'destroy';

    /**
     * @var string
     */
    const INDEX_ACTION = 'index';

    /**
     * @var string
     */
    const SHOW_ACTION = 'show';

    /**
     * @var string
     */
    const PAGINATE_ACTION = 'paginate';

    /**
     * @var string
     */
    const WEB_CONTEXT = 'web';

    /**
     * @var string
     */
    const API_CONTEXT = 'api';

    /**
     * @var string
     */
    protected $currentAction = null;

    /**
     * @var string
     */
    protected $context = 'web'; // web ,api

    protected $model = null;

    protected $controllerMonitoramento = null;

    protected $isDev = false;

    protected $inLockedTransaction = false;

    protected $inTransaction = false;

    protected $isChild = false;

    protected $childrens = [];

    public function __construct($checkToken = true, $checkPasswordProvisoria = true) {
        $this->onLoad();

        $this->isDev = env('APP_DEBUG');

        $currentUrl = URL::current();

        if (strpos($currentUrl, 'api/v')) {
            $this->context = self::API_CONTEXT;

			if($checkToken) {
                $this->middleware('check-jwt-token')->except(['login']);
            }
        }

        //VALIDA SE TA COMO PROVISORIA A SENHA DO USUÁRIO
        // A não ser que seja uma rota do aplicativo
        if($checkPasswordProvisoria && !strpos($currentUrl, 'api/v1/app')) {
            $this->middleware('check-password-provisoria');
        }
    }

    public function inURL($str) {
        if (strpos($this->getCurrentURL(), $str)) {
            return true;
        }

        return false;
    }

    public function getCurrentURL() {
        return URL::current();
    }

    public function index() {
        $this->setCurrentAction(self::INDEX_ACTION);

        $code = 200;

        try {
            $data = $this->getModel()->makePagination();
        } catch (Exception $e) {
            $code = 400;

            $data = ['mensagem' =>
                $this->extractMessageFromException($e)
            ];
        }

        return $this->resolveResponse($data, $code);
    }

    public function show($key) {
        $this->setCurrentAction(self::SHOW_ACTION);

        $code = 200;

        try {
            $data = $this->getModel()->getOneFromParam($key);
        } catch (Exception $e) {
            $code = 400;

            $data = ['mensagem' =>
                $this->extractMessageFromException($e)
            ];
        }

        return $this->resolveResponse($data, $code);
    }

    public function lockTransaction($lock = true) {
        $this->inLockedTransaction = $lock;
    }

    public function unlockTransaction() {
        $this->lockTransaction(false);
    }

    public function isChild($is = true) {
        $this->isChild = $is;
    }

    public function isNotChild() {
        $this->isChild(false);
    }

    public function begin() {
        if (!$this->inTransaction && !$this->inLockedTransaction) {
            DB::beginTransaction();

            $this->inTransaction = true;
        }
    }

    public function rollback() {
        if ($this->inTransaction && !$this->inLockedTransaction) {
            DB::rollback();

            $this->inTransaction = false;
        }
    }

    public function commit() {
        if ($this->inTransaction && !$this->inLockedTransaction) {
            DB::commit();

            $this->inTransaction = false;
        }
    }

    public function storeMany() {
        $this->begin();

        $this->isChild();

        $this->lockTransaction();

        $items = request()->get('items');

        $code = 200;

        $data = ['mensagem' => 'Operação realizada com sucesso.', 'items' => []];

        try {
            if (!$items) {
                $this->throwException('Parâmtro items não encontrado na requisiçao.');
            }

            $models = [];

            foreach ($items as $item) {
                $result = $this->store($item);

                if ($result['code'] !== 200) {
                    $this->throwException('Erro ao inserir os itens.');
                }

                $models[] = $result['data']['model'];
            }

            $this->unlockTransaction();

            $this->commit();

            $data['items'] = $models;
        } catch (Exception $e) {
            $code = 400;

            $data = ['mensagem' =>
                $this->extractMessageFromException($e)
            ];

            $this->unlockTransaction();

            $this->rollback();
        }

        $this->isNotChild();

        return $this->resolveResponse($data, $code);
    }

    public function setCurrentAction($currentAction) {
        $this->currentAction = $currentAction;
    }

    public function getCurrentAction() {
        return $this->currentAction;
    }

    /**
     * Exclui vários registros.
     * Obs: os registros deve ser separados por ';', e também devem
     * respeitar o método 'destroy'
     *
     * @param type $list
     */
    public function destroyMany($list) {
        $this->begin();

        $this->isChild();

        $this->lockTransaction();

        $code = 200;

        $data = ['mensagem' => 'Operação realizada com sucesso.'];

        try {
            $keys = explode(';', $list);

            if (empty($keys)) {
                $this->throwException('Chave de exclusão vazia ou inválida.');
            }

            foreach ($keys as $key) {
                $result = $this->destroy($key);

                if ($result['code'] !== 200) {
                    $this->throwException('Erro ao excluir os registros.');
                }
            }

            $this->unlockTransaction();

            $this->commit();
        } catch (Exception $e) {
            $code = 400;

            $data = ['mensagem' =>
                $this->extractMessageFromException($e)
            ];

            $this->unlockTransaction();

            $this->rollback();
        }

        $this->isNotChild();

        return $this->resolveResponse($data, $code);
    }

    /**
     * Excluir um registro.
     * Obs: a chave deve ser um objeto codificado em base64
     *
     * @param type $key
     * @return type
     */
    public function destroy($key) {
        $this->setCurrentAction(self::DESTROY_ACTION);

        $this->begin();

        $code = 200;

        $data = ['mensagem' => 'Operação realizada com sucesso.'];

        try {
            $this->handleItems();

            $this->getModel()->destroyFromParam($key);

            $this->commit();
        } catch (Exception $e) {
            $code = 400;

            $data = ['mensagem' =>
                $this->extractMessageFromException($e)
            ];

            $this->rollback();
        }

        return $this->resolveResponse($data, $code);
    }

    public function update($key) {
        $this->setCurrentAction(self::UPDATE_ACTION);

        $this->begin();

        $code = 200;

        $data = ['mensagem' => 'Registro alterado com sucesso.', 'model' => null];

        try {
            $toUpdate = $this->resolveValidate(
                $this->getModel()->getFilledAttributesWithoutKey()
            );

            $model = $this->getModel()->newInstanceFromKey($key);

            $totalUpdate   = 0;

            //utilizado no monitoramento
            $columnsUpdate = [];

            foreach ($toUpdate as $updateColumn => $updateValue) {
                if (trim($model->$updateColumn) !== trim($updateValue)) {
                    
                    //verificando se precisa registrar o monitoramento
                    if($this->getMonitoramentoController()) {
                        //adicionando no array de colunas alteradas a informação de como era e como ficou
                        $columnsUpdate[$updateColumn] = [
                            'before' => $model->$updateColumn,
                            'now'    => ($updateColumn != 'password') ? trim($updateValue) : 'Alteração na senha do usuário'
                        ];
                    }

                    $model->$updateColumn = trim($updateValue);

                    $totalUpdate++;
                }
            }

            //Vamos desativar essa parada por hora, pq ta fudendo os esquemas de relacionar as pessoas se não tiver pessoa rel ainda
            // if ($totalUpdate === 0) {
            //     $this->throwException('Nenhuma alteração encontrada.');
            // }

            $result = $model->save();

            if (is_string($result)) {
                $this->throwException('Erro ao atualizar o registro. ' . $result);
            }

            $this->handleItems($model);

            //verificando se precisa registrar o monitoramento
            if($this->getMonitoramentoController()) {
                $this->getMonitoramentoController()->storeMonitoramento(self::UPDATE_ACTION, $model, $columnsUpdate);
            }

            $this->commit();

            $data['model'] = $model;
        } catch (Exception $e) {
            $code = 400;

            $data = ['mensagem' =>
                $this->extractMessageFromException($e)
            ];

            $this->rollback();
        }

        return $this->resolveResponse($data, $code);
    }

    /**
     *
     * @return type
     */
    public function store($attrs = null) {
        $this->setCurrentAction(self::STORE_ACTION);

        $this->begin();

        $code = 200;

        $data = ['mensagem' => 'Registro inserido com sucesso.', 'model' => null];

        try {
            $toStore = $this->resolveValidate(
                $this->getModel()->getFilledAttributes($attrs)
            );

            if (empty($toStore)) {
                throw new NajException('Empty model.');
            }

            $data['model'] = $toStore;

            $model = $this->getModel()->newInstance();

            $model->fill($toStore);

            $result = $model->save();

            if (is_string($result)) {
                $this->throwException('Erro ao inserir o registro. ' . $result);
            }

            $this->handleItems($model);

            $data['model'] = $this->beforeStore($model);

            //verificando se precisa registrar o monitoramento
            if($this->getMonitoramentoController()) {
                $this->getMonitoramentoController()->storeMonitoramento(self::STORE_ACTION, $model);
            }

            $this->commit();

            $data['persisted'] = $model;
        } catch (Exception $e) {
            $code = 400;

            $data = ['mensagem' =>
                $this->extractMessageFromException($e)
            ];

            $this->rollback();
        }

        return $this->resolveResponse($data, $code);
    }

    /**
     *
     * @param NajException $e
     * @return type
     */
    protected function extractMessageFromException($e) {
        if ($e instanceof NajException) return $e->getMessage();

        return $this->isDev ? $e->getMessage() : 'Erro interno.';
    }

    /**
     *
     * @param type $data
     * @return type
     */
    public function resolveValidate($data) {
        switch ($this->currentAction) {
            case self::STORE_ACTION:
                $data = $this->validateStore($data);

                break;
            case self::UPDATE_ACTION:
                $data = $this->validateUpdate($data);

                break;
            case self::DESTROY_ACTION:
                $data = $this->validateDestroy($data);

                break;
            default:
                break;
        }

        return $data;
    }

    /**
     *
     * @param type $data
     * @param type $code
     * @return type
     */
    public function resolveResponse($data, $code = 200) {
        if ($this->isChild) return ['data' => $data, 'code' => $code];

        if ($this->context === self::WEB_CONTEXT) {
            if ($this->getCurrentAction() !== self::INDEX_ACTION) {
                return response()->json($data);
            }

            return $this->resolveWebContext(
                $data, $code
            );
        }

        return response()->json(['status_code' => $code, 'naj' => $data]);
    }

    /**
     *
     * @param type $data
     * @param type $code
     */
    protected function resolveWebContext($data, $code) { }

    /**
     *
     * @param type $model
     */
    public function setModel($model) {
        $this->model = $model;
    }

    /**
     *
     * @return type
     * @throws NajException
     */
    public function getModel() {
        if (!$this->model) {
            throw new NajException('Model não definido.');
        }

        return $this->model;
    }

    public function getUserFromToken() {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            $user = false;
        }

        return $user;
    }

    /**
     *
     * @return type
     */
    public function paginate() {
        //verificando se precisa registrar o monitoramento
        if($this->getMonitoramentoController() && !$this->isSkipsRoute()) {
            $this->getMonitoramentoController()->storeMonitoramento(self::PAGINATE_ACTION);
        }

        return $this->processPaginationAfter(
            $this->getModel()->makePagination()
        );
    }

    protected function isSkipsRoute() {
        $rota = request()->route()->getName();

        return in_array($rota, $this->skipsRoute());
    }

    protected function skipsRoute() {
        return [
            'usuario.relacionamento.paginate'
        ];
    }

    protected function processPaginationAfter($data) {
        return $data;
    }

    /**
     * Seta o controlador de monitoramento da rotina.
     */
    public function setMonitoramentoController($controller) {
        $this->controllerMonitoramento = $controller;
    }

    /**
     * Retorna o controlador de monitoramento da rotina, se não tiver nada é por que não precisa monitorar.
     */
    public function getMonitoramentoController() {
        if(!$this->controllerMonitoramento) return false;

        return $this->controllerMonitoramento;
    }

    /**
     *
     * @param type $value
     * @return type
     */
    public function parseQueryFilter($value) {
        return json_decode(base64_decode($value));
    }

    /**
     *
     * @param type $msg
     * @throws NajException
     */
    public function throwException($msg) {
        throw new NajException($msg);
    }

    /**
     * É chamado na inicialização da classe
     */
    public function onLoad() {}

    /**
     * Utilizado para manipular itens da
     * @param type $model
     */
    protected function handleItems($model = null) {}

    /**
     *
     * @param type $data
     * @return type
     */
    public function validateStore($data) { return $data; }

    public function validateUpdate($data) { return $data; }

    public function validateDestroy($data) { return $data; }

    public function beforeStore($model) { return $model ;}

}
