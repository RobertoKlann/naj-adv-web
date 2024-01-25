<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\NajController;
use App\Models\FinanceiroModel;

/**
 * Controlador do Financeiro.
 *
 * @since 2020-08-10
 */
class FinanceiroController extends NajController {

    public function onLoad() {
        $FinanceiroModel = new FinanceiroModel;

        // A PAGAR
        $FinanceiroModel->addRawFilter("(
            CONTA.TIPO = 'R' AND (CONTA.PAGADOR = '1' OR CONTA.PAGADOR IS NULL)
        )");

        // A RECEBER
        //$AppFinanceiroModel->addRawFilter("(
        //    CP.DATA_PAGAMENTO IS NOT NULL
        //    OR CP.ID IN (
        //        SELECT ID_PARCELA FROM CONTA_PARCELA_PARCIAL
        //    )
        //)");

        $this->setModel($FinanceiroModel);
    }

}