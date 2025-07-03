<?php

return [

    // MySQL DATA_TYPES in relation to Laravel fluent.
    'mysql'     => [
        // https://laravel.com/docs/9.x/migrations#available-column-types
        'fluentDataTypes' => [

            // Numeric - Integer
            // https://dev.mysql.com/doc/refman/8.0/en/integer-types.html
            'tinyint'         => 'tinyInteger',
            // Note that tinyint(1) is being handled separately.
            'boolean'         => 'boolean',
            'bigint'          => 'bigInteger',
            'mediumint'       => 'mediumInteger',
            'smallint'        => 'smallInteger',
            'int'             => 'integer',

            // Numeric - Fixed-Point
            // https://dev.mysql.com/doc/refman/8.0/en/fixed-point-types.html
            'decimal'         => 'decimal',
            // not supported by Laravel:
            // numeric

            // Numeric - Floating-Point
            // https://dev.mysql.com/doc/refman/8.0/en/floating-point-types.html
            'float'           => 'float',
            'double'          => 'double',

            // Numeric - Bit
            // https://dev.mysql.com/doc/refman/8.0/en/bit-type.html
            // not supported by Laravel:
            // bit

            // Numeric - Datetime
            // https://dev.mysql.com/doc/refman/8.0/en/datetime.html
            'date'            => 'date',
            'timestamp'       => 'timestamp',
            'datetime'        => 'dateTime',

            // Numeric - Time
            // https://dev.mysql.com/doc/refman/8.0/en/time.html
            'time'            => 'time',

            // Numeric - Year
            // https://dev.mysql.com/doc/refman/8.0/en/year.html
            'year'            => 'year',

            // String - Character
            // https://dev.mysql.com/doc/refman/8.0/en/char.html
            'char'            => 'char',
            'varchar'         => 'string',

            // String - Binary
            // https://dev.mysql.com/doc/refman/8.0/en/binary-varbinary.html
            // not supported by Laravel:
            // binary
            // varbinary
            // https://github.com/laravel/framework/issues/1606
            // It seems like you can just set the character set to binary as a workaround:
            // $table->char('url_hash', 16)->charset('binary');
            // This is actually shown as a real binary column type with a length of 16 in MySQL Workbench.

            // String - Blob
            // https://dev.mysql.com/doc/refman/8.0/en/blob.html
            'tinytext'        => 'tinyText',
            'mediumtext'      => 'mediumText',
            'text'            => 'text',
            'longtext'        => 'longText',
            'blob'            => 'binary',
            // not supported by Laravel:
            // tinyblob
            // mediumblob
            // longblob

            // String - Enum
            // https://dev.mysql.com/doc/refman/8.0/en/enum.html
            // Currently not supported by this package:
            // enum

            // String - Set
            // https://dev.mysql.com/doc/refman/8.0/en/set.html
            // Currently not supported by this package:
            // set

            // Spatial
            // https://dev.mysql.com/doc/refman/8.0/en/spatial-type-overview.html
            'geometry'        => 'geometry',
            'point'           => 'point',
            'linestring'      => 'lineString',
            'polygon'         => 'polygon',
            'multipoint'      => 'multiPoint',
            'multilinestring' => 'multiLineString',
            'multipolygon'    => 'multiPolygon',
            // Currently not supported by this package:
            // geomcollection

            // JSON
            // https://dev.mysql.com/doc/refman/8.0/en/json.html
            'json'            => 'json',
        ],

        'fluentIntegerTypes' => [
            'bigint',
            'int',
            'tinyint',
        ],

        // MySQL DATA_TYPE that are not supported by Laravel but can be created with simple raw statements
        'exceptionRawTypes'  => [
            'tinyblob'   => 'TINYBLOB',
            'mediumblob' => 'MEDIUMBLOB',
            'longblob'   => 'LONGBLOB',
            'bit'        => 'BIT',
        ],
    ],
];
