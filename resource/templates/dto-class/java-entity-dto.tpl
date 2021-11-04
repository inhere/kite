package com.kezhilian.wzl.service.order.entity;

import com.baomidou.mybatisplus.annotation.IdType;
import com.baomidou.mybatisplus.annotation.TableField;
import com.baomidou.mybatisplus.annotation.TableId;
import com.baomidou.mybatisplus.annotation.TableName;
import java.io.Serializable;
import lombok.Data;

/**
 * 订单操作日志表
 * @author inhere
 */
@TableName(value ="order_log")
@Data
public class OrderLog implements Serializable {
    /**
     * 主键
     */
    @TableId(value = "id", type = IdType.AUTO)
    private Integer id;

    /**
     * 店铺SID
     */
    @TableField(value = "sid")
    private Integer sid;

    /**
     * 订单用户ID
     */
    @TableField(value = "uid")
    private Integer uid;

    /**
     * 订单ID
     */
    @TableField(value = "order_id")
    private Integer orderId;

    /**
     * 订单编号
     */
    @TableField(value = "orderno")
    private String orderno;

    /**
     * 日志类型，1=用户下单
     */
    @TableField(value = "type")
    private Byte type;

    /**
     * 日志描述
     */
    @TableField(value = "message")
    private String message;

    /**
     * 时间
     */
    @TableField(value = "ctime")
    private Integer ctime;

    /**
     * 日志标识
     */
    @TableField(value = "code")
    private String code;

    /**
     * 日志内容
     */
    @TableField(value = "content")
    private String content;
}
