<?php

namespace Dusan\PhpMvc\Database;


use PDO;

interface PdoConstants
{
    const STRING = PDO::PARAM_STR;
    const NULL = PDO::PARAM_NULL;
    const INTEGER = PDO::PARAM_INT;
    const BOOLEAN = PDO::PARAM_BOOL;
    const LARGE_OBJECT = PDO::PARAM_LOB;
}
