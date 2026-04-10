<?php

declare(strict_types=1);

namespace Ixopay\Client\Tests\Recipes;

use Ixopay\Tools\Recipes\RecipeBuilder;
use PHPUnit\Framework\TestCase;

final class RecipeBuilderTest extends TestCase
{
    public function testItRendersARecipeTemplateAsDocusaurusMdx(): void
    {
        require_once dirname(__DIR__, 2) . '/tools/recipes/RecipeBuilder.php';

        $builder = new RecipeBuilder();

        $mdx = $builder->renderRecipe([
            'title' => 'How to use receipt templates',
            'slug' => 'use-receipt-templates',
            'description' => 'Render branded receipt templates after payment completion.',
            'position' => 10,
            'prerequisites' => [
                'A configured connector.',
            ],
            'steps' => [
                [
                    'title' => 'Store the payment reference',
                    'body' => 'Persist the IXOPAY reference with the merchant order.',
                    'code' => [
                        'language' => 'php',
                        'content' => '$order->payment_reference = $result->getUuid();',
                    ],
                ],
            ],
            'validation' => [
                'The generated page contains the receipt template title.',
            ],
            'links' => [
                [
                    'label' => 'Recipes',
                    'url' => 'https://documentation.ixopay.com/docs/recipes',
                ],
            ],
        ]);

        $this->assertStringContainsString('title: "How to use receipt templates"', $mdx);
        $this->assertStringContainsString('## Prerequisites', $mdx);
        $this->assertStringContainsString('### Step 1 - Store the payment reference', $mdx);
        $this->assertStringContainsString('```php', $mdx);
        $this->assertStringContainsString('[Recipes](https://documentation.ixopay.com/docs/recipes)', $mdx);
    }

    public function testItRejectsIncompleteRecipeTemplates(): void
    {
        require_once dirname(__DIR__, 2) . '/tools/recipes/RecipeBuilder.php';

        $this->expectException(\InvalidArgumentException::class);

        (new RecipeBuilder())->renderRecipe([
            'title' => 'Incomplete',
        ]);
    }
}
