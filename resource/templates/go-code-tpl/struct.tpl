
// {{ $className }} struct
//
// Gen by:
//   {{ $genMark | nl }}
type {{ $className }} struct {
{{ foreach ($fields as $field): }}
    // {{= $field->name | camel:true }} {{= $field->comment | nl }}
    {{= $field->name | camel:true }} {{= $field->getType($lang) }} `json:"{{= $field->name | snake }}"`
{{ endforeach }}
}