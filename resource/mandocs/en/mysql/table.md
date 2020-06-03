# mysql table

some examples for operate mysql table

## create table

examples:

```sql
CREATE TABLE `my_table` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `orderno` varchar(120) NOT NULL DEFAULT '' COMMENT 'order no',
  `create_at` int(10) unsigned NOT NULL COMMENT 'create time',
  PRIMARY KEY (`id`),
  KEY `idx_orderno` (`orderno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='table comments';
```

## drop table

```sql
drop table `my_table`;
```

## add column

examples for add column:

```sql
ALTER TABLE `my_table`
    ADD `new_field` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT 'new field' AFTER `orderno`;
```

## change column

examples for change column:

```sql
ALTER TABLE `my_table`
   CHANGE `new_field` `new_field` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT 'new field comments';
```

