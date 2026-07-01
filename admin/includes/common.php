<?php
// admin/includes/common.php
// Helper functions shared across admin pages.

if (!function_exists('tampilkanBintang')) {
    function tampilkanBintang(int $rating = 0): string
    {
        $rating = max(0, min(5, $rating));
        $stars  = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
        return '<span class="text-warning" style="letter-spacing:0.05rem;font-size:0.95rem;">' . $stars . '</span>';
    }
}
