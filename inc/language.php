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
        'Hello' => 'Hello',
        'Customer dashboard' => 'Customer dashboard',
        'Dashboard Overview' => 'Dashboard Overview',
        'View Cart' => 'View Cart',
        'Check Products' => 'Check Products',
        'Browse Marketplace' => 'Browse Marketplace',
        'Add Comments' => 'Add Comments',
        'Order History' => 'Order History',
        'Inquiry History' => 'Inquiry History',
        'Available Products' => 'Available Products',
        'Browse fresh farm items.' => 'Browse fresh farm items.',
        'Your Orders' => 'Your Orders',
        'Quick access to your order list.' => 'Quick access to your order list.',
        'Comments Posted' => 'Comments Posted',
        'Share feedback with farmers.' => 'Share feedback with farmers.',
        'Your Cart' => 'Your Cart',
        'Your cart is empty. Start browsing products to add items.' => 'Your cart is empty. Start browsing products to add items.',
        'Quantity' => 'Quantity',
        'Category' => 'Category',
        'Ordered on' => 'Ordered on',
        'total' => 'total',
        'Active' => 'Active',
        'Pending' => 'Pending',
        'Inactive' => 'Inactive',
        'View Full Order History' => 'View Full Order History',
        'Check Available Products' => 'Check Available Products',
        'No products are available at the moment.' => 'No products are available at the moment.',
        'Fresh farm produce' => 'Fresh farm produce',
        'Available' => 'Available',
        'In stock' => 'In stock',
        'Out of stock' => 'Out of stock',
        'Harvest date' => 'Harvest date',
        'Season' => 'Season',
        'Contact Farmer' => 'Contact Farmer',
        'Ask' => 'Ask',
        'Add to Cart' => 'Add to Cart',
        'Order' => 'Order',
        'Search Products' => 'Search Products',
        'Search product name...' => 'Search product name...',
        'Filter by Category' => 'Filter by Category',
        'All Categories' => 'All Categories',
        'Browse Marketplace' => 'Browse Marketplace',
        'Add Comments' => 'Add Comments',
        'Subject' => 'Subject',
        'Comment' => 'Comment',
        'Submit Comment' => 'Submit Comment',
        'Recent Comments' => 'Recent Comments',
        'Comment submitted successfully. Thank you!' => 'Comment submitted successfully. Thank you!',
        'Unable to save your comment. Please try again.' => 'Unable to save your comment. Please try again.',
        'Language' => 'Language',
        'CONTACT US' => 'CONTACT US',
        'FOLLOW US' => 'FOLLOW US',
        'Published' => 'Published',
        'Uncategorized' => 'Uncategorized',
        'Local Farm Market' => 'Local Farm Market',
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
        'Hello' => 'Oli otya',
        'Customer dashboard' => 'Ekibanja ky’omugulizi',
        'Dashboard Overview' => 'Okulaba ku kibanja',
        'View Cart' => 'Laba ebiri mu kagaali',
        'Check Products' => 'Kebera ebintu',
        'Browse Marketplace' => 'Noonya mu katale',
        'Add Comments' => 'Oteeko endowooza',
        'Order History' => 'Ebyalagiddwa eby’edda',
        'Inquiry History' => 'Ebibuuzo eby’edda',
        'Available Products' => 'Ebintu ebiriwo',
        'Browse fresh farm items.' => 'Noonya ebintu ebipya eby’obulimi.',
        'Your Orders' => 'Ebyalagiddwa byo',
        'Quick access to your order list.' => 'Tuuka mangu ku lukalala lw’ebyolagiddwa.',
        'Comments Posted' => 'Endowooza zo',
        'Share feedback with farmers.' => 'Gabana endowooza n’abalimi.',
        'Your Cart' => 'Akagaali ko',
        'Your cart is empty. Start browsing products to add items.' => 'Akagaali ko kamu. Tandika okunoonya ebintu okubyongeramu.',
        'Quantity' => 'Obungi',
        'Category' => 'Ekika',
        'Ordered on' => 'Kyawagiddwa nga',
        'total' => 'awamu',
        'Active' => 'Kikola',
        'Pending' => 'Kyalindirirwa',
        'Inactive' => 'Tekikola',
        'View Full Order History' => 'Laba ebyalagiddwa byonna',
        'Check Available Products' => 'Kebera ebintu ebiriwo',
        'No products are available at the moment.' => 'Tewali bintu biriiwo mu kiseera kino.',
        'Fresh farm produce' => 'Ebintu ebipya eby’obulimi',
        'Available' => 'Ebiriwo',
        'In stock' => 'Kiri mu sitoowa',
        'Out of stock' => 'Kiwedde mu sitoowa',
        'Harvest date' => 'Olunaku lw’okukungula',
        'Season' => 'Ekiseera',
        'Contact Farmer' => 'Tuukirira omulimi',
        'Ask' => 'Buuza',
        'Add to Cart' => 'Yongera ku kagaali',
        'Order' => 'Lagira',
        'Search Products' => 'Noonya ebintu',
        'Search product name...' => 'Noonya erinnya ly’ekintu...',
        'Filter by Category' => 'Londa okusinziira ku kika',
        'All Categories' => 'Ebika byonna',
        'Subject' => 'Omutwe',
        'Comment' => 'Endowooza',
        'Submit Comment' => 'Sindika endowooza',
        'Recent Comments' => 'Endowooza ezisembyeyo',
        'Comment submitted successfully. Thank you!' => 'Endowooza etumiddwa bulungi. Weebale!',
        'Unable to save your comment. Please try again.' => 'Tekisobose kutereka ndowooza yo. Ddamu ogezeeko.',
        'CONTACT US' => 'TUKWATAGANE',
        'FOLLOW US' => 'TUGOBERE',
        'Published' => 'Kifulumiziddwa',
        'Uncategorized' => 'Tekyateekebwa mu kika',
        'Local Farm Market' => 'Ak market k’ebyobulimi',
    ],
];

function t(string $text): string {
    global $translations, $currentLanguage;
    return $translations[$currentLanguage][$text] ?? $text;
}

function localize_page_output(string $output): string {
    global $translations, $currentLanguage;
    if ($currentLanguage === 'en') {
        return $output;
    }

    $keys = array_keys($translations['lg']);
    usort($keys, static fn(string $a, string $b): int => strlen($b) <=> strlen($a));
    foreach ($keys as $key) {
        $output = str_replace($key, $translations['lg'][$key], $output);
    }
    return $output;
}

ob_start('localize_page_output');

function language_url(string $language): string {
    $path = $_SERVER['REQUEST_URI'] ?? '/';
    $path = preg_replace('/([?&])lang=[^&]*/', '$1', $path);
    $path = rtrim($path, '?&');
    $separator = strpos($path, '?') === false ? '?' : '&';
    return htmlspecialchars($path . $separator . 'lang=' . $language, ENT_QUOTES, 'UTF-8');
}
?>
