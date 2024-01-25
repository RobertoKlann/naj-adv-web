<?php

namespace App\Models;

use App\Models\NajModel;
use Illuminate\Support\Facades\DB;

/**
 * Modelo de dashboard dos usuários.
 *
 * @since 2021-06-05
 */
class DashboardUsuarioModel extends NajModel {

    protected function loadTable() {
        $this->setTable('usuarios');
   	}

    public function getDataByGeral() {
        $data = DB::select("
            select date_format(m.data_hora, '%Y-%m') as 'Ano_Mes',
                   count(0)as 'Actions'
              from monitora m
        inner join pessoa p on p.codigo = m.codigo_usuario
             where p.codigo <> 0
               and date_format(m.data_hora, '%Y-%m') >= date_format(date_sub(current_date, interval 12 MONTH), '%Y-%m') # últimos 12 meses
          group by Ano_Mes
          order by Ano_Mes desc
        ");

        $chart = [];
        $period = ['x'];
        foreach ($data as $item) {
            $names[] = 'Todos usuários';
            $chartTemporary['Todos usuários'][$item->Ano_Mes] = $item->Actions;

            if (!in_array($item->Ano_Mes . '-01', $period)) {
                $period[] = $item->Ano_Mes . '-01';
                $periodAttributeFake[] = $item->Ano_Mes;
            }
        }

        foreach (array_unique($names) as $name)
            $namesValue[] = $name;

        foreach ($chartTemporary as $key => $user) {
            foreach ($periodAttributeFake as $month) {
                if(!array_key_exists($month, $user))
                    $chartTemporary[$key][$month] = 0;
            }
        }

        foreach ($chartTemporary as $key => $itemChart) {
            krsort($itemChart);

            $chart[$key] = array_values($itemChart);
        }

        return ['period' => $period, 'data' => $chart, 'names' => $namesValue];
    }

    public function getDataByUserTypeUser($limit) {
        $data = DB::select("
            select date_format(m.data_hora,'%Y-%m') as 'Ano_Mes', 
                   p.codigo as 'Código',
                   p.nome as 'Usuario',
                   u.usuario_tipo_id as 'TipoUser',
                   count(0) as 'Actions'
              from monitora m
        inner join pessoa p
                on p.codigo = m.codigo_usuario
        inner join pessoa_rel_usuarios pru
                on pru.pessoa_codigo = p.codigo
        inner join usuarios u
                on u.id = pru.usuario_id
             where p.codigo <> 0
               and date_format(m.data_hora,'%Y-%m') >= date_format(date_sub(current_date,interval 12 MONTH),'%Y-%m') # últimos 12 meses
          group by Ano_Mes, Usuario
          order by Ano_Mes desc, Actions desc
        ");

        $chart = [];
        $period = ['x'];
        foreach ($data as $item) {
            $names[] = $item->Usuario;
            $chartTemporary[$item->Usuario][$item->Ano_Mes] = $item->Actions;

            if (!in_array($item->Ano_Mes . '-01', $period)) {
                $period[] = $item->Ano_Mes . '-01';
                $periodAttributeFake[] = $item->Ano_Mes;
            }
                
        }

        foreach (array_unique($names) as $name)
            $namesValue[] = $name;

        if ($limit != '*')
            $chartTemporary = array_slice($chartTemporary, 0, (int) $limit);

        foreach ($chartTemporary as $key => $user) {
            foreach ($periodAttributeFake as $month) {
                if(!array_key_exists($month, $user))
                    $chartTemporary[$key][$month] = 0;
            }
        }

        foreach ($chartTemporary as $key => $itemChart) {
            krsort($itemChart);

            $chart[$key] = array_values($itemChart);
        }

        return ['period' => $period, 'data' => $chart, 'names' => $namesValue];
    }

    public function getDataByUserTypeClient($limit) {
        $data = DB::select("
            select date_format(m.data_hora,'%Y-%m') as 'Ano_Mes', 
                   p.codigo as 'Código', p.nome as 'Usuario',
                   u.usuario_tipo_id as 'TipoUser',
                   count(0) as 'Actions'
              from monitora m
        inner join pessoa p
                on p.codigo = m.codigo_usuario
        inner join pessoa_rel_clientes precl
                on precl.pessoa_codigo = p.codigo
        inner join usuarios u
                on u.id = precl.usuario_id
             where p.codigo <> 0
               and date_format(m.data_hora,'%Y-%m') >= date_format(date_sub(current_date,interval 12 MONTH),'%Y-%m') # últimos 12 meses
          group by Ano_Mes, Usuario
          order by Ano_Mes desc, Actions desc
        ");

        $chart = [];
        $period = ['x'];
        foreach ($data as $item) {
            $names[] = $item->Usuario;
            $chartTemporary[$item->Usuario][$item->Ano_Mes] = $item->Actions;

            if (!in_array($item->Ano_Mes . '-01', $period)) {
                $period[] = $item->Ano_Mes . '-01';
                $periodAttributeFake[] = $item->Ano_Mes;
            }
        }

        foreach (array_unique($names) as $name)
            $namesValue[] = $name;

        if ($limit != '*') {
            $namesValue = array_slice($namesValue, 0, (int) $limit);
            $chartTemporary = array_slice($chartTemporary, 0, (int) $limit);
        }

        foreach ($chartTemporary as $key => $user) {
            foreach ($periodAttributeFake as $month) {
                if(!array_key_exists($month, $user))
                    $chartTemporary[$key][$month] = 0;
            }
        }

        foreach ($chartTemporary as $key => $itemChart) {
            krsort($itemChart);

            $chart[$key] = array_values($itemChart);
        }

        return ['period' => $period, 'data' => $chart, 'names' => $namesValue];
    }

    public function getDataByTypeSystem() {
        $data = DB::select("
        (select
        date_format(m.data_hora,'%Y-%m') as 'Ano_Mes', 
        ('APP do Cliente') as 'Area',
        count(0) as 'Actions'
        from monitora m
        inner join modulos md on md.id = m.id_modulo
        inner join pessoa_rel_clientes precl on precl.pessoa_codigo = m.CODIGO_USUARIO
        inner join usuarios u on u.id = precl.usuario_id
        where md.id in(176,183,181,184,185,182)
        and m.codigo_usuario <> 0
        and date_format(m.data_hora,'%Y-%m') >= date_format(date_sub(current_date,interval 12 MONTH),'%Y-%m') # últimos 12 meses
    group by Ano_Mes) # últimos 12 meses
            union
            (select
            date_format(m.data_hora,'%Y-%m') as 'Ano_Mes', 
            ('Área WEB') as 'Area',
            count(0) as 'Actions'
            from monitora m
            inner join modulos md on md.id = m.id_modulo
            inner join pessoa_rel_clientes precl on precl.pessoa_codigo = m.CODIGO_USUARIO
            inner join usuarios u on u.id = precl.usuario_id
            where md.id in(119, 175,188,178,180,177,179)
            and m.codigo_usuario <> 0
            and date_format(m.data_hora,'%Y-%m') >= date_format(date_sub(current_date,interval 12 MONTH),'%Y-%m') # últimos 12 meses
            group by Ano_Mes)
            order by Ano_Mes desc
        ");

        $chart = [];
        $period = ['x'];
        foreach ($data as $item) {
            $names[] = $item->Area;
            $chartTemporary[$item->Area][$item->Ano_Mes] = (int) $item->Actions;

            if (!$item->Ano_Mes) {
                $period[] = date("Y") . '-01-01';
                $periodAttributeFake[] = date("Y") . '-01-01';

                continue;
            }

            if (!in_array($item->Ano_Mes . '-01', $period)) {
                $period[] = $item->Ano_Mes . '-01';
                $periodAttributeFake[] = $item->Ano_Mes;
            }
        }

        foreach (array_unique($names) as $name)
            $namesValue[] = $name;

        foreach ($chartTemporary as $key => $user) {
            foreach ($periodAttributeFake as $month) {
                if(!array_key_exists($month, $user))
                    $chartTemporary[$key][$month] = 0;
            }
        }

        foreach ($chartTemporary as $key => $itemChart) {
            krsort($itemChart);

            $chart[$key] = array_values($itemChart);
        }

        return ['period' => $period, 'data' => $chart, 'names' => $namesValue];
    }

    public function getDataByDispositivo() {
        
    }

}