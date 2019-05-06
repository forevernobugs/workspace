<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * 自定义查询构造
 * @author Jason
 */
class DBHelper {

    private $db = null;
    private $table = null;
    private $condition = null;
    private $join = [];

    /**
     * 构造方法
     * @param string $table
     */
    public function __construct($table) {
        $this->table = $table;
        $this->db = DB::table($table);
    }

    /**
     * 实例化返回DBHelper
     * @param string $table
     * @return \App\Models\DBHelper
     */
    public static function table($table) {
        return new DBHelper($table);
    }

    /**
     * 构造where
     * $condition格式：
     *  [
     *      'column_name' => string/numeric,                            # column_name=value
     *      'column_name' => ['=/<>/>/>=/</<=', string/numeric],        # column_name<=value
     *      'column_name' => ['LIKE', string],                          # column_name LIKE string
     *      'column_name' => ['BETWEEN/NOT BETWEEN', [left, right]],    # column_name BETWEEN left AND right
     *      'column_name' => ['IN/NOT IN', [...]],                      # column_name IN (...)
     *      'column_name' => ['NULL/NOT NULL', any],                    # column_name IS NULL
     *      'OR#1' => $condition,                                       # $logical (column_name=value OR column_name LIKE string...)
     *      'AND#1' => $condition,                                      # $logical (column_name=value AND column_name LIKE string...)
     *      'INNER_JOIN#1' => ['table AS t' => ['t.id' , 'tt.colume']] # INNER JOIN table AS t ON t.id=tt.colume
     *      'LEFT_JOIN#1' => ['table AS t' => ['t.id' , 'tt.colume']]  # LEFT JOIN table AS t ON t.id=tt.colume
     *      'RIGHT_JOIN#1' => ['table AS t' => ['t.id' , 'tt.colume']] # RIGHT JOIN table AS t ON t.id=tt.colume
     *  ]
     * @param type $condition
     * @param type $logical
     * @return type
     */
    public function build_where($condition = [], $logical = 'AND') {
        $this->condition = $condition;
        $this->_build_where($this->db, $this->condition, $logical);
        $this->_build_join($this->db, $this->join);
        return $this->db;
    }

    /**
     * 构造where
     * @param type $query
     * @param type $condition
     * @param type $logical
     * @return type
     */
    private function _build_where($query, $condition, $logical) {
        foreach ($condition as $column_name => $value) {
            // 列名非法
            if (!is_string($column_name)) {
                continue;
            }
            // 非数组时试图进行=构造
            if (!is_array($value)) {
                $this->_operator($query, $column_name, $value, $logical, '=');
                continue;
            }
            // 数组空或长度<2时忽略
            if (empty($value) || (count($value) <= 1 && isset($value[0]))) {
                continue;
            }
            // 数组下标0不存在时试图进行_or/_and/join构造
            if (!isset($value[0])) {
                $this->_other($query, $column_name, $value, $logical);
                continue;
            }
            // 操作符操作
            $opt = $value[0];
            $value = $value[1];
            switch (strtoupper($opt)) {
                case '=':
                case '<>':
                case '>':
                case '>=':
                case '<':
                case '<=':
                    $this->_operator($query, $column_name, $value, $logical, $opt);
                    break;
                case 'LIKE':
                    $this->_like($query, $column_name, $value, $logical);
                    break;
                case 'BETWEEN':
                    $this->_between($query, $column_name, $value, $logical);
                    break;
                case 'NOT BETWEEN':
                    $this->_not_between($query, $column_name, $value, $logical);
                    break;
                case 'NOT IN':
                    $this->_not_in($query, $column_name, $value, $logical);
                    break;
                case 'IN':
                    $this->_in($query, $column_name, $value, $logical);
                    break;
                case 'NULL':
                    $this->_is_null($query, $column_name, $value, $logical);
                    break;
                case 'NOT NULL':
                    $this->_is_not_null($query, $column_name, $value, $logical);
                    break;
                default:
                    break;
            }
        }
        return $query;
    }

    /**
     * 运算符
     * @param type $query
     * @param string $column_name
     * @param string|numeric $value
     * @param string $logical
     */
    private function _operator($query, $column_name, $value, $logical, $operator) {
        if (is_string($value) || is_numeric($value)) {
            if ('AND' == strtoupper($logical)) {
                $query->where($column_name, $operator, $value);
            } else {
                $query->orWhere($column_name, $operator, $value);
            }
        }
    }

