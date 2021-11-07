{{
/**
 * comments
 *
 * @var array $arr
 * @var object $obj
 */
}}

echo vars:

{{= $arr['name'] }}
{{ echo $arr['name'] }}

foreach example:

{{ foreach ($arr as $key => $val) : }}
        KEY:{{= $key}} => VALUE:{{
    $typ = gettype($val);
    echo ucfirst($typ === 'array' ? 'arrayValue' : $typ)
}}
    in foreach
{{ endforeach }}

{{
// define var
$a = random_int(1, 10);
}}

if example1:

{{ if ($a < 2): }}
  at if
{{ endif }}

if example2:
// raw if expr - inline
{{ if ($a < 2) { echo "at if\n"; } }}

// raw if expr - multi line
{{
    if ($a < 2) {
        echo "at if\n";
    }
}}

if-elseif-else example1:

{{ if ($a < 3): }}
  at if
{{ elseif ($a > 5)  }}
  at elseif
{{ else  }}
  at else
{{ endif }}

switch example:

{{ switch ($a): }}
{{ case 3:
        echo 'in case 3';
        break;
  }}

{{ case 5: }}
{{= 'in case 5'}}
{{ break }}

{{ endswitch; }}

