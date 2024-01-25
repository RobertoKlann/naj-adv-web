<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de processos parados.
 *
 * @since 2021-05-18
 */
class DashboardEspacoDiscoModel extends NajModel {

    protected function loadTable() {
        $this->setTable('prc');
   	}

    public function getDataToDashboard() {
        $data = DB::select("
            select 'Anexos_Pessoas' as 'Tabela',
                    convert( if(sum(FILE_SIZE) is null,'0,00', sum(FILE_SIZE)/1024), decimal(12,2) ) AS 'KB',
                    convert( if(sum(FILE_SIZE) is null,'0,00', ((sum(FILE_SIZE)/1024)/1024)) , decimal(12,2) ) AS 'MB',
                    convert( if(sum(FILE_SIZE) is null,'0,00', (((sum(FILE_SIZE)/1024)/1024)/1024)), decimal(12,2) ) AS 'GB'
              from  pessoa_anexos
                    union
                    select 'Anexos_Processos' as 'Tabela',
                    convert( if(sum(FILE_SIZE) is null,'0,00', sum(FILE_SIZE)/1024), decimal(12,2) ) AS 'KB',
                    convert( if(sum(FILE_SIZE) is null,'0,00', ((sum(FILE_SIZE)/1024)/1024)) , decimal(12,2) ) AS 'MB',
                    convert( if(sum(FILE_SIZE) is null,'0,00', (((sum(FILE_SIZE)/1024)/1024)/1024)), decimal(12,2) ) AS 'GB'
              from  prc_anexos
                    union
                    select 'Anexos_Atividades' as 'Tabela',
                    convert( if(sum(FILE_SIZE) is null,'0,00', sum(FILE_SIZE)/1024), decimal(12,2) ) AS 'KB',
                    convert( if(sum(FILE_SIZE) is null,'0,00', ((sum(FILE_SIZE)/1024)/1024)) , decimal(12,2) ) AS 'MB',
                    convert( if(sum(FILE_SIZE) is null,'0,00', (((sum(FILE_SIZE)/1024)/1024)/1024)), decimal(12,2) ) AS 'GB'
              from  atividade_anexos
                    union
                    select 'Anexos_Contas' as 'Tabela',
                    convert( if(sum(FILE_SIZE) is null,'0,00', sum(FILE_SIZE)/1024), decimal(12,2) ) AS 'KB',
                    convert( if(sum(FILE_SIZE) is null,'0,00', ((sum(FILE_SIZE)/1024)/1024)) , decimal(12,2) ) AS 'MB',
                    convert( if(sum(FILE_SIZE) is null,'0,00', (((sum(FILE_SIZE)/1024)/1024)/1024)), decimal(12,2) ) AS 'GB'
              from  conta_anexos
                    union
                    select 'Textos' as 'Tabela',
                    convert( if(sum(FILE_SIZE) is null,'0,00', sum(FILE_SIZE)/1024), decimal(12,2) ) AS 'KB',
                    convert( if(sum(FILE_SIZE) is null,'0,00', ((sum(FILE_SIZE)/1024)/1024)) , decimal(12,2) ) AS 'MB',
                    convert( if(sum(FILE_SIZE) is null,'0,00', (((sum(FILE_SIZE)/1024)/1024)/1024)), decimal(12,2) ) AS 'GB'
              from  texto_versao
             where codigo_texto is not null
                    union
                    select 'Textos_Modelo' as 'Tabela',
                    convert( if(sum(FILE_SIZE) is null,'0,00', sum(FILE_SIZE)/1024), decimal(12,2) ) AS 'KB',
                    convert( if(sum(FILE_SIZE) is null,'0,00', ((sum(FILE_SIZE)/1024)/1024)) , decimal(12,2) ) AS 'MB',
                    convert( if(sum(FILE_SIZE) is null,'0,00', (((sum(FILE_SIZE)/1024)/1024)/1024)), decimal(12,2) ) AS 'GB'
              from  texto_versao where codigo_modelo is not null
          ORDER BY KB DESC
        ");

        return $data;
    }

}