    /**
     * like
     * @param type $query
     * @param string $column_name
     * @param string $value
     * @param string $logical
     */
    private function _like($query, $column_name, $value, $logical) {
        if (is_string($value)) {
            if ('AND' == strtoupper($logical)) {
                $query->where($column_name, 'like', $value);
            } else {
                $query->orWhere($column_name, 'like', $value);
            }
        }
    }

    /**
     * between
     * @param type $query
     * @param string $column_name
     * @param array $value
     * @param string $logical
     */
    private function _between($query, $column_name, $value, $logical) {
        if (is_array($value) && isset($value[1])) {
            if ('AND' == strtoupper($logical)) {
                $query->whereBetween($column_name, [$value[0], $value[1]]);
            } else {
                $query->orWhereBetween($column_name, [$value[0], $value[1]]);
            }
        }
    }

    /**
     * not between
     * @param type $query
     * @param string $column_name
     * @param array $value
     * @param string $logical
     */
    private function _not_between($query, $column_name, $value, $logical) {
        if (is_array($value) && isset($value[1])) {
            if ('AND' == strtoupper($logical)) {
                $query->whereNotBetween($column_name, [$value[0], $value[1]]);
            } else {
                $query->orWhereNotBetween($column_name, [$value[0], $value[1]]);
            }
        }
    }

    /**
     * in
     * @param type $query
     * @param string $column_name
     * @param array $value
     * @param string $logical
     */
    private function _in($query, $column_name, $value, $logical) {
        if (is_array($value)) {
            if ('AND' == strtoupper($logical)) {
                $query->whereIn($column_name, $value);
            } else {
                $query->orWhereIn($column_name, $value);
            }
        }
    }

    /**
     * not in
     * @param type $query
     * @param string $column_name
     * @param array $value
     * @param string $logical
     */
    private function _not_in($query, $column_name, $value, $logical) {
        if (is_array($value)) {
            if ('AND' == strtoupper($logical)) {
                $query->whereNotIn($column_name, $value);
            } else {
                $query->orWhereNotIn($column_name, $value);
            }
        }
    }

    /**
     * is_null
     * @param type $query
     * @param string $column_name
     * @param type $value
     * @param string $logical
     */
    private function _is_null($query, $column_name, $value, $logical) {
        if ('AND' == strtoupper($logical)) {
            $query->whereNull($column_name);
        } else {
            $query->orWhereNull($column_name);
        }
    }

    /**
     * is_not_null
     * @param type $query
     * @param string $column_name
     * @param type $value
     * @param string $logical
     */
    private function _is_not_null($query, $column_name, $value, $logical) {
        if ('AND' == strtoupper($logical)) {
            $query->whereNotNull($column_name);
        } else {
            $query->orWhereNotNull($column_name);
        }
    }

    /**
     * 复合查询AND/OR及联接JOIN
     * @param type $query
     * @param string $column_name
     * @param array $value
     * @param string $logical
     */
    private function _other($query, $column_name, $value, $logical) {
        if (is_array($value) && 'AND' == strtoupper(substr($column_name, 0, 3))) {
            $this->condition = $value;
            if ('AND' == strtoupper($logical)) {
                $query->where(function ($qy) {
                    $this->_build_where($qy, $this->condition, 'AND');
                });
            } else {
                $query->orWhere(function ($qy) {
                    $this->_build_where($qy, $this->condition, 'AND');
                });
            }
        } elseif (is_array($value) && 'OR' == strtoupper(substr($column_name, 0, 2))) {
            $this->condition = $value;
            if ('AND' == strtoupper($logical)) {
                $query->where(function ($qy) {
                    $this->_build_where($qy, $this->condition, 'OR');
                });
            } else {
                $query->orWhere(function ($qy) {
                    $this->_build_where($qy, $this->condition, 'OR');
                });
            }
        } elseif (is_array($value) && in_array(strtoupper(substr($column_name, 0, 9)), ['LEFT_JOIN', 'INNER_JOI', 'RIGHT_JOI'])) {
            $columes = reset($value);
            $table = key($value);
            $this->join[] = [strtoupper(substr($column_name, 0, 1)), $table, $columes[0], $columes[1]];
        }
    }

    /**
     * 构造join
     * @param type $query
     * @param type $joins
     */
    private function _build_join($query, $joins) {
        foreach ($joins as $join) {
            switch ($join[0]) {
                case 'I':
                    $query->join($join[1], $join[2], '=', $join[3]);
                    break;
                case 'L':
                    $query->leftJoin($join[1], $join[2], '=', $join[3]);
                    break;
                case 'R':
                    $query->rightJoin($join[1], $join[2], '=', $join[3]);
                    break;
            }
        }
    }

}
