<?php

declare(strict_types=1);

namespace Ixopay\Tools\Recipes;

final class RecipeBuilder
{
    public function buildDirectory(string $sourceDirectory, string $targetDirectory): array
    {
        if (!is_dir($sourceDirectory)) {
            throw new \InvalidArgumentException("Recipe source directory does not exist: {$sourceDirectory}");
        }

        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0777, true) && !is_dir($targetDirectory)) {
            throw new \RuntimeException("Could not create recipe target directory: {$targetDirectory}");
        }

        $builtFiles = [];
        $recipeFiles = glob(rtrim($sourceDirectory, '/') . '/*.json') ?: [];
        sort($recipeFiles);

        foreach ($recipeFiles as $recipeFile) {
            $recipe = $this->loadRecipe($recipeFile);
            $targetPath = rtrim($targetDirectory, '/') . '/' . $recipe['slug'] . '.mdx';
            file_put_contents($targetPath, $this->renderRecipe($recipe));
            $builtFiles[] = $targetPath;
        }

        file_put_contents(
            rtrim($targetDirectory, '/') . '/_category_.json',
            json_encode([
                'label' => 'How to ...',
                'position' => 2,
                'link' => [
                    'type' => 'generated-index',
                    'description' => 'Step-by-step IXOPAY recipes for common merchant use-cases.',
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
        );

        return $builtFiles;
    }

    public function renderRecipe(array $recipe): string
    {
        $this->validateRecipe($recipe);

        $markdown = "---\n";
        $markdown .= 'title: "' . $this->escapeYaml($recipe['title']) . '"' . "\n";
        $markdown .= 'description: "' . $this->escapeYaml($recipe['description']) . '"' . "\n";
        $markdown .= 'sidebar_position: ' . (int) ($recipe['position'] ?? 1) . "\n";
        $markdown .= "---\n\n";
        $markdown .= '# ' . $recipe['title'] . "\n\n";
        $markdown .= $recipe['description'] . "\n\n";

        $markdown .= "## Prerequisites\n\n";
        foreach ($recipe['prerequisites'] as $prerequisite) {
            $markdown .= '- ' . $prerequisite . "\n";
        }

        $markdown .= "\n## Recipe\n\n";
        foreach ($recipe['steps'] as $index => $step) {
            $markdown .= '### Step ' . ($index + 1) . ' - ' . $step['title'] . "\n\n";
            $markdown .= $step['body'] . "\n\n";

            if (!empty($step['code'])) {
                $language = $step['code']['language'] ?? 'text';
                $markdown .= "```{$language}\n" . rtrim($step['code']['content']) . "\n```\n\n";
            }
        }

        if (!empty($recipe['validation'])) {
            $markdown .= "## Validation\n\n";
            foreach ($recipe['validation'] as $validation) {
                $markdown .= '- ' . $validation . "\n";
            }
            $markdown .= "\n";
        }

        if (!empty($recipe['links'])) {
            $markdown .= "## Related documentation\n\n";
            foreach ($recipe['links'] as $link) {
                $markdown .= '- [' . $link['label'] . '](' . $link['url'] . ')' . "\n";
            }
            $markdown .= "\n";
        }

        return $markdown;
    }

    private function loadRecipe(string $recipeFile): array
    {
        $decoded = json_decode((string) file_get_contents($recipeFile), true);

        if (!is_array($decoded)) {
            throw new \RuntimeException("Recipe file is not valid JSON: {$recipeFile}");
        }

        return $decoded;
    }

    private function validateRecipe(array $recipe): void
    {
        foreach (['title', 'slug', 'description', 'prerequisites', 'steps'] as $required) {
            if (!array_key_exists($required, $recipe)) {
                throw new \InvalidArgumentException("Recipe is missing required field: {$required}");
            }
        }

        if (!is_array($recipe['prerequisites']) || !is_array($recipe['steps'])) {
            throw new \InvalidArgumentException('Recipe prerequisites and steps must be arrays.');
        }
    }

    private function escapeYaml(string $value): string
    {
        return str_replace('"', '\"', $value);
    }
}
