<?php declare(strict_types=1);

namespace Inhere\KiteTest\Lib\Parser;

use Inhere\Kite\Lib\Parser\DBMdTable;
use Inhere\Kite\Lib\Parser\DBTable;
use Inhere\Kite\Lib\Parser\MySQL\TypeMap;
use Inhere\KiteTest\BaseKiteTestCase;

/**
 * class DBTableTest
 */
class DBTableTest extends BaseKiteTestCase
{
    private $mdTable1 = <<<MD
### 用户的订单记录表 `user_order`

字段名 | 类型 | 是否为空 | 默认值 | 注释
-------|------|---------|--------|-----
`id` | `INT(11) UNSIGNED` | `No` |  | `id`
`uid` | `INT(11) UNSIGNED` | `No` | `0` | 下单用户ID
`name` | `VARCHAR(64)` | `No` | `` | 用户名称
`orderno` | `VARCHAR(48)` | `No` |  | 主订单编号
`ctime` | `INT(10) UNSIGNED` | `No` | `0` | 订单创建时间

> INDEXES: PRIMARY KEY (`id`), UNIQUE KEY `uni_uid_orderno` (`uid`, `orderno`)
MD;

    private $createSql1 = <<<SQL
CREATE TABLE `user_order` (
  `id` INT(11) UNSIGNED NOT NULL DEFAULT 'AUTO_INCREMENT' COMMENT 'id',
  `uid` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '下单用户ID',
  `name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '用户名称',
  `orderno` VARCHAR(48) NOT NULL DEFAULT '' COMMENT '主订单编号',
  `ctime` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uni_uid_orderno` (`uid`, `orderno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户的订单记录表';
SQL;

    public function testFromMdTable(): void
    {
        $dbt = DBTable::fromMdTable($this->mdTable1);

        // vdump($dbt->getFields());
        $this->assertNotEmpty($dbt->getSource());
        $this->assertNotEmpty($dbt->getFields());
        $this->assertNotEmpty($dbt->getIndexes());
        $this->assertEquals('user_order', $dbt->getTableName());
        $this->assertEquals('用户的订单记录表', $dbt->getTableComment());

        $field = $dbt->getField('orderno');
        $this->assertNotEmpty($field);
        $this->assertEquals(TypeMap::VARCHAR, $field['type']);
        $this->assertEquals(48, $field['typeLen']);

        $this->assertNotEmpty($genSql = $dbt->toString());
        $this->assertEquals($this->createSql1, $genSql);
        // vdump($dbt->toString());

        $this->assertNotEmpty($mdTable = $dbt->toMDTable());
        // $this->assertEquals($this->mdTable1, $mdTable);
        // vdump($mdTable);
    }

    public function testDbMdTable_parseLine(): void
    {
        $dmt = new DBMdTable();
        $ret = $dmt->parseLine('`name` | `VARCHAR(64)` | `No` | `` | 用户名称');

        $this->assertNotEmpty($ret);
        $this->assertFalse($ret['nullable']);
    }
}
