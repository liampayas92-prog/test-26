<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final readonly class ViewRenderer
{
    public function __construct(private string $viewPath)
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = [], string $layout = 'layout'): string
    {
        $content = $this->renderTemplate($template, $data);

        return $this->renderTemplate($layout, array_merge($data, ['content' => $content]));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderTemplate(string $template, array $data): string
    {
        $file = $this->viewPath . DIRECTORY_SEPARATOR . $template . '.php';

        if (! is_file($file)) {
            throw new RuntimeException(sprintf('View "%s" was not found.', $template));
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $file;

        return (string) ob_get_clean();
    }
}
