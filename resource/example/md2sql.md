# MD <=> SQL

## create sql

```sql
CREATE TABLE `goods_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `detail_content` json NOT NULL COMMENT '详情内容',
  `atime` int(11) unsigned NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '2' COMMENT '是否删除，1，已删除；2，未删除,
  `deleted_at` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_deleted` (`deleted`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9931 DEFAULT CHARSET=utf8mb4 COMMENT='菜品信息表';
```

## md table


### 用户被结算的订单记录表 `order_receipted_order`

字段名 | 类型 | 是否为空 | 默认值 | 注释
-------|------|---------|--------|-----
`id` | `INT(11) UNSIGNED` | `No` |  | `id`
`uid` | `INT(11) UNSIGNED` | `No` | `0` | 下单用户ID
`sid` | `INT(11) UNSIGNED` | `No` | `0` | 下单SID
`order_id` | `INT(11) UNSIGNED` | `No` | `0` | 订单ID
`orderno` | `VARCHAR(48)` | `No` |  | 主订单编号
`ctime` | `INT(10) UNSIGNED` | `No` | `0` | 订单创建时间

> INDEXES: PRIMARY KEY (`id`), UNIQUE KEY `uni_uid_orderno` (`uid`, `orderno`)
