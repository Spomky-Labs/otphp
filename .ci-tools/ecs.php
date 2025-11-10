<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\ConstantNotation\NativeConstantInvocationFixer;
use PhpCsFixer\Fixer\ControlStructure\NoSuperfluousElseifFixer;
use PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer;
use PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveIssetsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\CombineConsecutiveUnsetsFixer;
use PhpCsFixer\Fixer\Phpdoc\AlignMultilineCommentFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocOrderFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimConsecutiveBlankLineSeparationFixer;
use PhpCsFixer\Fixer\PhpTag\LinebreakAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestAnnotationFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestCaseStaticMethodCallsFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestClassRequiresCoversFixer;
use PhpCsFixer\Fixer\ReturnNotation\SimplifiedNullReturnFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use Symplify\CodingStandard\Fixer\Spacing\MethodChainingNewlineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $config): void {
    $header = '';
    $config->import(SetList::PSR_12);
    $config->import(SetList::CLEAN_CODE);
    $config->import(SetList::DOCTRINE_ANNOTATIONS);
    $config->import(SetList::SPACES);
    $config->import(SetList::PHPUNIT);
    $config->import(SetList::SYMPLIFY);
    $config->import(SetList::ARRAY);
    $config->import(SetList::COMMON);
    $config->import(SetList::COMMENTS);
    $config->import(SetList::CONTROL_STRUCTURES);
    $config->import(SetList::DOCBLOCK);
    $config->import(SetList::NAMESPACES);
    $config->import(SetList::STRICT);

    $config->rule(StrictParamFixer::class);
    $config->rule(StrictComparisonFixer::class);
    $config->rule(ArrayIndentationFixer::class);
    $config->rule(OrderedImportsFixer::class);
    $config->rule(ProtectedToPrivateFixer::class);
    $config->rule(DeclareStrictTypesFixer::class);
    $config->rule(NativeConstantInvocationFixer::class);
    $config->rule(LinebreakAfterOpeningTagFixer::class);
    $config->rule(CombineConsecutiveIssetsFixer::class);
    $config->rule(CombineConsecutiveUnsetsFixer::class);
    $config->rule(NoSuperfluousElseifFixer::class);
    $config->rule(NoSuperfluousPhpdocTagsFixer::class);
    $config->rule(PhpdocTrimConsecutiveBlankLineSeparationFixer::class);
    $config->rule(PhpdocOrderFixer::class);
    $config->rule(SimplifiedNullReturnFixer::class);
    $config->rule(PhpUnitTestCaseStaticMethodCallsFixer::class);
    $config->ruleWithConfiguration(ArraySyntaxFixer::class, [
        'syntax' => 'short',
    ]);
    $config->ruleWithConfiguration(NativeFunctionInvocationFixer::class, [
        'include' => ['@compiler_optimized'],
        'scope' => 'namespaced',
        'strict' => true,
    ]);
    $config->ruleWithConfiguration(HeaderCommentFixer::class, [
        'header' => $header,
    ]);
    $config->ruleWithConfiguration(AlignMultilineCommentFixer::class, [
        'comment_type' => 'all_multiline',
    ]);
    $config->ruleWithConfiguration(PhpUnitTestAnnotationFixer::class, [
        'style' => 'annotation',
    ]);
    $config->ruleWithConfiguration(GlobalNamespaceImportFixer::class, [
        'import_classes' => true,
        'import_constants' => true,
        'import_functions' => true,
    ]);

    $config->skip([
        PhpUnitTestClassRequiresCoversFixer::class,
        MethodChainingIndentationFixer::class => [__DIR__ . '/src/Resources/config'],
        MethodChainingNewlineFixer::class => [__DIR__ . '/src/Resources/config'],
    ]);

    $config->parallel();
    $config->paths([
        __DIR__ . '/../src',
        __DIR__ . '/../tests',
        __DIR__ . '/../castor.php',
        __DIR__ . '/ecs.php',
        __DIR__ . '/rector.php',
    ]);
};
