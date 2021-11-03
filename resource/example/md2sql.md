# MD <=> SQL

## create sql

```sql
CREATE TABLE `goods_v2_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `sid` int(11) unsigned NOT NULL COMMENT '店铺id',
  `glid` int(11) unsigned NOT NULL COMMENT '商品列表id',
  `name` json NOT NULL COMMENT '二级商品名称',
  `photo` json NOT NULL COMMENT '图片',
  `show_params` json DEFAULT NULL COMMENT '自定义图片参数',
  `broadcast` int(11) unsigned NOT NULL COMMENT '轮播间隔',
  `show_en` tinyint(4) NOT NULL COMMENT '是否显示英文名。1，显示；2，不显示',
  `show_en_name` varchar(128) NOT NULL COMMENT '英文名称',
  `limitation` tinyint(4) NOT NULL COMMENT '限购提醒。1，限购，2，不限购',
  `detail_show` tinyint(4) NOT NULL COMMENT '是否显示商品详情，1，显示。2，不显示',
  `detail_name` json NOT NULL COMMENT '详情显示名',
  `detail_content` json NOT NULL COMMENT '详情内容',
  `atime` int(11) unsigned NOT NULL,
  `title` json NOT NULL COMMENT '商品规格名称',
  `deleted` tinyint(4) NOT NULL DEFAULT '2' COMMENT '是否删除，1，已删除；2，未删除 3，已废弃（脏数据标记-不可修改此状态进行还原）',
  `deleted_at` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `idx_sid` (`sid`) USING BTREE,
  KEY `idx_glid` (`glid`) USING BTREE,
  KEY `idx_deleted` (`deleted`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9931 DEFAULT CHARSET=utf8mb4 COMMENT='菜品二级信息表';
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
