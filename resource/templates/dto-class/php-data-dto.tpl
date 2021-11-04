<?php declare(strict_types=1);

namespace {= };

import javax.validation.constraints.Min;
import javax.validation.constraints.NotBlank;
import javax.validation.constraints.NotNull;

/**
 * @author inhere
 */
@Data
public class PayQueryReqDTO {

    /**
     * 店铺id
     */
    @NotNull
    @Min(1)
    private Long sid;

    /**
     * 订单号
     */
    @NotBlank
    private String orderno;

    /**
     * 发起支付后得到的三方支付单号
     */
    @NotBlank
    @JSONField(name = "payOrderno")
    private String payOrderno;
}
