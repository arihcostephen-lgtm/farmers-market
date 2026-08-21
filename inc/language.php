<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'lg'], true)) {
    $_SESSION['language'] = $_GET['lang'];
}

$currentLanguage = $_SESSION['language'] ?? 'en';

$translations = [
    'en' => [
        'Home' => 'Home',
        'About Us' => 'About Us',
        'Blog' => 'Blog',
        'Login' => 'Login',
        'Register' => 'Register',
        'Farmer Account' => 'Farmer Account',
        'Admin Login' => 'Admin Login',
        'Profile Update' => 'Profile Update',
        'Order List' => 'Order List',
        'Log Out' => 'Log Out',
        'Language' => 'Language',
        'English' => 'English',
        'Luganda' => 'Luganda',
        'Notifications' => 'Notifications',
        'Manage Products' => 'Manage Products',
        'Add New Product' => 'Add New Product',
        'Trash' => 'Trash',
        'Save Product' => 'Save Product',
        'Price is negotiable' => 'Price is negotiable',
    ],
    'lg' => [
        'Home' => 'Awaka',
        'About Us' => 'Ebikwata ku Ffe',
        'Blog' => 'Blog',
        'Login' => 'Yingira',
        'Register' => 'Wewandiise',
        'Farmer Account' => 'Akawunti y’Omulimi',
        'Admin Login' => 'Yingira nga Admin',
        'Profile Update' => 'Kyuusa Ebikwata Kwo',
        'Order List' => 'Olukalala lw’Ebyalagiddwa',
        'Log Out' => 'Fuluma',
        'Language' => 'Olulimi',
        'English' => 'Lungereza',
        'Luganda' => 'Luganda',
        'Notifications' => 'Obubaka Obupya',
        'Manage Products' => 'Fuga Ebintu',
        'Add New Product' => 'Yongerako Ekintu',
        'Trash' => 'Kasasiro',
        'Save Product' => 'Tereka Ekintu',
        'Price is negotiable' => 'Omuwendo guyinza okuteesebwako',
    ],
];

function t(string $text): string {
    global $translations, $currentLanguage;
    return $translations[$currentLanguage][$text] ?? $text;
}

function language_url(string $language): string {
    $path = $_SERVER['REQUEST_URI'] ?? '/';
    $path = preg_replace('/([?&])lang=[^&]*/', '$1', $path);
    $path = rtrim($path, '?&');
    $separator = strpos($path, '?') === false ? '?' : '&';
    return htmlspecialchars($path . $separator . 'lang=' . $language, ENT_QUOTES, 'UTF-8');
}
?>
