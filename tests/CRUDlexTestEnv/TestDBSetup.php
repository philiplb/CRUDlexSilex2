<?php

/*
 * This file is part of the CRUDlexSilex2 package.
 *
 * (c) Philip Lehmann-Böhm <philip@philiplb.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CRUDlexTestEnv;

class TestDBSetup
{

    public static function getDBConfig()
    {
        return [
            'host'      => '127.0.0.1',
            'dbname'    => 'crudTest',
            'user'      => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'driver' => 'pdo_mysql',
        ];
    }

}
