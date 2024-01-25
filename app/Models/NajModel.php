<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Exceptions\NajException;
use Exception;
use stdClass;

/**
 * Model base
 *
 * @author Deivid Staehr
 * @since 2019-11-26
 */
abstract class NajModel extends Model {

    /**
     * @var boolean
     */
    public $incrementing = false;

    /**
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Número de itens por página da rota de paginação
     *
     * @var integer
     */
    protected $itemsPerPage = 50;

    /**
     * @var integer
     */
    protected $page = 1;

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var array
     */
    protected $selection = [];

    /**
     * @var array
     */
    protected $lastColumn = [];

    /**
     * @var array
     */
    protected $joins = [];

    /**
     * @var array
     */
    protected $filterOptions = [];

    /**
     * @var string|null
     */
    protected $order = null;

    /**
     * @var string|null
     */
    protected $having = null;

    protected $rawBaseSelect = false;

    protected $rawColumns = false;

    protected $fixedFilters = [];

    protected $rawFilters = [];

    /**
     * Define se o paginate deve retornar o sql
     * @var boolean
     */
    protected $withSql = false;

    public function __construct(array $attributes = []) {
        $this->loadTable();

        $limit = (integer) request()->query('limit');

        $primaryKey = is_array($this->primaryKey) ? $this->primaryKey : [$this->primaryKey];

        if ($limit && $limit > 0 && $limit <= 100) {
            $this->itemsPerPage = $limit;
        }

        parent::__construct($attributes);

        /* if (empty($this->columns)) {
            $this->throwException('Nenhuma coluna definida.');
        } */

        if (!$this->primaryKey) {
            $this->throwException('Chave primária não definida.');
        }

        if (!$this->table) {
            $this->throwException('Tabela não definida.');
        }

        if (!$this->order) {
            $this->setOrder($primaryKey[0], 'asc');
        }

        $this->loadFilterOptions();
    }

    /**
     *
     */
    protected function loadFilterOptions() {
        $this->filterOptions['I']  = '=';
        $this->filterOptions['C']  = 'like';
        $this->filterOptions['B']  = 'between';
        $this->filterOptions['BT'] = '>';
        $this->filterOptions['BE'] = '>=';
        $this->filterOptions['LT'] = '<';
        $this->filterOptions['LE'] = '<=';
        $this->filterOptions['D']  = '!=';
        $this->filterOptions['N']  = 'null';
        $this->filterOptions['NN'] = 'not null';
        $this->filterOptions['CF'] = 'custom';
        $this->filterOptions['IN'] = 'in';
    }

    public function addFixedFilter($column, $value1, $value2 = false, $option = 'I') {
        if (!in_array($option, array_keys($this->filterOptions))) {
            $this->throwException('Opção de filtro inválida.');
        }

        $filter = new stdClass;
        $filter->col = $column;
        $filter->op = $option;
        $filter->val = $value1;

        if ($option) {
            $filter->val2 = $value2;
        }

        $this->fixedFilters[] = $filter;
    }

    public function getFixedFilters() {
        return $this->fixedFilters;
    }

    public function setRawBaseSelect($baseSelect) {
        $this->rawBaseSelect = $baseSelect;
    }

    public function getRawBaseSelect() {
        return $this->rawBaseSelect;
    }

    public function addRawFilter($filter) {
        $this->rawFilters[] = $filter;
    }

    public function getRawFilters() {
        return $this->rawFilters;
    }

