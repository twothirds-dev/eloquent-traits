<?php

/**
 * TwoThirds Development PHPCS ruleset
 *
 * https://github.com/FriendsOfPHP/PHP-CS-Fixer
 */
$rules = [
    '@PSR2' => true,

    'align_multiline_comment' => [
        'comment_type' => 'all_multiline',
    ],

    'array_syntax' => [
        'syntax' => 'short',
    ],

    'binary_operator_spaces' => [
        'default'   => 'align_single_space_minimal',
        'operators' => [
            '&'   => null,
            '&='  => null,
            '&&'  => null,
            '||'  => null,
            'and' => null,
            'or'  => null,
            'xor' => null,
        ],
    ],

    'blank_line_after_opening_tag' => true,

    'blank_line_before_statement' => true,

    'cast_spaces' => true,

    'class_keyword_remove' => false,

    'combine_consecutive_issets' => true,

    'combine_consecutive_unsets' => true,

    'compact_nullable_typehint' => false,

    'concat_space' => [
        'spacing' => 'one',
    ],

    'declare_equal_normalize' => [
        'space' => 'single',
    ],

    'function_typehint_space' => true,

    'general_phpdoc_annotation_remove' => [
        'annotations' => [
            'author',
        ],
    ],

    'include' => true,

    'linebreak_after_opening_tag' => true,

    'list_syntax' => true,

    'lowercase_cast' => true,

    'magic_constant_casing' => true,

    'method_separation' => true,

    'native_function_casing' => true,

    'new_with_braces' => false,

    'no_blank_lines_after_class_opening' => true,

    'no_blank_lines_after_phpdoc' => true,

    'no_blank_lines_before_namespace' => false,

    'no_empty_comment' => false,

    'no_empty_phpdoc' => true,

    'no_empty_statement' => true,

    'no_extra_consecutive_blank_lines' => true,

    'no_leading_import_slash' => true,

    'no_leading_namespace_whitespace' => true,

    'no_mixed_echo_print' => false,

    'no_multiline_whitespace_around_double_arrow' => true,

    'no_multiline_whitespace_before_semicolons' => true,

    'no_null_property_initialization' => true,

    'no_short_bool_cast' => true,

    'no_short_echo_tag' => true,

    'no_singleline_whitespace_before_semicolons' => true,

    'no_spaces_around_offset' => true,

    'no_superfluous_elseif' => true,

    'no_trailing_comma_in_list_call' => true,

    'no_trailing_comma_in_singleline_array' => true,

    'no_unneeded_control_parentheses' => true,

    'no_unneeded_curly_braces' => true,

    'no_unneeded_final_method' => true,

    'no_unused_imports' => true,

    'no_useless_else' => true,

    'no_useless_return' => true,

    'no_whitespace_before_comma_in_array' => true,

    'no_whitespace_in_blank_line' => true,

    'normalize_index_brace' => true,

    'not_operator_with_space' => false,

    'not_operator_with_successor_space' => true,

    'object_operator_without_whitespace' => true,

    'ordered_class_elements' => [
        'order' => [
            'use_trait',
            'constant_public',
            'constant_protected',
            'constant_private',
            'property_public',
            'property_protected',
            'property_private',
            'construct',
            'destruct',
            'phpunit',
            'method_public',
            'method_protected',
            'method_private',
            'magic',
        ],
    ],

    'ordered_imports' => [
        'importsOrder'  => null,
        'sortAlgorithm' => 'length',
    ],

    'php_unit_fqcn_annotation' => true,

    'php_unit_test_class_requires_covers' => false,

    'phpdoc_add_missing_param_annotation' => [
        'only_untyped' => false,
    ],

    'phpdoc_align' => false,

    'phpdoc_annotation_without_dot' => true,

    'phpdoc_indent' => true,

    'phpdoc_inline_tag' => true,

    'phpdoc_no_access' => true,

    'phpdoc_no_alias_tag' => true,

    'phpdoc_no_empty_return' => false,

    'phpdoc_no_package' => true,

    'phpdoc_no_useless_inheritdoc' => true,

    'phpdoc_order' => true,

    'phpdoc_return_self_reference' => true,

    'phpdoc_scalar' => true,

    'phpdoc_separation' => true,

    'phpdoc_single_line_var_spacing' => false,

    'phpdoc_summary' => false,

    'phpdoc_to_comment' => true,

    'phpdoc_trim' => true,

    'phpdoc_types' => true,

    'phpdoc_types_order' => [
        'null_adjustment' => 'always_last',
        'sort_algorithm'  => 'none',
    ],

    'phpdoc_var_without_name' => false,

    'protected_to_private' => false,

    'return_type_declaration' => [
        'space_before' => 'one',
    ],

    'self_accessor' => false,

    'semicolon_after_instruction' => true,

    'short_scalar_cast' => true,

    'single_blank_line_before_namespace' => true,

    'single_line_comment_style' => true,

    'single_quote' => true,

    'space_after_semicolon' => true,

    'standardize_not_equals' => true,

    'ternary_operator_spaces' => true,

    'ternary_to_null_coalescing' => true,

    'trailing_comma_in_multiline_array' => true,

    'trim_array_spaces' => true,

    'unary_operator_spaces' => true,

    'whitespace_after_comma_in_array' => true,

    'yoda_style' => [
        'equal'            => false,
        'identical'        => false,
        'less_and_greater' => false,
    ],
 ];

return PhpCsFixer\Config::create()
    ->setUsingCache(false)
    ->setRules($rules)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->path('src')
            ->path('app')
            ->path('config')
            ->path('database')
            ->path('routes')
            ->path('tests')
            ->in(getcwd())
    );
