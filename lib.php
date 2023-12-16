<?php

class DBConnection
{
    var $cid;
    var $res;
    var $last_query;
    var $left_join;

    public $tables;

    public function __construct($host, $userName, $userPassword, $dbName)
    {
        $this->connect($host, $userName, $userPassword, $dbName);
    }

    function connect($host, $user, $pass, $db_name): bool
    {
        if (!($this->cid = mysqli_connect($host, $user, $pass)))
            return $this->error(__FUNCTION__, mysqli_error($this->cid));
        if (!mysqli_select_db($this->cid, $db_name))
            return $this->error(__FUNCTION__, mysqli_error($this->cid));
        if (!mysqli_query($this->cid, 'SET NAMES utf8'))
            return $this->error(__FUNCTION__, mysqli_error($this->cid));
        return true;
    }

    function error($func_name, $error, $exit = true)
    {
        $log_file = "Date: " . date('d-m-Y H:i:s') . PHP_EOL .
            "Function: " . $func_name . PHP_EOL .
            $error . PHP_EOL .
            'http: ' . http_build_query($_GET) . PHP_EOL;
        if ($fp = fopen('log/DB-error.log', 'a+')) {
            fwrite($fp, $log_file);
            fclose($fp);
        }

        if ($exit)
            exit;
        return false;
    } // connect

    function setTableAlias($alias, $realName)
    {
        $this->tables[$alias] = $realName;
    } // error

    function select($what, $from, $where = '', $group_by = '', $order_by = '', $limit = ''): bool
    {
        $q = 'SELECT ' . $what . ' FROM ' . $from . $this->left_join;
        $this->left_join = '';
        if ($where != '')
            $q .= ' WHERE ' . $where;
        if ($group_by != '')
            $q .= ' GROUP BY ' . $group_by;
        if ($order_by != '')
            $q .= ' ORDER BY ' . $order_by;
        if ($limit != '')
            $q .= ' LIMIT ' . $limit;
        $this->query(__FUNCTION__, $q);
        return true;
    } // select

    function query($from, $q, $skip_error = true)
    {
        $this->last_query = $q;
        $this->res = mysqli_query($this->cid, $q);
        if ((!$this->res || !(is_resource($this->res) || is_bool($this->res))) && !$skip_error)
            $this->error(__FUNCTION__ . ' - ' . $from, mysqli_error($this->cid) . "\nQuery: " . $q);
    } //

    function LeftJoin($table, $condition)
    {
        $this->left_join .= ' LEFT JOIN ' . $table . ' ON ' . $condition . ' ';
    }

    function next_row($type = 'key', $html_output = false)
    {
        if (is_bool($this->res))
            return false;
        switch ($type) {
            case "num":
                $out = mysqli_fetch_row($this->res);
                break;
            case "key":
                $out = mysqli_fetch_assoc($this->res);
                break;
            case "all":
                $out = mysqli_fetch_array($this->res);
                break;
            default :
                return false;
        }
        if (is_array($out) && count($out) > 0) {
            if ($html_output) {
                foreach ($out as $key => $value) {
                    $out[$key] = htmlspecialchars($value);
                }
            }
            return $out;
        } else return false;
    }
}

class Categories
{
    var $list;

    public function getAll(): array
    {
        global $DB;
        $DB->LeftJoin($DB->tables['goods'] . ' AS g', 'g.category=c.id');
        $DB->select('c.*, COUNT(g.id) as goodsCount', $DB->tables['categories'] . ' AS c', '', 'c.id');
        $this->list = array();
        $tmp['id'] = 0;
        $tmp['name'] = 'Всі категорії';
        $this->list[] = $tmp;
        $tmp = 0;
        while ($row = $DB->next_row()) {
            $this->list[] = $row;
            $tmp += $row['goodsCount'];
        }
        $this->list[0]['goodsCount'] = $tmp;
        return $this->list;
    }
}

class Goods
{
    var $list;

    public function getAll(): array
    {
        return $this->getGoodsByCat(0);
    }

    public function getGoodsByCat($id, $sorting = ''): array
    {
        global $DB;
        switch ($sorting) {
            case 'priceASC':
                $sorting = 'price ASC';
                break;
            case 'AbsAsc':
                $sorting = 'name ASC';
                break;
            case  'dateDESC':
                $sorting = '`date` DESC';
                break;
            default:
                $sorting = '';
        }


        $DB->select('*', $DB->tables['goods'], $id > 0 ? 'category=' . $id : '', '', $sorting);
        $this->list = array();
        while ($row = $DB->next_row()) {
            $this->list[] = $row;
        }
        return $this->list;
    }

    public function getItemInfo($id): array
    {
        global $DB;
        $DB->select('*', $DB->tables['goods'], 'id=' . $id);
        $this->list = $DB->next_row();
        return $this->list;
    }
}