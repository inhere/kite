
{{ include('parts/class-comments', [
  'user' => $user,
  'date' => $date
]) }}
class {{ $className | nl }}
{
{{ foreach ($fields as $field): }}

    /**
     * {{= $field->comment | nl }}
     *
     * @param {{= $field->getType($lang) | nl }}
     */
    public ${{= $field->name | camel }};
{{ endforeach }}
}
