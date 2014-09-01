<?php
/**
 * app model封装文件
 * 创建model文件 可用命令 php   APP_ROOT_PATH/cli/index.php newModel test
 *
 * @file app_model.php
 * @brief app model封装
 * @package model
 * @author 陈金(wind.golden@gmail.com)
 */
/**
 * app_model 封装
 */
abstract Class App_Model extends Model {
    /**
     * 正则表达式验证信息
     * @var array
     */
    var $validate_preg = array();
    /**
     * 数据库验证
     * @var aray
     */
    var $validate_db = array();
    /**
     * 验证错误信息
     * @var array
     */
    var $validate_messages = array();
    /**
     * 验证规则
     * @var array
     */
    var $validate_rules;
    /**
     * 已经验证过的字段
     * @var  array
     */
    private $_validated_fields = array();
    /**
     * 支持的验证规则
     * @var array
     */
    var $_validate_rules_support = array(
        'callback_',
        'compare_',
        'nonempty',
        'preg',
        'db_in',
        'db_notin',
        'badword',
        'rangeIn_'
    ); //支持的验证方式
    
    /**
     * 默认验证错误提示
     * @var array
     */
    var $_validate_default_errors = array(
        'nonempty' => '%field% does not exists',
        'preg' => '%field% format error',
        'db_in' => '%field% already exists',
        'db_notin' => '%field% does not exists',
        'callback_' => '',
        'compare_' => '',
        'badword' => 'Include Bad words'
    );
    /**
     *  添加验证规则
     * @param string|array $fields 被验证的字段 必须与被验证数组的键对应
     * @param string|array $rules 验证规则 见属性$validate_rules_support
     * @param string|array $error 错误信息
     */
    function addValidateRules($fields, $rules, $error = '') {
        if (is_string($rules)) $rules = array(
            $rules
        );
        if (is_string($fields)) $fields = array(
            $fields
        );
        foreach ($fields as $field) {
            foreach ($rules as $key => $rule) {
                if (in_array($rule, $this->_validate_rules_support) || substr($rule, 0, 9) === 'callback_' || substr($rule, 0, 8) === 'compare_' || substr($rule, 0, 8) === 'rangeIn_') {
                    if (is_string($error)) {
                        if ($error === '') {
                            $error = (substr($rule, 0, 9) === 'callback_' || substr($rule, 0, 8) === 'compare_' || substr($rule, 0, 8) === 'rangeIn_') ? '' : $this->_validate_default_errors[$rule];
                        }
                        $this->validate_rules[] = array(
                            'field' => $field,
                            'error' => $error,
                            'rule' => $rule
                        );
                    }
                    elseif (is_array($error) && count($error) == count($rules)) {
                        $this->validate_rules[] = array(
                            'field' => $field,
                            'error' => $error[$key],
                            'rule' => $rule
                        );
                    }
                    else {
                        throw new Exception('Validation error in ' . $this->table . ': error is not matched with rules  ' . $validate_rule['field']);
                    }
                }
            }
        }
        return;
    }
    /**
     * 重置当前model的所有验证
     */
    function resetValidatation() {
        unset($this->validate_rules);
    }
    /**
     *  按照验证规则执行验证
     * @param array $data 被验证的数据 目前支持一维数组
     */
    function runValidatation($data = NULL) {
        if (count($this->validate_rules) === 0 || !is_array($data)) return;
        foreach ($this->validate_rules as $validate_rule) {
            if (!isset($data[$validate_rule['field']])) {
                $this->validate_messages[$validate_rule['field']] = $validate_rule['error'];
                return;
            }
            $field_data = $data[$validate_rule['field']];
            if (is_string($field_data)) $error = str_replace(array(
                '%field%',
                '%value%'
            ) , array(
                $validate_rule['field'],
                $field_data
            ) , $validate_rule['error']);
            else $error = $validate_rule['error'];
            switch ($validate_rule['rule']) {
                case 'nonempty': //非空
                    if (!isset($field_data) || (is_string($field_data) && trim($field_data) === '') || (is_array($field_data) && (in_array('', $field_data) || count($field_data) == 0))) {
                        $this->validate_messages[$validate_rule['field']] = $error;
                        return;
                    }
                    break;

                case 'preg': //正则
                    if (!isset($this->validate_preg[$validate_rule['field']])) {
                        throw new Exception('Validation error in ' . $this->table . ':no preg info for field ' . $validate_rule['field']);
                    }
                    if (is_string($field_data)) {
                        if (preg_match($this->validate_preg[$validate_rule['field']], $field_data) === 0) {
                            $this->validate_messages[$validate_rule['field']] = $error;
                            return;
                        }
                    }
                    elseif (is_array($field_data)) {
                        foreach ($field_data as $item_data) {
                            if (preg_match($this->validate_preg[$validate_rule['field']], $item_data) === 0) {
                                $this->validate_messages[$validate_rule['field']] = $error;
                                return;
                            }
                        }
                    }
                    break;

                case 'db_in': //数据库中必须存在
                    
                case 'db_notin': //数据库中必须不存在
                    $conditions = $this->validate_db[$validate_rule['field']];
                    if (!isset($conditions)) {
                        throw new Exception('Validation error in ' . $this->table . ':no db info for field ' . $validate_rule['field']);
                    }
                    if (is_string($conditions)) {
                        preg_match_all('/\[([a-zA-z0-9]+)\]/i', $conditions, $matched_data);
                        if (count($matched_data[1]) > 0) {
                            foreach ($matched_data[1] as $k => $field) {
                                if (!isset($data[$field])) {
                                    throw new Exception('Validation error in ' . $this->table . ':no data info for field ' . $data_field);
                                }
                                $condition_value = str_replace(array(
                                    $matched_data[0][$k],
                                    '[',
                                    ']'
                                ) , array(
                                    "'" . $data[$field] . "'",
                                    '',
                                    ''
                                ) , $conditions);
                            }
                        }
                        $conditions = $condition_value;
                    }
                    else if (is_array($conditions)) {
                        //给查询条件中的变量赋值
                        foreach ($conditions as $condition_field => $condition_value) {
                            if (is_string($condition_value) && strpos($condition_value, '[') !== false) {
                                $data_field = str_replace(array(
                                    '[',
                                    ']'
                                ) , '', $condition_value);
                                if (!isset($data[$data_field])) {
                                    throw new Exception('Validation error in ' . $this->table . ':no data info for field ' . $data_field);
                                }
                                $conditions[$condition_field] = $data[$data_field];
                            }
                            elseif (is_array($condition_value)) {
                                foreach ($condition_value as $k => $v) {
                                    if (is_string($v) && strpos($v, '[') !== false) {
                                        $data_field = str_replace(array(
                                            '[',
                                            ']'
                                        ) , '', $v);
                                        if (!isset($data[$data_field])) {
                                            throw new Exception('Validation error in ' . $this->table . ':no data info for field ' . $data_field);
                                        }
                                        $conditions[$condition_field][$k] = $data[$data_field];
                                    }
                                }
                            }
                        }
                    }
                    if (($validate_rule['rule'] === 'db_in' && !($this->hasAny($conditions))) || ($validate_rule['rule'] === 'db_notin' && ($this->hasAny($conditions)))) {
                        $this->validate_messages[$validate_rule['field']] = $error;
                        return;
                    }
                    break;

                default: //回调函数方式
                    if (!(substr($validate_rule['rule'], 0, 9) === 'callback_') && !(substr($validate_rule['rule'], 0, 8) === 'compare_') && !(substr($validate_rule['rule'], 0, 8) === 'rangeIn_')) {
                        throw new Exception('Validation error in ' . $this->table . ':unrecognised rule for field ' . $validate_rule['field']);
                    }
                    if (substr($validate_rule['rule'], 0, 9) === 'callback_') {
                        $callback_function = substr($validate_rule['rule'], 9);
                        if (!method_exists($this, $callback_function) || !is_callable(array(
                            $this,
                            $callback_function
                        ))) {
                            throw new Exception('Validation error in ' . $this->table . ':unrecognised rule for field ' . $validate_rule['field']);
                        }
                    }
                    elseif (substr($validate_rule['rule'], 0, 8) === 'compare_' || substr($validate_rule['rule'], 0, 8) === 'rangeIn_') {
                        $callback_function = $validate_rule['rule'];
                    }
                    call_user_func_array(array(
                        $this,
                        $callback_function
                    ) , array(
                        'data' => $data,
                        'field' => $validate_rule['field'],
                        'error' => $error
                    ));
                    break;
                }
        }
    }
    /**
     * 用魔术方法来 比较两个数据是否相等 和 数据是否落在某个范围内
     * @param string $method 方法
     * @param array $args 参数 array(0=>data,1=>field,2=>error)
     * @return called function
     */
    function __call($method, $args) {
        if (substr($method, 0, 8) == 'compare_') {
            $compared_field = substr($method, 8);
            $data_array = $args[0];
            $field = $args[1];
            $error = $args[2];
            if (!isset($data_array[$compared_field])) {
                $this->validate_messages[$field] = $compared_field . ' does not exists';
                return;
            }
            if ($data_array[$field] !== $data_array[$compared_field]) {
                $this->validate_messages[$field] = ($error === '' ? $field . ' and ' . $compared_field . ' are different' : $error);
                return;
            }
        }
        elseif (substr($method, 0, 8) == 'rangeIn_') {
            $ranges = substr($method, 8);
            $data_array = $args[0];
            $field = $args[1];
            $error = $args[2];
            list($min, $max) = explode('-', $ranges);
            if (is_string($data_array[$field])) {
                if ($data_array[$field] > $max || $data_array[$field] < $min) {
                    $this->validate_messages[$field] = ($error === '' ? $field . ' is outranged of ' . $min . ' and ' . $max : $error);
                    return;
                }
            }
            elseif (is_array($data_array[$field])) {
                foreach ($data_array[$field] as $item_data) {
                    if ($item_data > $max || $item_data < $min) {
                        $this->validate_messages[$field] = ($error === '' ? 'one of ' . $field . ' data' . $item_data . ' is outranged of ' . $min . ' and ' . $max : $error);
                        return;
                    }
                }
            }
        }
        else {
            return parent::__call($method, $args);
        }
    }
    /**
     * 获得验证消息
     */
    function getValidateMsg() {
        return $this->validate_messages;
    }
}
