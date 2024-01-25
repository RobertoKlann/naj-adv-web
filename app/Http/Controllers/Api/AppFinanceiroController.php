<?php

namespace App\Http\Controllers\Api;

//use Illuminate\Support\Facades\URL;
use App\Http\Controllers\NajController;
use App\Http\Traits\MonitoraTrait;
use App\Models\AppFinanceiroModel;

/**
 * Controller de processos (aplicativo)
 *
 * @since 2020-04-27
 */
class AppFinanceiroController extends NajController {
    
    use MonitoraTrait;

    public function onLoad() {
        $AppFinanceiroModel = new AppFinanceiroModel;

        if ($this->inURL('/pagar')) {
            $AppFinanceiroModel->setToPay();
            $AppFinanceiroModel->addRawFilter("(CONTA.TIPO = 'R' AND CONTA.PAGADOR <> '2')");
            $AppFinanceiroModel->addRawFilter("(N.TIPO_SUB NOT IN ('M', 'J', 'C') OR N.TIPO_SUB IS NULL)");
        } else if ($this->inURL('/receber')) {
            $AppFinanceiroModel->setToReceive();
            $AppFinanceiroModel->addRawFilter("((CONTA.TIPO = 'R' AND CONTA.PAGADOR = '2') OR CONTA.TIPO = 'P')");
            $AppFinanceiroModel->addRawFilter("(N.TIPO_SUB NOT IN ('M', 'J', 'C') OR N.TIPO_SUB IS NULL)");
        }

        $user = $this->getUserFromToken();
        $codigoCliente = $AppFinanceiroModel->getRelacionamentoClientes($user->id);

        if (!$codigoCliente) {
            $this->throwException('Usuário sem relacionamento com cliente');
        }

        $AppFinanceiroModel->addRawFilter("CONTA.CODIGO_PESSOA IN ({$codigoCliente})");
        $AppFinanceiroModel->addRawFilter("CONTA.DISPONIVEL_CLIENTE = 'S'");

        $this->setModel($AppFinanceiroModel);
    }
    
    public function monitoracao() {
        return $this->resolveResponse(
            $this->monitora(
                'AcessoAreaClienteAPPFinanceiro',
                'Pesquisou por dados na rotina Financeiro'
            )
        );
    }

}
