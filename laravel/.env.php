<?php 
 
// 定义配置文件路径 

$phpenv = strtolower(get_cfg_var("PHPENV"));
    if (!empty($phpenv)) {
        $env = $phpenv;
    } else {
        $env = 'prod';
    }

define('_ENV_FILE_PATH_', $env);