package {= ctx.pkgName | default:org.example.entity};

// import com.alibaba.fastjson.annotation.JSONField;
import lombok.Data;

import javax.validation.constraints.Min;
import javax.validation.constraints.NotBlank;
import javax.validation.constraints.NotNull;

/**
 * @author {= ctx.user | default:inhere}
 */
@Data
public class PayQueryReqDTO {

{for field in fields}
    /**
     * 订单号
     */
    @NotBlank
    private String {= field.name};
{/for}
}
