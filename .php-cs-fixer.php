<?php

use PhpCsFixer\Config;

final class Php73 extends Config {
    public function __construct() {
        parent::__construct('KRNStyle');
        $this->setRiskyAllowed(true);
    }

    public function getRules(): array {
        $rules = [
        '@Symfony' => true,
        'array_syntax' => [
        'syntax' => 'short',
        ],
                'binary_operator_spaces' => [
                ],
                'blank_line_after_namespace' => true,
                'blank_line_after_opening_tag' => true,
                'braces' => [
                'position_after_functions_and_oop_constructs' => 'same',
                ],
                'blank_line_before_statement' => ['statements' => ['return']],
                'concat_space' => ['spacing' => 'one'],
                'function_typehint_space' => true,
                'lowercase_cast' => true,
                'native_function_casing' => true,
                'new_with_braces' => true,
                'no_empty_comment' => true,
                'no_empty_phpdoc' => true,
                'no_empty_statement' => true,
                'no_leading_import_slash' => true,
                'no_leading_namespace_whitespace' => true,
                'no_multiline_whitespace_around_double_arrow' => true,
                'no_short_bool_cast' => true,
                'no_singleline_whitespace_before_semicolons' => true,
                'no_trailing_comma_in_singleline_array' => true,
                'no_unneeded_control_parentheses' => true,
                'no_unused_imports' => true,
                'no_whitespace_before_comma_in_array' => true,
                'no_whitespace_in_blank_line' => true,
                'normalize_index_brace' => true,
                'not_operator_with_successor_space' => true,
                'object_operator_without_whitespace' => true,
                'ordered_imports' => true,
                'php_unit_construct' => true,
                'php_unit_dedicate_assert' => true,
                'php_unit_method_casing' => false,
                'phpdoc_single_line_var_spacing' => true,
                'phpdoc_trim' => true,
                'random_api_migration' => true,
                'self_accessor' => true,
                'short_scalar_cast' => true,
                'single_blank_line_before_namespace' => true,
                'single_class_element_per_statement' => true,
                'single_quote' => true,
                'space_after_semicolon' => true,
                'standardize_not_equals' => true,
                'ternary_operator_spaces' => true,
                'trim_array_spaces' => true,
                'unary_operator_spaces' => true,
                'whitespace_after_comma_in_array' => true,
        ];

        return $rules;
    }
}

$config = new Php73();
$finder = $config->getFinder();
$finder->in([
    'src',
    'tests',
]);

return $config;
