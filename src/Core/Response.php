<?php

namespace App\Core;

class Response {
    /**
     * Send a JSON response.
     */
    public static function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to another URL.
     */
    public static function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }

    /**
     * Render a view file inside the /views folder.
     */
    public static function renderView(string $viewName, array $data = [], bool $includeLayout = true): void {
        // Extract variables to local scope of this function
        if (!empty($data)) {
            extract($data);
        }

        // Setup some global variables that templates might expect
        $auth = isset($_SESSION['Auth']);
        $user = $_SESSION['userdata'] ?? null;
        
        // Define views base directory
        $baseDir = dirname(__DIR__, 2) . '/views';

        // Check if page_title is set
        if (!isset($data['page_title'])) {
            $page_title = '';
        }

        if ($includeLayout) {
            $headerFile = "{$baseDir}/layouts/header.php";
            if (file_exists($headerFile)) {
                include $headerFile;
            }
            
            $navbarFile = "{$baseDir}/layouts/navbar.php";
            if ($auth && file_exists($navbarFile)) {
                // If authenticated, list follow suggestions
                $follow_suggestions = $data['follow_suggestions'] ?? [];
                include $navbarFile;
            }
        }

        $viewFile = "{$baseDir}/pages/{$viewName}.php";
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            if (APP_ENV === 'development') {
                echo "<p style='color:red; padding: 20px;'>View file not found: views/pages/{$viewName}.php</p>";
            } else {
                http_response_code(404);
                include "{$baseDir}/pages/404.php";
            }
        }

        if ($includeLayout) {
            $footerFile = "{$baseDir}/layouts/footer.php";
            if (file_exists($footerFile)) {
                include $footerFile;
            }
        }
        
        // Clear flash session data after rendering the view
        self::clearFlashData();
        exit;
    }

    /**
     * Clear temporary flash session data (errors, formdata)
     */
    private static function clearFlashData(): void {
        unset($_SESSION['error'], $_SESSION['formdata']);
    }
}
