<?php declare(strict_types=1);

namespace {= ctx.namespace | default:YourNamespace};

import javax.validation.constraints.Min;
import javax.validation.constraints.NotBlank;
import javax.validation.constraints.NotNull;

/**
 * @author {= ctx.user | default:inhere}
 */
class {= ctx.className | default:YourClass} {

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
