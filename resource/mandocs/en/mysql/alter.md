# alter

examples for use `alter` on mysql

## add column

add new column example:

```sql
alter table my_table
    ADD COLUMN new_field int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'my new field';
```

## change column

change column example:

```sql
alter table my_table
    CHANGE COLUMN old_field old_field int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'my new field';
```

## drop column

drop column example:

```sql
alter table my_table DROP old_field;
```

## add index

key index:

```sql
ALTER TABLE `my_table` ADD INDEX `idx_orderno`(`orderno`);
```

