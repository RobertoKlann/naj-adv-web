<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use App\Models\NajModel;

class UsuarioModel extends NajModel implements
    JWTSubject,
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract {
    
    use Notifiable, Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail;
    
    protected function loadTable() {
        $this->setTable('usuarios');

        $this->addColumn('id', true);
        $this->addColumn('usuario_tipo_id')->addJoin('usuarios_tipo');
        $this->addColumn('login');
        $this->addColumn('password')->setHidden();
        $this->addColumn('status');
        $this->addColumn('data_inclusao');
        $this->addColumn('data_baixa');
        $this->addColumn('email_recuperacao');
        $this->addColumn('mobile_recuperacao');
        $this->addColumn('nome');
        $this->addColumn('apelido');
        $this->addColumn('cpf');
        $this->addColumn('senha_provisoria');
        $this->addColumn('ultimo_acesso');
        $this->addColumn('smtp_host');
        $this->addColumn('smtp_login');
        $this->addColumn('smtp_senha');
        $this->addColumn('smtp_porta');
        $this->addColumn('smtp_ssl');

        $this->addAllColumns();

        $this->setRawBaseSelect("
                SELECT [COLUMNS]
                  FROM usuarios
                  JOIN usuarios_tipo
                    ON usuarios_tipo.id = usuarios.usuario_tipo_id
        ");
        
        $this->setOrder('status ASC, usuario_tipo_id ASC, usuarios.id ASC');
        
        $this->primaryKey = 'id';
    }

    public function addAllColumns() {
        $this->addRawColumn("usuarios_tipo.tipo");
    }

    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims() {
        return [];
    }

    public function setPasswordAttribute($password) {
        if($password !== null && $password !== "" && !(request()->get('tokenInstall')) && !(request()->get('usuarioVeioDoCpanel'))) {
            $this->attributes['password'] = bcrypt($password);
        } else {
            $this->attributes['password'] = $password;
        }
    }

    public function getCodigoGrupoFromPessoa($grupo) {
        return DB::select("
            SELECT codigo
              FROM pessoa_grupo
             WHERE TRUE
               AND grupo LIKE'%{$grupo}%';
        ");
    }

    public function dataEstatisticasUser($userId) {
        $person = (new PessoaRelacionamentoUsuarioModel)->getRelacionamentosUsuario($userId);

        if (!$person)
            return ['errorMessage' => 'Não foi encontrado uma pessoa para esse usuário! Verifique os relacionamentos e se necessário inclua um novo!'];

        foreach ($person as $model)
            $persons[] = $model->pessoa_codigo;

        $person = implode(', ', $persons);

        $data = DB::select("
            select date_format(m.data_hora,'%Y-%m') as 'Ano_Mes', 
                   p.codigo as 'Código', p.nome as 'Usuario',
                   count(0)as 'Actions'
              from monitora m
        inner join pessoa p
                on p.codigo = m.codigo_usuario
             where p.codigo <> 0
               and date_format(m.data_hora, '%Y-%m') >= date_format(date_sub(current_date, interval 12 MONTH), '%Y-%m') # últimos 12 meses
               and codigo_usuario IN ({$person})
          group by Ano_Mes,
                   Usuario
          order by Ano_Mes desc,
                   Actions desc
        ");

        $chart = [];
        $period = [];
        $periodAttributeFake = [];
        foreach ($data as $item) {
            $names[] = $item->Usuario;
            $chartTemporary[$item->Usuario][$item->Ano_Mes] = $item->Actions;

            if (!in_array($item->Ano_Mes . '-01', $period)) {
                $period[] = $item->Ano_Mes . '-01';
                $periodAttributeFake[] = $item->Ano_Mes;
            }
        }

        if (count($data) == 0)
            return [];

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


    public function getStatistics($userId) {
        $data = DB::select("
            select date_format(m.data_hora, '%Y-%m') as 'Ano_Mes', 
                   ('APP do Cliente') as 'Area',
                   count(0) as 'Actions'
              from monitora m
            inner join modulos md on md.id = m.id_modulo
            inner join pessoa_rel_clientes precl on precl.pessoa_codigo = m.CODIGO_USUARIO
            inner join usuarios u on u.id = precl.usuario_id
            where md.id in(176,183,181,184,185,182)
              and m.codigo_usuario <> 0
              and precl.usuario_id = {$userId} #ID DO USUÁRIO
              and date_format(m.data_hora,'%Y-%m') >= date_format(date_sub(current_date,interval 12 MONTH),'%Y-%m') # últimos 12 meses

            union

            select date_format(m.data_hora,'%Y-%m') as 'Ano_Mes', 
                   ('Área WEB') as 'Area',
                   count(0) as 'Actions'
              from monitora m
            inner join modulos md on md.id = m.id_modulo
            inner join pessoa_rel_clientes precl on precl.pessoa_codigo = m.CODIGO_USUARIO
            inner join usuarios u on u.id = precl.usuario_id
            where md.id in(119, 175,188,178,180,177,179)
              and m.codigo_usuario <> 0
              and date_format(m.data_hora,'%Y-%m') >= date_format(date_sub(current_date,interval 12 MONTH),'%Y-%m') # últimos 12 meses
              and precl.usuario_id = {$userId} #ID DO USUÁRIO
            group by Ano_Mes
            order by Ano_Mes desc
        ");

        $chart = [];
        $period = ['x'];
        $names = [];
        $namesValue = [];
        $chartTemporary = [];
        $periodAttributeFake = [];
        foreach ($data as $item) {
            if (!$item->Ano_Mes)
                continue;

            $names[] = $item->Area;
            $chartTemporary[$item->Area][$item->Ano_Mes] = $item->Actions;


            if (!in_array($item->Ano_Mes . '-01', $period)) {
                $period[] = $item->Ano_Mes . '-01';
                $periodAttributeFake[] = $item->Ano_Mes;
            }
        }

        if (count($data) == 0)
            return [];

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

    public function getDataByUserAdmin($userId) {
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
               and u.id = {$userId}
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
    
}