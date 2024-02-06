<?php declare(strict_types=1);

namespace Inhere\KiteTest\Lib\Parser;

use Inhere\Kite\Lib\Defines\ProgramLang;
use Inhere\Kite\Lib\Parser\DTOParser;
use Inhere\KiteTest\BaseKiteTestCase;
use Toolkit\Stdlib\Json;

class DTOParserTest extends BaseKiteTestCase
{
    private const JAVA_CLASS_C1 = <<<CODE
package com.some.pkg.service.abc.dto;

import com.alibaba.fastjson.annotation.JSONField;
import lombok.Data;

import java.util.List;

/**
 * comments in class
 *
 * @author inhere <in.798@qq.com>
 */
@Data
public class OrderRefundPayExtDTO {

    /**
     * comments for field objVal
     */
    @JSONField(name = "mcd")
    private OtherClassDTO objVal;

    /**
     * 礼品卡退款明细
     */
    @JSONField(name = "gcd")
    private List<InnerClassItem> innerClasList;

    /**
     * comments for field pointsNum
     */
    @JSONField(name = "pn")
    public Integer pointsNum;

    // ------------------- a comments in class -------------------

    /**
     * 员工名称
     */
    @JSONField(name = "wn")
    private String workerName;

    /**
     * comments in sub class
     */
    @Data
    public static class InnerClassItem {
        /**
         * comments for field intVal
         */
        @JSONField(name = "iv")
        private Integer intVal;

        /**
         * comments for field strVal
         */
        @JSONField(name = "sv")
        private String strVal;

        /**
         * 设置默认值
         */
        public RefundGiftCardItem() {
            intVal = 0;
        }
    }
}

CODE;

    public function testJavaClass(): void
    {
        $m = DTOParser::parse(ProgramLang::JAVA, self::JAVA_CLASS_C1);

        $this->assertNotEmpty($m->toArray());

        $str = Json::pretty($m->toArray());
        println($str);
    }
}