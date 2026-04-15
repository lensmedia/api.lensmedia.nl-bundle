<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()->in([
    __DIR__.'/src',
]);

return new PhpCsFixer\Config()
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules([
        // Symfony is our base
        '@Symfony' => true,
        '@Symfony:risky' => true,

        // Migrations these have all migrations from previous versions chained in them, update to latest when they become available
        '@PHP8x4Migration:risky' => true,
        '@PHP8x4Migration' => true,

        // Overrides from preset
        'cast_spaces' => ['space' => 'none'],

        // @Symfony - symfony enables this, but we are not ready yet
        'declare_strict_types' => false,

        // @Symfony - prefer imports over inline `\count`, `\Exception`, etc.
        'global_namespace_import' => [
            'import_constants' => true,
            'import_functions' => true,
            'import_classes' => true,
        ],

        // @Symfony - alignment mirrors argument list no need to do weird spacing stuff and track that with every change
        'phpdoc_align' => ['align' => 'left'],

        // @Symfony - this shit gets long for complex messages
        'single_line_throw' => false,

        // @Symfony - added ignore for todos
        'phpdoc_to_comment' => ['ignored_tags' => ['todo', 'see']],

        // Others
        'array_indentation' => true,
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'comment_to_phpdoc' => true,
        'date_time_create_from_format_call' => true,
        'date_time_immutable' => true,
        'declare_parentheses' => true,
        'explicit_string_variable' => true,
        'explicit_indirect_variable' => true,
        'get_class_to_class_keyword' => true,
        'heredoc_indentation' => ['indentation' => 'same_as_start'],
        'multiline_comment_opening_closing' => true,
        'no_superfluous_elseif' => true,
        'no_unset_on_property' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'no_unneeded_control_parentheses' => [
            'statements' => [
                'break',
                'clone',
                'continue',
                'echo_print',
                // 'negative_instanceof', // preserve !($foo instanceof Bar)
                'others',
                'return',
                'switch_case',
                'yield',
                'yield_from'
            ],
        ],
        'nullable_type_declaration_for_default_null_value' => true,
        'operator_linebreak' => true,
        'ordered_interfaces' => true,
        'phpdoc_to_property_type' => true,
        'phpdoc_to_return_type' => true,
        'self_static_accessor' => true,
        'simplified_if_return' => true,
        'strict_comparison' => true,
        'strict_param' => true,
    ]);
