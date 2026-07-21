<?php

namespace App\Helpers;

class MailTemplate
{
    public static function render(string $view, array $data = []): string
    {
        extract($data);
        
        ob_start();
        require __DIR__ . "/../../resources/emails/{$view}.php";
        $content = ob_get_clean();

        ob_start();
        require __DIR__ . '/../../resources/emails/layout.php';
        return ob_get_clean();
    }
}