    public function addRawColumn($column) {
        $this->selection['raw'][] = $column;

        return $this;
    }

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(Builder $query) {
        $keys = $this->getKeyName();

        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the primary key value for a save query.
     *
     * @param mixed $keyName
     * @return mixed
     */
    protected function getKeyForSaveQuery($keyName = null) {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }

    public function parseQueryFilter($value) {
        $return = json_decode(base64_decode($value));
        return $return;
    }

    /**
     *
     * @return type
     */
    public function makePagination() {
        $limit = $this->itemsPerPage;

        $pageQuery = request()->query('page');

        $page = $pageQuery > 0 ? (integer) $pageQuery : 1;

        $offset = ($page * $limit) - $limit;

        $baseSelect = $this->getBaseSelect();

        $queryFilters =  request()->query('f');

        if ($queryFilters) {
            $queryFilters = $this->parseQueryFilter($queryFilters);
        }

        $filters = $this->resolveFilters($queryFilters);

        $baseSelect .= $filters['where'];

        // registros
        $selectForPagination = $this->fillSelectForPagination($baseSelect, $limit, $offset);

        // dd($selectForPagination);

        $data = DB::select($selectForPagination, $filters['values']);

        // contador
        $selectForCount = $this->fillSelectForPaginationCounter($baseSelect);
        $counter = DB::select($selectForCount, $filters['values']);

        $dev = null;

        if ($this->withSql || request()->query('withSql') == '1') {
            $dev = [
                'sql'    => $selectForPagination,
                'values' => $filters['values'],
            ];
        }

        return [
            'total'     => $counter[0]->total,
            'pagina'    => $page,
            'limite'    => $limit,
            'resultado' => $data,
            'dev'       => $dev
        ];
    }

    public function setWithSql($withSql) {
        $this->withSql = $withSql;
    }

    public function getWithSql() {
        return $this->withSql;
    }

    public function getSelectSql() {
        $baseSelect = $this->getBaseSelect();

        $queryFilters =  request()->query('f');

        if ($queryFilters) {
            $queryFilters = $this->parseQueryFilter($queryFilters);
        }

        $filters = $this->resolveFilters($queryFilters);

        $baseSelect .= $filters['where'];

        $withColumns = $this->fillWithColumns($baseSelect);

        $having = "";

        if ($this->having) {
            $having = "HAVING ({$this->having})";
        }

        $sql = "{$withColumns}
                 {$having}
            ORDER BY {$this->getOrder()}";

        return $sql;
    }

    /**
     * Contas todos os registros da tabela
     * @return Array
     */
    public function countAll($filters = []) {
        $baseSelect = $this->getBaseSelect();

        $filters = $this->resolveFilters($filters);

        $baseSelect .= $filters['where'];

        $counter = DB::select(
            $this->fillSelectForPaginationCounter($baseSelect),
            $filters['values']
        );

        return $counter[0]->total;
    }

    /**
     *
     * @param type $keys
     * @return type
     */
    public function newInstanceFromKey($keys) {
        $attributes = (array) $this->parseQueryFilter($keys);

        $columns = array_keys($attributes);

        $values = array_values($attributes);

        $where = $this->where($columns[0], $values[0]);

        array_shift($attributes);

        foreach ($attributes as $column => $value) {
            $where->where($column, $value);
        }

        return $where->first();
    }

    /**
     *
     * @return type
     */
    public function getFilledAttributesWithoutKey() {
        $keys = is_array($this->primaryKey) ? $this->primaryKey : [$this->primaryKey];;

        return array_filter(
            $this->getFilledAttributes(),
            function($index) use ($keys) {
                return !in_array($index, $keys);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     *
     * @param type $baseSelect
     * @param type $limit
     * @param type $offset
     * @return type
     */
    public function fillSelectForPagination($baseSelect, $limit, $offset) {
        $withColumns = $this->fillWithColumns($baseSelect);

        $having = "";

        if ($this->having) {
            $having = "HAVING ({$this->having})";
        }

        $rest = "{$withColumns}
                 {$having}
            ORDER BY {$this->getOrder()}
               LIMIT {$limit} offset {$offset}";

        return $rest;
    }

    /**
     *
     * @param type $baseSelect
     * @return type
     */
    public function fillSelectForPaginationCounter($baseSelect) {
        $withColumn = str_replace('[COLUMNS]', '1', $baseSelect);
        $having = "";

        if ($this->having) {
            $having = "HAVING ({$this->having})";
            $withColumn = $this->fillWithColumns($baseSelect);
        }

        $withCount = "select count(1) as total from (
            {$withColumn} {$having}
            ) as temp_count";

        return $withCount;
    }

    /**
     *
     * @param type $pagination
     * @return type
     */
    public function getBaseSelect() {
        if ($this->rawBaseSelect) return $this->rawBaseSelect;

        $select = "
            select [COLUMNS]
              from {$this->getTable()} {$this->getJoins()}";

        return $select;
    }

    /**
     *
     * @param type $filters
     * @return type
     */
    public function resolveFilters($filters) {
        if (!$filters && empty($this->fixedFilters) && empty($this->rawFilters)) return ['where' => '', 'values' => []];

        if (!is_array($filters)) $filters = [$filters];

        $where = [];

        $values = [];

        $mergedFilters = array_merge($filters, $this->fixedFilters);

        foreach ($mergedFilters as $filter) {
            if ($filter) {
                $insertValue = true;
                $blockForCustom = false;

                $val = $filter->val;
                $option = $this->filterOptions[$filter->op] ?? '=';
                $flt = "({$filter->col} {$option} ?)";

                switch ($option) {
                    case 'between':
                        $flt = "({$filter->col} between ? and ?)";
                        $values[] = $this->resolveFilterValue($val, $filter->op);
                        $val = $filter->val2;

                        break;
                    case 'null':
                        $insertValue = false;
                        $flt = "({$filter->col} IS NULL)";

                        break;
                    case 'not null':
                        $insertValue = false;
                        $flt = "({$filter->col} IS NOT NULL)";

                        break;
                    case 'custom': // filtro customizado
                        $result = $this->handleCustomFilter($filter);

                        if ($result) {
                            [$flt, $insertValue] = $result;
                        } else {
                            $blockForCustom = true;
                        }

                        break;
                    case 'in': // filtro IN
                        $condition = $this->resolveFilterInValue($val);
                        $flt = "{$filter->col} IN ($condition)";
                        $insertValue = false;

                        break;
                    default:
                        break;
                }

                if (!$blockForCustom) {
                    $where[$filter->col] = $flt;

                    if ($insertValue) {
                        $values[] = $this->resolveFilterValue($val, $filter->op);
                    }
                }
            }
        }

        $finalFilters = implode(' and ', array_merge($where, $this->rawFilters));
        //$finalRawFilters = implode(' and ', $this->rawFilters);

        return [
            'where'  => ' where ' . $finalFilters,
            'values' => $values
        ];
    }

    /**
     * Deve retornar um array.
     * Índice 0: deve retornar o filtro bruto.
     * Índice 1: caso haja um valor a ser tratado, deve retorna true.
     *
     * @param type $filter
     * @return array
     */
    protected function handleCustomFilter($filter) {
        $this->throwException('O modelo não apresenta tratamento para filtros customizados');
    }

    /**
     *
     * @param type $value
     * @param type $option
     * @return type
     */
    protected function resolveFilterValue($value, $option) {
        if ($this->filterOptions[$option] === 'like') {
            return "%{$value}%";
        }

        return $value;
    }

    protected function resolveFilterInValue($value) {
        return implode(', ', $value);
    }


    /**
     *
     * @return type
     */
    public function fillWithColumns($select) {
        $columnsForSelect = '';

        foreach ($this->selection as $_ => $columns) {
            if (!empty($columns)) {
                $columnsForSelect .= implode(', ', $columns) . ', ';
            }
        }

        $with = str_replace('[COLUMNS]', rtrim($columnsForSelect, ', '), $select);

        return $with;
    }

    /**
     *
     * @return type
     */
    public function getSelection() {
        return $this->selection[$this->getTable()];
    }

    /**
     *
     */
    abstract protected function loadTable();

    /**
     *
     * @return type
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     *
     * @return type
     */
    public function getJoins() {
        $joins = '';

        foreach ($this->joins as $table => $join) {
            $left = $join['left'] ? " left" : " ";

            $joins .= "
                {$left} join {$table}
                on {$join['on']} = {$join['equal']} ";
        }

        return $joins;
    }

    /**
     *
     * @param type $table
     * @param type $relationColumn
     * @param type $column
     * @return $this
     */
    public function addJoin($table, $column = 'id', $left = false) {
        $last = $this->getLastColumn();

        $this->joins[$table] = [
            'on'    => "{$table}.{$column}",
            'equal' => "{$last['table']}.{$last['name']}",
            'left'  => $left
        ];

        return $this;
    }

    /**
     *
     * @param type $table
     * @param type $relationColumn
     * @param type $column
     * @return type
     */
    public function addLeftJoin($table, $relationColumn = 'id') {
        return $this->addJoin($table, $relationColumn, true);
    }

    /**
     *
     * @param type $table
     * @param type $column
     * @return $this
     */
    public function addSelection($table, $column) {
        $this->selection[$table][] = $table.'.'.$column;

        return $this;
    }

    /**
     *
     * @param type $table
     * @param type $column
     * @return $this
     */
    public function setHidden($_ = false) {
        $last = $this->getLastColumn();

        $table = $last['table'];

        $column = "{$last['table']}.{$last['name']}";

        foreach ($this->selection[$table] as $index => $selectionColumn) {
            if ($selectionColumn === $column) {
                unset($this->selection[$table][$index]);
            }
        }

        return $this;
    }

    /**
     *
     * @param type $name
     * @param type $isPk
     * @return $this
     */
    public function addColumn($name, $isPk = false) {
        $this->setLastColumn($this->getTable(), $name);

        if ($isPk) {
            $this->primaryKey = is_array($this->primaryKey)
                ? array_merge($this->primaryKey, [$name])
                : [$name];
        }

        $this->addSelection($this->getTable(), $name);

        $this->fillable[] = $name;

        $this->columns[] = [
            'name' => $name,
            'isPk' => $isPk,
            'from' => $this->getTable(),
            'as'   => false
        ];

        return $this;
    }

    /**
     *
     * @return type
     */
    public function getColumns() {
        return $this->columns;
    }

    /**
     *
     * @return type
     */
    public function getTableColumnsWithoutKeys() {
        $tableColumns = [];

        foreach ($this->columns as $column => $data) {
            if ($data['from'] === $this->getTable() && !$data['isPk']) {
                $tableColumns[] = $data['name'];
            }
        }

        return $tableColumns;
    }

    /**
     *
     * @return type
     */
    public function getTableColumns() {
        $tableColumns = [];

        foreach ($this->columns as $column => $data) {
            if ($data['from'] === $this->getTable()) {
                $tableColumns[] = $data['name'];
            }
        }

        return $tableColumns;
    }

    /**
     *
     * @param type $reqAttrs
     * @return type
     */
    public function getFilledAttributes($reqAttrs = null) {
        $toFill = [];

        $columns = !$this->incrementing ? $this->getTableColumns() : $this->getTableColumnsWithoutKeys();

        foreach ($columns as $column) {
            if ($reqAttrs) {
                $reqValue = isset($reqAttrs[$column]) ? $reqAttrs[$column] : null;

                $toFill[$column] = $reqValue;
            } else {
                $reqValue = request()->get($column);

                if (!is_null($reqValue)) {
                    $toFill[$column] = $reqValue;
                }
            }
        }

        return $toFill;
    }

    /**
     *
     * @param type $table
     * @param type $name
     * @return $this
     */
    public function addColumnFrom($table, $name, $alias = false) {
        $this->setLastColumn($table, $name);

        $columName = $name;

        if ($alias) {
            $columName .= " as {$alias}";
        }

        $this->addSelection($table, $columName);

        $this->columns[] = [
            'name' => $name,
            'isPk' => false,
            'from' => $table,
            'as'   => $alias
        ];

        return $this;
    }

    /**
     *
     * @return type
     */
    protected function getLastColumn() {
        return $this->lastColumn;
    }

    /**
     *
     * @param type $table
     * @param type $name
     */
    protected function setLastColumn($table, $name) {
        $this->lastColumn = [
            'table' => $table,
            'name'  => $name
        ];
    }

    public function setRawHaving($rawHaving) {
        $this->having = $rawHaving;
    }

    public function setRawOrder($order) {
        $this->order = $order;
    }

    /**
     *
     * @param type $order
     * @param type $from
     */
    public function setOrder($order, $from = 'asc', $useTable = true) {
        $order = strtolower($order);
        // se informou a ordem diretamente
        if (strpos($order, 'asc') || strpos($order, 'desc')) {
            $from = '';
        }

        // se não informou a tabela, preenche com a própria tabela do model
        if (!strpos($order, '.')) {
            $columns = explode(',', $order);

            foreach ($columns as $index => $column) {
                $column = trim($column);

                if($useTable) {
                    $columns[$index] = "{$this->getTable()}.{$column} {$from}";
                } else {
                    $columns[$index] = "{$column} {$from}";
                }
            }

            $order = implode(',', $columns);

            $from = '';
        }

        $this->order = $order . ' ' . $from;
    }

    /**
     *
     * @param type $id
     * @return type
     */
    public function getOne($keys) {
        $filters = [];

        $baseSelect = $this->fillWithColumns(
            $this->getBaseSelect()
        );

        $primaryKey = is_array($this->primaryKey) ? $this->primaryKey : [$this->primaryKey];

        foreach ($primaryKey as $pkey) {
            $fl = new \stdClass();
            $fl->col = "{$this->getTable()}.{$pkey}";
            $fl->op = 'I';
            $fl->val = $keys->$pkey;

            $filters[] = $fl;
        }

        $filters = $this->resolveFilters($filters);

        $data = DB::select(
            $baseSelect . $filters['where'] . " limit 1",
            $filters['values']
        );

        $final = $data[0] ?? null;

        if ($final) {
            $final = $this->extraDataFromOne($final);
        }

        return $final;
    }

    /**
     * Sempre deve retornar o próprio conteúdo.
     *
     * @param stdClass $data
     * @return stdClass
     */
    public function extraDataFromOne($data) {
        return $data;
    }

    /**
     *
     * @param type $keys
     * @return type
     */
    public function getOneFromParam($keys) {
        if ($keys) {
            $keys = json_decode(base64_decode($keys));
        }

        if (!$keys || empty($keys)) {
            $this->throwException('Chave inválida');
        }

        return $this->getOne($keys);
    }

    /**
     *
     * @param type $keys
     * @return \stdClass
     */
    protected function generateFiltersFromKeys($keys) {
        $filters = [];

        $primaryKey = is_array($this->primaryKey) ? $this->primaryKey : [$this->primaryKey];

        try {
            foreach ($primaryKey as $pkey) {
                $fl = new \stdClass();
                $fl->col = "{$this->getTable()}.{$pkey}";
                $fl->op = 'I';
                $fl->val = $keys->$pkey;

                $filters[] = $fl;
            }
        } catch (Exception $e) {
            $this->throwException('Erro ao extrair a chave');
        }

        return $filters;
    }

    /**
     *
     * @param type $keys
     * @return type
     */
    public function destroyFromParam($keys) {
        if ($keys) {
            $keys = json_decode(base64_decode($keys));
        }

        if (!$keys || empty($keys)) {
            $this->throwException('Chave inválida');
        }

        $filters = $this->resolveFilters(
            $this->generateFiltersFromKeys($keys)
        );

        $result = DB::delete(
            $this->fillDelete($filters),
            $filters['values']
        );

        return $result;
    }

    /**
     *
     * @param type $filters
     * @return type
     */
    protected function fillDelete($filters) {
        return $this->getBaseDelete() . $filters['where'];
    }

    /**
     *
     * @return type
     */
    protected function getBaseDelete() {
        return "delete from {$this->getTable()} ";
    }

    public function getDataByColumn($columnFilter, $valueFilter, $tabela) {
        $filter = "";
        if($columnFilter) {
            $filter .= "AND {$columnFilter} = '{$valueFilter}'";
        }

        return DB::select("
            SELECT *
              FROM {$tabela}
             WHERE TRUE
            {$filter}
        ");
    }

    /**
     *
     * @param type $msg
     * @throws NajException
     */
    public function throwException($msg) {
        throw new NajException($msg);
    }

}
