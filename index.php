<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
$startDate = new DateTime('2020-02-14');
$currentDate = new DateTime('now');
$interval = $startDate->diff($currentDate);
$daysTogether = $interval->days;
$yearsTogether = $interval->y;
$monthsTogether = $interval->m;
$formSuccess = false;
$formError = '';
$guestbook = [];
if (file_exists('guestbook_data.json')) {
    $rawData = file_get_contents('guestbook_data.json');
    $guestbook = json_decode($rawData, true);
    if (!is_array($guestbook)) {
        $guestbook = [];
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guestbook_submit'])) {
    $name = trim($_POST['guest_name'] ?? '');
    $email = trim($_POST['guest_email'] ?? '');
    $message = trim($_POST['guest_message'] ?? '');
    $honeypot = trim($_POST['website'] ?? '');
    if (!empty($honeypot)) {
        $formError = 'Terjadi kesalahan. Silakan coba lagi.';
    } elseif (empty($name) || empty($message)) {
        $formError = 'Nama dan pesan wajib diisi.';
    } elseif (strlen($name) < 2) {
        $formError = 'Nama terlalu pendek.';
    } elseif (strlen($message) < 10) {
        $formError = 'Pesan terlalu pendek, minimal 10 karakter.';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formError = 'Format email tidak valid.';
    } else {
        $entry = [
            'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            'email' => htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
            'message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
            'date' => date('d F Y, H:i'),
            'timestamp' => time()
        ];
        array_unshift($guestbook, $entry);
        $guestbook = array_slice($guestbook, 0, 50);
        file_put_contents('guestbook_data.json', json_encode($guestbook, JSON_PRETTY_PRINT));
        $formSuccess = true;
        $_SESSION['form_submitted'] = true;
        header('Location: ' . $_SERVER['PHP_SELF'] . '?sent=1');
        exit;
    }
}
if (isset($_GET['sent']) && $_GET['sent'] === '1') {
    $formSuccess = true;
}
function sanitize($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}
function generateId() {
    return bin2hex(random_bytes(8));
}
function timeAgo($timestamp) {
    $diff = time() - $timestamp;
    if ($diff < 60) return 'baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    if ($diff < 2592000) return floor($diff / 86400) . ' hari lalu';
    return date('d M Y', $timestamp);
}
function getInitials($name) {
    $parts = explode(' ', trim($name));
    $initials = '';
    foreach ($parts as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    return substr($initials, 0, 2);
}
function getAvatarColor($name) {
    $colors = ['#8B9A7B', '#A8B5A0', '#C9A96E', '#B8A088', '#7B8B6F', '#9A8B7B', '#6F7B8B', '#8B7B6F'];
    $hash = 0;
    for ($i = 0; $i < strlen($name); $i++) {
        $hash = ord($name[$i]) + (($hash << 5) - $hash);
    }
    return $colors[abs($hash) % count($colors)];
}
$coupleData = [
    'name' => 'NALYS',
    'fullName' => 'Nanda & Felys',
    'tagline' => '',
    'description' => 'Kami adalah dua insan yang dipertemukan oleh takdir, berjalan bersama dalam suka dan duka, merangkai kisah cinta yang sederhana namun penuh makna.',
    'since' => '30 July 2020',
    'male' => [
        'name' => 'Ananda Kurnia',
        'nickname' => 'Nanda',
        'role' => 'The Anchor',
        'birthday' => '12 Maret 1998',
        'zodiac' => 'Pisces',
        'mbti' => 'INTJ',
        'hobby' => 'Fotografi & Hiking',
        'favorite_food' => 'Nasi Goreng Spesial',
        'favorite_song' => 'Perfect - Ed Sheeran',
        'favorite_movie' => 'The Notebook',
        'quote' => 'Cinta bukan tentang memiliki, tapi tentang menghargai keberadaan satu sama lain.',
        'traits' => ['Tenang', 'Pemikir', 'Setia', 'Humoris'],
        'skills' => [
            'Fotografi' => 90,
            'Memasak' => 75,
            'Navigasi' => 85,
            'Kesabaran' => 95
        ],
        'bio' => 'Seorang pria yang menemukan kedamaian dalam lensa kamera dan puncak gunung. Bagi Nanda, cinta adalah perjalanan tanpa akhir yang ingin ia tempuh bersama Felys.'
    ],
    'female' => [
        'name' => 'Felys Anggraini',
        'nickname' => 'Felys',
        'role' => 'The Compass',
        'birthday' => '28 Juli 2000',
        'zodiac' => 'Leo',
        'mbti' => 'ENFP',
        'hobby' => 'Melukis & Baking',
        'favorite_food' => 'Tiramisu',
        'favorite_song' => 'All of Me - John Legend',
        'favorite_movie' => 'La La Land',
        'quote' => 'Cinta itu seperti lukisan, setiap goresan punya cerita dan warna tersendiri.',
        'traits' => ['Ceria', 'Kreatif', 'Empatik', 'Berani'],
        'skills' => [
            'Melukis' => 92,
            'Baking' => 88,
            'Dekorasi' => 85,
            'Mendengarkan' => 96
        ],
        'bio' => 'Seorang seniman yang melihat dunia dengan penuh warna. Bagi Felys, Nanda adalah kanvas terbaik yang pernah takdir lukis dalam hidupnya.'
    ]
];
$timelineData = [
    [
        'date' => '14 Februari 2020',
        'title' => 'Awal Pertemuan',
        'description' => 'Bertemu pertama kali di sebuah kafe kecil di sudut kota. Sebuah kebetulan yang ternyata adalah awal dari segalanya.',
        'icon' => 'coffee',
        'location' => 'Kafe Senja, Jakarta'
    ],
    [
        'date' => '21 Maret 2020',
        'title' => 'Kencan Pertama',
        'description' => 'Nonton film bersama untuk pertama kalinya. Gugup, canggung, tapi penuh tawa. Saat itulah kami tahu ada sesuatu yang istimewa.',
        'icon' => 'film',
        'location' => 'Bioskop XXI, Mall Central Park'
    ],
    [
        'date' => '14 Mei 2020',
        'title' => 'Resmi Menjalin Hubungan',
        'description' => 'Di bawah langit senja yang jingga, Nanda memberanikan diri mengungkapkan perasaannya. Dan Felys menjawab dengan senyuman termanis.',
        'icon' => 'heart',
        'location' => 'Taman Suropati, Jakarta'
    ],
    [
        'date' => '10 Agustus 2020',
        'title' => 'Perjalanan Pertama Bersama',
        'description' => 'Liburan pertama kami ke Bandung. Hujan sepanjang hari, tapi hati kami tetap hangat. Tersesat bersama ternyata menyenangkan.',
        'icon' => 'plane',
        'location' => 'Bandung, Jawa Barat'
    ],
    [
        'date' => '25 Desember 2020',
        'title' => 'Natal Pertama Bersama',
        'description' => 'Merayakan hari Natal bersama untuk pertama kalinya. Bertukar kado sederhana namun penuh makna.',
        'icon' => 'gift',
        'location' => 'Rumah Felys, Depok'
    ],
    [
        'date' => '14 Februari 2021',
        'title' => 'Anniversary Pertama',
        'description' => 'Satu tahun penuh cerita. Merayakan anniversary pertama dengan dinner romantis di restoran favorit kami.',
        'icon' => 'cake',
        'location' => 'Amuz Gourmet, Jakarta'
    ],
    [
        'date' => '7 Juni 2021',
        'title' => 'Adopsi Kucing Pertama',
        'description' => 'Mengadopsi kucing lucu bernama Mochi. Keluarga kecil kami bertambah satu anggota berbulu.',
        'icon' => 'cat',
        'location' => 'Shelter Kucing, Bogor'
    ],
    [
        'date' => '15 September 2021',
        'title' => 'Pindahan ke Apartemen Baru',
        'description' => 'Memulai babak baru dengan pindah ke apartemen kecil yang kami dekorasi bersama. Setiap sudut punya cerita.',
        'icon' => 'home',
        'location' => 'Apartemen Green View, Jakarta Selatan'
    ],
    [
        'date' => '31 Desember 2021',
        'title' => 'Malam Tahun Baru',
        'description' => 'Menyambut tahun baru bersama di rooftop. Kembang api di langit, dan janji untuk terus bersama di hati.',
        'icon' => 'star',
        'location' => 'Rooftop Apartemen'
    ],
    [
        'date' => '14 Februari 2022',
        'title' => 'Anniversary Kedua',
        'description' => 'Dua tahun berjalan bersama. Kali ini kami merayakannya dengan camping di alam terbuka, menikmati bintang berdua.',
        'icon' => 'tent',
        'location' => 'Gunung Pancar, Bogor'
    ],
    [
        'date' => '20 Agustus 2022',
        'title' => 'Trip ke Bali',
        'description' => 'Liburan impian ke Pulau Dewata. Sunset di Tanah Lot, bermain di pantai, dan menikmati kuliner khas Bali.',
        'icon' => 'sun',
        'location' => 'Bali, Indonesia'
    ],
    [
        'date' => '14 Februari 2023',
        'title' => 'Anniversary Ketiga',
        'description' => 'Tiga tahun penuh warna. Merayakan dengan photoshoot couple pertama kami, mengabadikan cinta dalam bingkai.',
        'icon' => 'camera',
        'location' => 'Studio Foto, Kemang'
    ],
    [
        'date' => '5 November 2023',
        'title' => 'Konser Musik Pertama Bersama',
        'description' => 'Menonton konser band favorit kami. Bernyanyi bersama di tengah ribuan orang, tapi terasa hanya ada kami berdua.',
        'icon' => 'music',
        'location' => 'GBK, Jakarta'
    ]
];
$galleryData = [
    [
        'id' => 1,
        'title' => 'Senja di Dermaga',
        'category' => 'Romantic',
        'color' => '#E8D5C4',
        'description' => 'Menikmati matahari terbenam di dermaga tua',
        'date' => 'Januari 2023'
    ],
    [
        'id' => 2,
        'title' => 'Piknik di Taman',
        'category' => 'Casual',
        'color' => '#D4E0D0',
        'description' => 'Weekend santai dengan bekal buatan sendiri',
        'date' => 'Maret 2023'
    ],
    [
        'id' => 3,
        'title' => 'Sunset Bali',
        'category' => 'Travel',
        'color' => '#F0D5C8',
        'description' => 'Golden hour di Pantai Seminyak',
        'date' => 'Agustus 2022'
    ],
    [
        'id' => 4,
        'title' => 'Coffee Date',
        'category' => 'Casual',
        'color' => '#D8CFC4',
        'description' => 'Ritual kopi pagi di kafe favorit',
        'date' => 'Februari 2024'
    ],
    [
        'id' => 5,
        'title' => 'Camping Seru',
        'category' => 'Adventure',
        'color' => '#C8D4C8',
        'description' => 'Malam di bawah bintang Gunung Pancar',
        'date' => 'Februari 2022'
    ],
    [
        'id' => 6,
        'title' => 'Anniversary Dinner',
        'category' => 'Romantic',
        'color' => '#E0D0D8',
        'description' => 'Makan malam romantis anniversary ke-3',
        'date' => 'Februari 2023'
    ],
    [
        'id' => 7,
        'title' => 'Photoshoot Couple',
        'category' => 'Formal',
        'color' => '#D0D8E0',
        'description' => 'Sesi foto couple pertama kami',
        'date' => 'Februari 2023'
    ],
    [
        'id' => 8,
        'title' => 'Cooking Together',
        'category' => 'Casual',
        'color' => '#E4D8C8',
        'description' => 'Memasak bersama di dapur kecil kami',
        'date' => 'Mei 2024'
    ],
    [
        'id' => 9,
        'title' => 'Beach Walk',
        'category' => 'Travel',
        'color' => '#C8DCE0',
        'description' => 'Jalan-jalan sore di pinggir pantai',
        'date' => 'Agustus 2022'
    ],
    [
        'id' => 10,
        'title' => 'Concert Night',
        'category' => 'Adventure',
        'color' => '#D8D0E0',
        'description' => 'Malam konser yang tak terlupakan',
        'date' => 'November 2023'
    ],
    [
        'id' => 11,
        'title' => 'Rainy Day',
        'category' => 'Casual',
        'color' => '#D0D4D8',
        'description' => 'Menikmati hujan dari balik jendela kafe',
        'date' => 'Oktober 2023'
    ],
    [
        'id' => 12,
        'title' => 'New Year Eve',
        'category' => 'Romantic',
        'color' => '#E0D8C8',
        'description' => 'Menyambut tahun baru di rooftop',
        'date' => 'Desember 2023'
    ]
];
$quotesData = [
    [
        'text' => 'Cinta sejati bukan tentang kesempurnaan, tapi tentang melihat ketidaksempurnaan satu sama lain dengan cara yang sempurna.',
        'author' => 'Nanda'
    ],
    [
        'text' => 'Setiap hari bersamamu adalah lukisan baru yang ingin kuabadikan selamanya.',
        'author' => 'Felys'
    ],
    [
        'text' => 'Rumah bukan tentang tempat, tapi tentang siapa yang ada di sisimu. Dan rumahku adalah kamu.',
        'author' => 'Nanda'
    ],
    [
        'text' => 'Kamu bukan hanya cintaku, kamu adalah sahabat terbaikku, partner hidupku, dan alasan senyumku setiap pagi.',
        'author' => 'Felys'
    ],
    [
        'text' => 'Dalam setiap langkah yang kuambil, aku selalu memastikan kamu ada di sebelahku.',
        'author' => 'Nanda'
    ],
    [
        'text' => 'Cinta kita bukan dongeng, tapi lebih indah dari dongeng mana pun karena ini nyata.',
        'author' => 'Felys'
    ],
    [
        'text' => 'Aku tidak menjanjikan hidup yang mudah, tapi aku menjanjikan tangan yang selalu menggenggam.',
        'author' => 'Nanda'
    ],
    [
        'text' => 'Bersamamu, bahkan hari Senin terasa seperti hari libur.',
        'author' => 'Felys'
    ]
];
$hobbiesData = [
    [
        'title' => 'Fotografi',
        'icon' => 'camera',
        'person' => 'Nanda',
        'description' => 'Mengabadikan momen-momen berharga melalui lensa kamera.'
    ],
    [
        'title' => 'Melukis',
        'icon' => 'palette',
        'person' => 'Felys',
        'description' => 'Menuangkan emosi dan perasaan ke dalam goresan kuas.'
    ],
    [
        'title' => 'Hiking',
        'icon' => 'mountain',
        'person' => 'Nanda',
        'description' => 'Menjelajahi alam dan menaklukkan puncak-puncak gunung.'
    ],
    [
        'title' => 'Baking',
        'icon' => 'cake',
        'person' => 'Felys',
        'description' => 'Menciptakan kue-kue lezat yang menghangatkan hati.'
    ],
    [
        'title' => 'Traveling',
        'icon' => 'plane',
        'person' => 'Berdua',
        'description' => 'Menjelajahi tempat-tempat baru dan menciptakan kenangan bersama.'
    ],
    [
        'title' => 'Movie Night',
        'icon' => 'film',
        'person' => 'Berdua',
        'description' => 'Menikmati film favorit dengan popcorn dan selimut hangat.'
    ],
    [
        'title' => 'Cooking',
        'icon' => 'utensils',
        'person' => 'Berdua',
        'description' => 'Memasak bersama di dapur kecil kami setiap akhir pekan.'
    ],
    [
        'title' => 'Reading',
        'icon' => 'book',
        'person' => 'Berdua',
        'description' => 'Membaca buku sambil menikmati teh hangat di sore hari.'
    ]
];
$statsData = [
    ['value' => $daysTogether, 'label' => 'Hari Bersama', 'suffix' => '+'],
    ['value' => 16, 'label' => 'Kota Dikunjungi', 'suffix' => ''],
    ['value' => 1247, 'label' => 'Foto Bersama', 'suffix' => '+'],
    ['value' => 5, 'label' => 'Tahun Bersama', 'suffix' => '']
];
$milestonesData = [
    [
        'year' => '2020',
        'events' => [
            'Pertama kali bertemu di kafe',
            'Kencan pertama yang canggung',
            'Resmi menjalin hubungan',
            'Liburan pertama ke Bandung'
        ]
    ],
    [
        'year' => '2021',
        'events' => [
            'Anniversary pertama',
            'Adopsi kucing bernama Mochi',
            'Pindahan ke apartemen baru',
            'Merayakan tahun baru bersama'
        ]
    ],
    [
        'year' => '2022',
        'events' => [
            'Camping anniversary kedua',
            'Trip impian ke Bali',
            'Memasak dinner spesial',
            'Mengunjungi 5 kota baru'
        ]
    ],
    [
        'year' => '2023',
        'events' => [
            'Photoshoot couple pertama',
            'Konser musik pertama bersama',
            'Merayakan anniversary ketiga',
            'Memulai hobi baru bersama'
        ]
    ],
    [
        'year' => '2024',
        'events' => [
            'Anniversary keempat',
            'Renovasi kecil apartemen',
            'Road trip ke Yogyakarta',
            'Merencanakan masa depan bersama'
        ]
    ],
    [
        'year' => '2025',
        'events' => [
            'Anniversary kelima',
            'Milestone 5 tahun bersama',
            'Melanjutkan mimpi berdua',
            'Dan cerita terus berlanjut...'
        ]
    ]
];
$loveLanguages = [
    [
        'person' => 'Nanda',
        'primary' => 'Acts of Service',
        'secondary' => 'Quality Time',
        'description' => 'Nanda menunjukkan cintanya melalui tindakan nyata. Memperbaiki barang yang rusak, memasak makanan favorit, atau sekadar memastikan Felys selalu nyaman.',
        'percentage' => 85
    ],
    [
        'person' => 'Felys',
        'primary' => 'Words of Affirmation',
        'secondary' => 'Physical Touch',
        'description' => 'Felys mengungkapkan cintanya melalui kata-kata manis dan sentuhan hangat. Pujian tulus dan pelukan adalah bahasa cintanya.',
        'percentage' => 90
    ]
];
$compatibilityData = [
    ['aspect' => 'Komunikasi', 'score' => 88],
    ['aspect' => 'Kepercayaan', 'score' => 95],
    ['aspect' => 'Humor', 'score' => 82],
    ['aspect' => 'Petualangan', 'score' => 78],
    ['aspect' => 'Romantisme', 'score' => 91],
    ['aspect' => 'Kesabaran', 'score' => 85]
];
$randomQuote = $quotesData[array_rand($quotesData)];
$currentPage = basename($_SERVER['PHP_SELF']);
$navItems = [
    ['href' => '#home', 'label' => 'Beranda'],
    ['href' => '#about', 'label' => 'Tentang Kami'],
    ['href' => '#story', 'label' => 'Cerita Kami'],
    ['href' => '#gallery', 'label' => 'Galeri'],
    ['href' => '#hobbies', 'label' => 'Hobi'],
    ['href' => '#milestones', 'label' => 'Milestone'],
    ['href' => '#guestbook', 'label' => 'Buku Tamu']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portofolio pasangan Nanda dan Felys - Dua hati, satu perjalanan.">
    <meta name="theme-color" content="#2C3E2D">
    <title>NALYS | Nanda & Felys</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Inter:wght@300;400;500;600&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #FAF7F2;
            --bg-secondary: #F3EDE4;
            --bg-tertiary: #EDE5D8;
            --color-primary: #2C3E2D;
            --color-secondary: #4A6B4E;
            --color-accent: #C9A96E;
            --color-accent-light: #E5D5B8;
            --color-sage: #A8B5A0;
            --color-sage-light: #D4DED0;
            --color-text: #2D2D2D;
            --color-text-light: #6B7268;
            --color-text-muted: #9A9F96;
            --color-white: #FFFFFF;
            --color-border: #E2DAD0;
            --font-display: 'Playfair Display', serif;
            --font-body: 'Inter', sans-serif;
            --font-elegant: 'Cormorant Garamond', serif;
            --shadow-sm: 0 1px 3px rgba(44, 62, 45, 0.06);
            --shadow-md: 0 4px 12px rgba(44, 62, 45, 0.08);
            --shadow-lg: 0 8px 30px rgba(44, 62, 45, 0.12);
            --shadow-xl: 0 16px 50px rgba(44, 62, 45, 0.15);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 20px;
            --radius-xl: 30px;
            --transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            --transition-slow: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html {
            scroll-behavior: smooth;
            font-size: 16px;
        }
        body {
            font-family: var(--font-body);
            background-color: var(--bg-primary);
            color: var(--color-text);
            line-height: 1.7;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        ::selection {
            background-color: var(--color-accent);
            color: var(--color-white);
        }
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg-secondary);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--color-sage);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--color-secondary);
        }
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--color-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            transition: opacity 0.8s ease, visibility 0.8s ease;
        }
        .preloader.hidden {
            opacity: 0;
            visibility: hidden;
        }
        .preloader-content {
            text-align: center;
        }
        .preloader-logo {
            font-family: var(--font-display);
            font-size: 3rem;
            color: var(--color-accent-light);
            letter-spacing: 0.3em;
            animation: pulse 1.5s ease-in-out infinite;
        }
        .preloader-line {
            width: 60px;
            height: 2px;
            background: var(--color-accent);
            margin: 20px auto;
            animation: expandLine 1.5s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }
        @keyframes expandLine {
            0%, 100% { width: 60px; }
            50% { width: 120px; }
        }
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            padding: 20px 0;
            transition: var(--transition);
        }
        .navbar.scrolled {
            background: rgba(250, 247, 242, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 12px 0;
            box-shadow: var(--shadow-sm);
        }
        .navbar .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .nav-logo {
            font-family: var(--font-display);
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--color-white);
            text-decoration: none;
            letter-spacing: 0.15em;
            transition: var(--transition);
        }
        .navbar.scrolled .nav-logo {
            color: var(--color-primary);
        }
        .nav-logo span {
            color: var(--color-accent);
        }
        .nav-links {
            display: flex;
            list-style: none;
            gap: 32px;
            align-items: center;
        }
        .nav-links a {
            text-decoration: none;
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            transition: var(--transition);
            position: relative;
            padding: 4px 0;
        }
        .navbar.scrolled .nav-links a {
            color: var(--color-text-light);
        }
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 1.5px;
            background: var(--color-accent);
            transition: var(--transition);
        }
        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }
        .nav-links a:hover,
        .nav-links a.active {
            color: var(--color-accent);
        }
        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 5px;
            z-index: 1001;
        }
        .hamburger span {
            width: 25px;
            height: 2px;
            background: var(--color-white);
            transition: var(--transition);
        }
        .navbar.scrolled .hamburger span {
            background: var(--color-primary);
        }
        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }
        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }
        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(5px, -5px);
        }
        .mobile-menu {
            position: fixed;
            top: 0;
            right: -100%;
            width: 100%;
            height: 100vh;
            background: var(--color-primary);
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: right 0.5s cubic-bezier(0.77, 0, 0.175, 1);
        }
        .mobile-menu.active {
            right: 0;
        }
        .mobile-menu-links {
            list-style: none;
            text-align: center;
        }
        .mobile-menu-links li {
            margin: 24px 0;
        }
        .mobile-menu-links a {
            font-family: var(--font-display);
            font-size: 2rem;
            color: var(--color-white);
            text-decoration: none;
            transition: var(--transition);
            opacity: 0.8;
        }
        .mobile-menu-links a:hover {
            opacity: 1;
            color: var(--color-accent);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }
        .container-wide {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
        }
        .section {
            padding: 100px 0;
        }
        .section-alt {
            background: var(--bg-secondary);
        }
        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }
        .section-label {
            font-family: var(--font-body);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.25em;
            color: var(--color-accent);
            display: block;
            margin-bottom: 12px;
        }
        .section-title {
            font-family: var(--font-display);
            font-size: 2.8rem;
            font-weight: 600;
            color: var(--color-primary);
            line-height: 1.2;
            margin-bottom: 16px;
        }
        .section-subtitle {
            font-family: var(--font-elegant);
            font-size: 1.15rem;
            color: var(--color-text-light);
            max-width: 600px;
            margin: 0 auto;
            font-style: italic;
        }
        .section-divider {
            width: 50px;
            height: 2px;
            background: var(--color-accent);
            margin: 20px auto 0;
        }
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background: linear-gradient(160deg, var(--color-primary) 0%, #1A2A1B 40%, #2C3E2D 70%, #3A4F3B 100%);
            overflow: hidden;
        }
        .hero-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.04;
            background-image: radial-gradient(circle at 2px 2px, var(--color-white) 1px, transparent 0);
            background-size: 40px 40px;
        }
        .hero-decoration {
            position: absolute;
            border-radius: 50%;
            opacity: 0.08;
        }
        .hero-decoration-1 {
            width: 500px;
            height: 500px;
            background: var(--color-accent);
            top: -150px;
            right: -100px;
        }
        .hero-decoration-2 {
            width: 300px;
            height: 300px;
            background: var(--color-sage);
            bottom: -80px;
            left: -60px;
        }
        .hero-decoration-3 {
            width: 200px;
            height: 200px;
            border: 1px solid var(--color-accent);
            top: 20%;
            left: 10%;
            background: transparent;
        }
        .hero-content {
            text-align: center;
            z-index: 2;
            padding: 0 24px;
        }
        .hero-badge {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.35em;
            color: var(--color-accent);
            border: 1px solid rgba(201, 169, 110, 0.3);
            padding: 10px 28px;
            border-radius: 50px;
            margin-bottom: 32px;
            backdrop-filter: blur(10px);
        }
        .hero-title {
            font-family: var(--font-display);
            font-size: clamp(3.5rem, 10vw, 7rem);
            font-weight: 600;
            color: var(--color-white);
            letter-spacing: 0.2em;
            line-height: 1.1;
            margin-bottom: 8px;
        }
        .hero-title .ampersand {
            font-family: var(--font-elegant);
            font-style: italic;
            color: var(--color-accent);
            font-weight: 400;
        }
        .hero-names {
            font-family: var(--font-elegant);
            font-size: clamp(1.1rem, 2.5vw, 1.5rem);
            color: rgba(255, 255, 255, 0.7);
            letter-spacing: 0.15em;
            margin-bottom: 24px;
            font-weight: 400;
        }
        .hero-tagline {
            font-family: var(--font-elegant);
            font-size: clamp(1rem, 2vw, 1.3rem);
            color: rgba(255, 255, 255, 0.5);
            font-style: italic;
            margin-bottom: 40px;
        }
        .hero-counter {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 40px;
            margin-bottom: 48px;
        }
        .counter-item {
            text-align: center;
        }
        .counter-value {
            font-family: var(--font-display);
            font-size: 2.5rem;
            font-weight: 600;
            color: var(--color-accent);
            display: block;
        }
        .counter-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: rgba(255, 255, 255, 0.5);
        }
        .hero-scroll {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.4);
            text-decoration: none;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.2em;
        }
        .scroll-line {
            width: 1px;
            height: 50px;
            background: linear-gradient(to bottom, var(--color-accent), transparent);
            animation: scrollDown 2s ease-in-out infinite;
        }
        @keyframes scrollDown {
            0%, 100% { transform: scaleY(1); opacity: 1; }
            50% { transform: scaleY(0.6); opacity: 0.5; }
        }
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 40px;
        }
        .about-card {
            background: var(--color-white);
            border-radius: var(--radius-lg);
            padding: 48px;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }
        .about-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }
        .about-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }
        .about-card.male::before {
            background: linear-gradient(90deg, var(--color-primary), var(--color-secondary));
        }
        .about-card.female::before {
            background: linear-gradient(90deg, var(--color-accent), var(--color-accent-light));
        }
        .about-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 600;
            color: var(--color-white);
            margin-bottom: 24px;
            position: relative;
        }
        .about-card.male .about-avatar {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        }
        .about-card.female .about-avatar {
            background: linear-gradient(135deg, var(--color-accent), #D4B87A);
        }
        .about-role {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.25em;
            color: var(--color-accent);
            font-weight: 600;
            margin-bottom: 8px;
        }
        .about-name {
            font-family: var(--font-display);
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--color-primary);
            margin-bottom: 4px;
        }
        .about-birthday {
            font-size: 0.85rem;
            color: var(--color-text-muted);
            margin-bottom: 16px;
        }
        .about-bio {
            font-size: 0.92rem;
            color: var(--color-text-light);
            line-height: 1.8;
            margin-bottom: 24px;
        }
        .about-traits {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 28px;
        }
        .trait-tag {
            font-size: 0.72rem;
            padding: 6px 16px;
            border-radius: 50px;
            background: var(--bg-secondary);
            color: var(--color-text-light);
            font-weight: 500;
            letter-spacing: 0.05em;
        }
        .about-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .detail-label {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--color-text-muted);
            font-weight: 600;
        }
        .detail-value {
            font-size: 0.85rem;
            color: var(--color-text);
            font-weight: 500;
        }
        .about-quote {
            margin-top: 24px;
            padding: 20px;
            background: var(--bg-primary);
            border-radius: var(--radius-md);
            border-left: 3px solid var(--color-accent);
        }
        .about-quote p {
            font-family: var(--font-elegant);
            font-size: 1rem;
            font-style: italic;
            color: var(--color-text-light);
            line-height: 1.7;
        }
        .skills-section {
            margin-top: 24px;
        }
        .skills-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--color-text-muted);
            font-weight: 600;
            margin-bottom: 12px;
        }
        .skill-item {
            margin-bottom: 14px;
        }
        .skill-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .skill-name {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--color-text);
        }
        .skill-percent {
            font-size: 0.75rem;
            color: var(--color-accent);
            font-weight: 600;
        }
        .skill-bar {
            width: 100%;
            height: 4px;
            background: var(--bg-secondary);
            border-radius: 2px;
            overflow: hidden;
        }
        .skill-fill {
            height: 100%;
            border-radius: 2px;
            background: linear-gradient(90deg, var(--color-sage), var(--color-accent));
            transition: width 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            width: 0;
        }
        .skill-fill.animated {
            width: var(--target-width);
        }
        .couple-connection {
            text-align: center;
            margin-top: 60px;
            padding: 60px;
            background: var(--color-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }
        .connection-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--color-primary), var(--color-accent));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 2rem;
        }
        .connection-text {
            font-family: var(--font-elegant);
            font-size: 1.4rem;
            color: var(--color-text-light);
            font-style: italic;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.8;
        }
        .timeline-section {
            position: relative;
        }
        .timeline {
            position: relative;
            max-width: 900px;
            margin: 0 auto;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 2px;
            height: 100%;
            background: linear-gradient(to bottom, var(--color-accent-light), var(--color-sage-light), var(--color-accent-light));
        }
        .timeline-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 50px;
            position: relative;
        }
        .timeline-item:nth-child(odd) {
            flex-direction: row;
        }
        .timeline-item:nth-child(even) {
            flex-direction: row-reverse;
        }
        .timeline-content {
            width: calc(50% - 40px);
            background: var(--color-white);
            padding: 32px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            position: relative;
        }
        .timeline-content:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }
        .timeline-dot {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: var(--color-accent);
            border: 3px solid var(--bg-primary);
            box-shadow: 0 0 0 3px var(--color-accent-light);
            z-index: 2;
        }
        .timeline-date {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--color-accent);
            font-weight: 600;
            margin-bottom: 8px;
        }
        .timeline-title {
            font-family: var(--font-display);
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--color-primary);
            margin-bottom: 8px;
        }
        .timeline-desc {
            font-size: 0.88rem;
            color: var(--color-text-light);
            line-height: 1.7;
            margin-bottom: 12px;
        }
        .timeline-location {
            font-size: 0.75rem;
            color: var(--color-text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        .gallery-item {
            position: relative;
            border-radius: var(--radius-md);
            overflow: hidden;
            cursor: pointer;
            aspect-ratio: 3/4;
            transition: var(--transition);
        }
        .gallery-item:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
        }
        .gallery-item:nth-child(1),
        .gallery-item:nth-child(6),
        .gallery-item:nth-child(9) {
            aspect-ratio: 3/4;
        }
        .gallery-item:nth-child(3),
        .gallery-item:nth-child(8) {
            aspect-ratio: 4/3;
            grid-column: span 2;
        }
        .gallery-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: var(--transition-slow);
        }
        .gallery-item:hover .gallery-placeholder {
            transform: scale(1.05);
        }
        .gallery-icon {
            font-size: 2.5rem;
            opacity: 0.6;
        }
        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 24px;
            background: linear-gradient(to top, rgba(44, 62, 45, 0.9), transparent);
            transform: translateY(100%);
            transition: var(--transition);
        }
        .gallery-item:hover .gallery-overlay {
            transform: translateY(0);
        }
        .gallery-title {
            font-family: var(--font-display);
            font-size: 1rem;
            color: var(--color-white);
            margin-bottom: 4px;
        }
        .gallery-category {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--color-accent-light);
        }
        .gallery-filter {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 8px 24px;
            border: 1px solid var(--color-border);
            border-radius: 50px;
            background: transparent;
            font-family: var(--font-body);
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--color-text-light);
            cursor: pointer;
            transition: var(--transition);
        }
        .filter-btn:hover,
        .filter-btn.active {
            background: var(--color-primary);
            border-color: var(--color-primary);
            color: var(--color-white);
        }
        .hobbies-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }
        .hobby-card {
            background: var(--color-white);
            padding: 36px 28px;
            border-radius: var(--radius-md);
            text-align: center;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
        }
        .hobby-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-md);
        }
        .hobby-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--color-sage), var(--color-accent));
            transform: scaleX(0);
            transition: var(--transition);
        }
        .hobby-card:hover::after {
            transform: scaleX(1);
        }
        .hobby-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--bg-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 1.5rem;
        }
        .hobby-title {
            font-family: var(--font-display);
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--color-primary);
            margin-bottom: 6px;
        }
        .hobby-person {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--color-accent);
            font-weight: 600;
            margin-bottom: 10px;
        }
        .hobby-desc {
            font-size: 0.82rem;
            color: var(--color-text-light);
            line-height: 1.6;
        }
        .stats-section {
            background: linear-gradient(160deg, var(--color-primary), #1A2A1B);
            padding: 80px 0;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-value {
            font-family: var(--font-display);
            font-size: 3rem;
            font-weight: 600;
            color: var(--color-accent);
            display: block;
            margin-bottom: 8px;
        }
        .stat-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: rgba(255, 255, 255, 0.6);
        }
        .quote-section {
            padding: 100px 0;
            text-align: center;
            position: relative;
            background: var(--bg-secondary);
        }
        .quote-mark {
            font-family: var(--font-display);
            font-size: 6rem;
            color: var(--color-accent-light);
            line-height: 1;
            margin-bottom: -20px;
        }
        .quote-text {
            font-family: var(--font-elegant);
            font-size: clamp(1.3rem, 3vw, 1.8rem);
            color: var(--color-text);
            font-style: italic;
            max-width: 700px;
            margin: 0 auto 24px;
            line-height: 1.8;
        }
        .quote-author {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--color-accent);
            text-transform: uppercase;
            letter-spacing: 0.15em;
        }
        .love-language-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 60px;
        }
        .love-card {
            background: var(--color-white);
            padding: 40px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        .love-card:hover {
            box-shadow: var(--shadow-md);
        }
        .love-person {
            font-family: var(--font-display);
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--color-primary);
            margin-bottom: 16px;
        }
        .love-primary {
            display: inline-block;
            padding: 6px 18px;
            background: var(--bg-secondary);
            border-radius: 50px;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--color-secondary);
            margin-bottom: 8px;
        }
        .love-secondary {
            display: inline-block;
            padding: 6px 18px;
            background: var(--bg-primary);
            border-radius: 50px;
            font-size: 0.78rem;
            color: var(--color-text-muted);
            margin-bottom: 16px;
        }
        .love-desc {
            font-size: 0.88rem;
            color: var(--color-text-light);
            line-height: 1.7;
            margin-bottom: 20px;
        }
        .love-bar {
            width: 100%;
            height: 6px;
            background: var(--bg-secondary);
            border-radius: 3px;
            overflow: hidden;
        }
        .love-fill {
            height: 100%;
            border-radius: 3px;
            background: linear-gradient(90deg, var(--color-accent), var(--color-sage));
            transition: width 1.5s ease;
        }
        .compatibility-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }
        .compat-item {
            background: var(--color-white);
            padding: 28px;
            border-radius: var(--radius-md);
            text-align: center;
            box-shadow: var(--shadow-sm);
        }
        .compat-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 16px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .compat-value {
            font-family: var(--font-display);
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--color-primary);
        }
        .compat-label {
            font-size: 0.8rem;
            color: var(--color-text-light);
            font-weight: 500;
        }
        .milestones-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }
        .milestone-card {
            background: var(--color-white);
            padding: 36px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        .milestone-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }
        .milestone-year {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 600;
            color: var(--color-accent);
            margin-bottom: 16px;
        }
        .milestone-list {
            list-style: none;
        }
        .milestone-list li {
            font-size: 0.85rem;
            color: var(--color-text-light);
            padding: 8px 0;
            border-bottom: 1px solid var(--bg-secondary);
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .milestone-list li:last-child {
            border-bottom: none;
        }
        .milestone-list li::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--color-sage);
            flex-shrink: 0;
            margin-top: 7px;
        }
        .guestbook-section {
            background: var(--bg-secondary);
        }
        .guestbook-form {
            max-width: 600px;
            margin: 0 auto 60px;
            background: var(--color-white);
            padding: 48px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }
        .form-group {
            margin-bottom: 24px;
        }
        .form-label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--color-text-light);
            margin-bottom: 8px;
        }
        .form-input,
        .form-textarea {
            width: 100%;
            padding: 14px 18px;
            border: 1.5px solid var(--color-border);
            border-radius: var(--radius-sm);
            font-family: var(--font-body);
            font-size: 0.9rem;
            color: var(--color-text);
            background: var(--bg-primary);
            transition: var(--transition);
            outline: none;
        }
        .form-input:focus,
        .form-textarea:focus {
            border-color: var(--color-accent);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.15);
        }
        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }
        .form-honeypot {
            position: absolute;
            left: -9999px;
            opacity: 0;
            height: 0;
            width: 0;
        }
        .form-submit {
            width: 100%;
            padding: 16px;
            background: var(--color-primary);
            color: var(--color-white);
            border: none;
            border-radius: var(--radius-sm);
            font-family: var(--font-body);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            cursor: pointer;
            transition: var(--transition);
        }
        .form-submit:hover {
            background: var(--color-secondary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .form-message {
            padding: 14px 18px;
            border-radius: var(--radius-sm);
            margin-bottom: 24px;
            font-size: 0.85rem;
        }
        .form-message.success {
            background: #E8F5E9;
            color: #2E7D32;
            border: 1px solid #C8E6C9;
        }
        .form-message.error {
            background: #FBE9E7;
            color: #C62828;
            border: 1px solid #FFCCBC;
        }
        .guestbook-entries {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }
        .guest-entry {
            background: var(--color-white);
            padding: 28px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        .guest-entry:hover {
            box-shadow: var(--shadow-md);
        }
        .guest-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        .guest-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--color-white);
        }
        .guest-info {
            flex: 1;
        }
        .guest-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--color-text);
        }
        .guest-date {
            font-size: 0.72rem;
            color: var(--color-text-muted);
        }
        .guest-message {
            font-size: 0.88rem;
            color: var(--color-text-light);
            line-height: 1.7;
        }
        .footer {
            background: var(--color-primary);
            color: var(--color-white);
            padding: 80px 0 40px;
        }
        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 60px;
            margin-bottom: 60px;
        }
        .footer-brand {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 600;
            letter-spacing: 0.15em;
            margin-bottom: 16px;
        }
        .footer-brand span {
            color: var(--color-accent);
        }
        .footer-desc {
            font-size: 0.88rem;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.8;
            max-width: 350px;
        }
        .footer-title {
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: var(--color-accent);
            margin-bottom: 20px;
        }
        .footer-links {
            list-style: none;
        }
        .footer-links li {
            margin-bottom: 12px;
        }
        .footer-links a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 0.88rem;
            transition: var(--transition);
        }
        .footer-links a:hover {
            color: var(--color-accent);
        }
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .footer-copyright {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.4);
        }
        .footer-hearts {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.4);
        }
        .footer-hearts span {
            color: var(--color-accent);
        }
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--color-primary);
            color: var(--color-white);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
            opacity: 0;
            visibility: hidden;
            z-index: 100;
        }
        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }
        .back-to-top:hover {
            background: var(--color-accent);
            transform: translateY(-4px);
        }
        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .reveal-delay-1 { transition-delay: 0.1s; }
        .reveal-delay-2 { transition-delay: 0.2s; }
        .reveal-delay-3 { transition-delay: 0.3s; }
        .reveal-delay-4 { transition-delay: 0.4s; }
        @media (max-width: 1024px) {
            .gallery-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .hobbies-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .milestones-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .compatibility-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .footer-content {
                grid-template-columns: 1fr 1fr;
            }
        }
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            .hamburger {
                display: flex;
            }
            .section {
                padding: 70px 0;
            }
            .section-title {
                font-size: 2.2rem;
            }
            .about-grid {
                grid-template-columns: 1fr;
            }
            .timeline::before {
                left: 20px;
            }
            .timeline-item {
                flex-direction: row !important;
                padding-left: 50px;
            }
            .timeline-content {
                width: 100%;
            }
            .timeline-dot {
                left: 20px;
            }
            .gallery-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .gallery-item:nth-child(3),
            .gallery-item:nth-child(8) {
                grid-column: span 1;
                aspect-ratio: 3/4;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 30px;
            }
            .love-language-grid {
                grid-template-columns: 1fr;
            }
            .guestbook-entries {
                grid-template-columns: 1fr;
            }
            .milestones-grid {
                grid-template-columns: 1fr;
            }
            .hero-counter {
                gap: 24px;
            }
            .footer-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            .footer-bottom {
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }
        }
        @media (max-width: 480px) {
            .hero-title {
                letter-spacing: 0.1em;
            }
            .hobbies-grid {
                grid-template-columns: 1fr;
            }
            .gallery-grid {
                grid-template-columns: 1fr;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .compatibility-grid {
                grid-template-columns: 1fr;
            }
            .guestbook-form {
                padding: 28px;
            }
            .about-card {
                padding: 28px;
            }
            .couple-connection {
                padding: 36px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="preloader" id="preloader">
        <div class="preloader-content">
            <div class="preloader-logo">NALYS</div>
            <div class="preloader-line"></div>
        </div>
    </div>
    <nav class="navbar" id="navbar">
        <div class="container">
            <a href="#home" class="nav-logo">N<span>&</span>F</a>
            <ul class="nav-links">
                <?php foreach ($navItems as $item): ?>
                    <li><a href="<?php echo $item['href']; ?>"><?php echo $item['label']; ?></a></li>
                <?php endforeach; ?>
            </ul>
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>
    <div class="mobile-menu" id="mobileMenu">
        <ul class="mobile-menu-links">
            <?php foreach ($navItems as $item): ?>
                <li><a href="<?php echo $item['href']; ?>" class="mobile-link"><?php echo $item['label']; ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <section class="hero" id="home">
        <div class="hero-pattern"></div>
        <div class="hero-decoration hero-decoration-1"></div>
        <div class="hero-decoration hero-decoration-2"></div>
        <div class="hero-decoration hero-decoration-3"></div>
        <div class="hero-content">
            <div class="hero-badge">Est. <?php echo date('Y', strtotime($coupleData['since'])); ?></div>
            <h1 class="hero-title"><?php echo $coupleData['name']; ?></h1>
            <p class="hero-names"><?php echo $coupleData['male']['nickname']; ?> <span class="ampersand">&</span> <?php echo $coupleData['female']['nickname']; ?></p>
            <p class="hero-tagline"><?php echo $coupleData['tagline']; ?></p>
            <div class="hero-counter">
                <div class="counter-item">
                    <span class="counter-value" data-target="<?php echo $yearsTogether; ?>"><?php echo $yearsTogether; ?></span>
                    <span class="counter-label">Tahun</span>
                </div>
                <div class="counter-item">
                    <span class="counter-value" data-target="<?php echo $monthsTogether; ?>"><?php echo $monthsTogether; ?></span>
                    <span class="counter-label">Bulan</span>
                </div>
                <div class="counter-item">
                    <span class="counter-value" data-target="<?php echo $daysTogether; ?>"><?php echo $daysTogether; ?></span>
                    <span class="counter-label">Hari</span>
                </div>
            </div>
        </div>
        <a href="#about" class="hero-scroll">
            <span>Scroll</span>
            <div class="scroll-line"></div>
        </a>
    </section>
    <section class="section" id="about">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-label">Tentang Kami</span>
                <h2 class="section-title">Dua Hati, Satu Cerita</h2>
                <p class="section-subtitle">Kenali kami lebih dekat, dua pribadi yang berbeda namun saling melengkapi.</p>
                <div class="section-divider"></div>
            </div>
            <div class="about-grid">
                <div class="about-card male reveal reveal-delay-1">
                    <div class="about-avatar"><?php echo getInitials($coupleData['male']['name']); ?></div>
                    <div class="about-role"><?php echo $coupleData['male']['role']; ?></div>
                    <h3 class="about-name"><?php echo $coupleData['male']['name']; ?></h3>
                    <p class="about-birthday"><?php echo $coupleData['male']['birthday']; ?> | <?php echo $coupleData['male']['zodiac']; ?> | <?php echo $coupleData['male']['mbti']; ?></p>
                    <p class="about-bio"><?php echo $coupleData['male']['bio']; ?></p>
                    <div class="about-traits">
                        <?php foreach ($coupleData['male']['traits'] as $trait): ?>
                            <span class="trait-tag"><?php echo $trait; ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="about-details">
                        <div class="detail-item">
                            <span class="detail-label">Hobi</span>
                            <span class="detail-value"><?php echo $coupleData['male']['hobby']; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Makanan Favorit</span>
                            <span class="detail-value"><?php echo $coupleData['male']['favorite_food']; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Lagu Favorit</span>
                            <span class="detail-value"><?php echo $coupleData['male']['favorite_song']; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Film Favorit</span>
                            <span class="detail-value"><?php echo $coupleData['male']['favorite_movie']; ?></span>
                        </div>
                    </div>
                    <div class="skills-section">
                        <div class="skills-title">Kemampuan</div>
                        <?php foreach ($coupleData['male']['skills'] as $skillName => $skillValue): ?>
                            <div class="skill-item">
                                <div class="skill-header">
                                    <span class="skill-name"><?php echo $skillName; ?></span>
                                    <span class="skill-percent"><?php echo $skillValue; ?>%</span>
                                </div>
                                <div class="skill-bar">
                                    <div class="skill-fill" style="--target-width: <?php echo $skillValue; ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="about-quote">
                        <p>"<?php echo $coupleData['male']['quote']; ?>"</p>
                    </div>
                </div>
                <div class="about-card female reveal reveal-delay-2">
                    <div class="about-avatar"><?php echo getInitials($coupleData['female']['name']); ?></div>
                    <div class="about-role"><?php echo $coupleData['female']['role']; ?></div>
                    <h3 class="about-name"><?php echo $coupleData['female']['name']; ?></h3>
                    <p class="about-birthday"><?php echo $coupleData['female']['birthday']; ?> | <?php echo $coupleData['female']['zodiac']; ?> | <?php echo $coupleData['female']['mbti']; ?></p>
                    <p class="about-bio"><?php echo $coupleData['female']['bio']; ?></p>
                    <div class="about-traits">
                        <?php foreach ($coupleData['female']['traits'] as $trait): ?>
                            <span class="trait-tag"><?php echo $trait; ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="about-details">
                        <div class="detail-item">
                            <span class="detail-label">Hobi</span>
                            <span class="detail-value"><?php echo $coupleData['female']['hobby']; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Makanan Favorit</span>
                            <span class="detail-value"><?php echo $coupleData['female']['favorite_food']; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Lagu Favorit</span>
                            <span class="detail-value"><?php echo $coupleData['female']['favorite_song']; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Film Favorit</span>
                            <span class="detail-value"><?php echo $coupleData['female']['favorite_movie']; ?></span>
                        </div>
                    </div>
                    <div class="skills-section">
                        <div class="skills-title">Kemampuan</div>
                        <?php foreach ($coupleData['female']['skills'] as $skillName => $skillValue): ?>
                            <div class="skill-item">
                                <div class="skill-header">
                                    <span class="skill-name"><?php echo $skillName; ?></span>
                                    <span class="skill-percent"><?php echo $skillValue; ?>%</span>
                                </div>
                                <div class="skill-bar">
                                    <div class="skill-fill" style="--target-width: <?php echo $skillValue; ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="about-quote">
                        <p>"<?php echo $coupleData['female']['quote']; ?>"</p>
                    </div>
                </div>
            </div>
            <div class="couple-connection reveal">
                <div class="connection-icon">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                </div>
                <p class="connection-text"><?php echo $coupleData['description']; ?></p>
            </div>
        </div>
    </section>
    <section class="section section-alt" id="story">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-label">Cerita Kami</span>
                <h2 class="section-title">Perjalanan Cinta</h2>
                <p class="section-subtitle">Setiap momen adalah babak baru dalam kisah kami.</p>
                <div class="section-divider"></div>
            </div>
            <div class="timeline">
                <?php foreach ($timelineData as $index => $event): ?>
                    <div class="timeline-item reveal">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-date"><?php echo $event['date']; ?></div>
                            <h3 class="timeline-title"><?php echo $event['title']; ?></h3>
                            <p class="timeline-desc"><?php echo $event['description']; ?></p>
                            <div class="timeline-location">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                <?php echo $event['location']; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <?php foreach ($statsData as $index => $stat): ?>
                    <div class="stat-item reveal reveal-delay-<?php echo $index + 1; ?>">
                        <span class="stat-value" data-count="<?php echo $stat['value']; ?>">0<?php echo $stat['suffix']; ?></span>
                        <span class="stat-label"><?php echo $stat['label']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <section class="section" id="gallery">
        <div class="container-wide">
            <div class="section-header reveal">
                <span class="section-label">Galeri</span>
                <h2 class="section-title">Momen Berharga</h2>
                <p class="section-subtitle">Kumpulan kenangan yang kami abadikan bersama.</p>
                <div class="section-divider"></div>
            </div>
            <div class="gallery-filter reveal">
                <button class="filter-btn active" data-filter="all">Semua</button>
                <button class="filter-btn" data-filter="Romantic">Romantic</button>
                <button class="filter-btn" data-filter="Casual">Casual</button>
                <button class="filter-btn" data-filter="Travel">Travel</button>
                <button class="filter-btn" data-filter="Adventure">Adventure</button>
                <button class="filter-btn" data-filter="Formal">Formal</button>
            </div>
            <div class="gallery-grid">
                <?php foreach ($galleryData as $item): ?>
                    <div class="gallery-item reveal" data-category="<?php echo $item['category']; ?>">
                        <div class="gallery-placeholder" style="background: <?php echo $item['color']; ?>">
                            <div class="gallery-icon">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="rgba(44,62,45,0.3)" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                            </div>
                        </div>
                        <div class="gallery-overlay">
                            <h4 class="gallery-title"><?php echo $item['title']; ?></h4>
                            <span class="gallery-category"><?php echo $item['category']; ?> | <?php echo $item['date']; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <section class="quote-section">
        <div class="container reveal">
            <div class="quote-mark">"</div>
            <p class="quote-text"><?php echo $randomQuote['text']; ?></p>
            <span class="quote-author">— <?php echo $randomQuote['author']; ?></span>
        </div>
    </section>
    <section class="section section-alt" id="hobbies">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-label">Hobi & Minat</span>
                <h2 class="section-title">Hal yang Kami Cintai</h2>
                <p class="section-subtitle">Karena cinta tumbuh dari hal-hal kecil yang dilakukan bersama.</p>
                <div class="section-divider"></div>
            </div>
            <div class="hobbies-grid">
                <?php foreach ($hobbiesData as $index => $hobby): ?>
                    <div class="hobby-card reveal reveal-delay-<?php echo ($index % 4) + 1; ?>">
                        <div class="hobby-icon">
                            <?php if ($hobby['icon'] === 'camera'): ?>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                            <?php elseif ($hobby['icon'] === 'palette'): ?>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r="2"></circle><circle cx="17.5" cy="10.5" r="2"></circle><circle cx="8.5" cy="7.5" r="2"></circle><circle cx="6.5" cy="12.5" r="2"></circle><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"></path></svg>
                            <?php elseif ($hobby['icon'] === 'mountain'): ?>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m8 3 4 8 5-5 5 15H2L8 3z"></path></svg>
                            <?php elseif ($hobby['icon'] === 'cake'): ?>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-8a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8"></path><path d="M4 16s.5-1 2-1 2.5 2 4 2 2.5-2 4-2 2.5 2 4 2 2-1 2-1"></path><path d="M2 21h20"></path><path d="M7 8v3"></path><path d="M12 8v3"></path><path d="M17 8v3"></path><path d="M7 4h.01"></path><path d="M12 4h.01"></path><path d="M17 4h.01"></path></svg>
                            <?php elseif ($hobby['icon'] === 'plane'): ?>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"></path></svg>
                            <?php elseif ($hobby['icon'] === 'film'): ?>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"></rect><line x1="7" y1="2" x2="7" y2="22"></line><line x1="17" y1="2" x2="17" y2="22"></line><line x1="2" y1="12" x2="22" y2="12"></line><line x1="2" y1="7" x2="7" y2="7"></line><line x1="2" y1="17" x2="7" y2="17"></line><line x1="17" y1="17" x2="22" y2="17"></line><line x1="17" y1="7" x2="22" y2="7"></line></svg>
                            <?php elseif ($hobby['icon'] === 'utensils'): ?>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"></path><path d="M7 2v20"></path><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"></path></svg>
                            <?php elseif ($hobby['icon'] === 'book'): ?>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path></svg>
                            <?php endif; ?>
                        </div>
                        <h3 class="hobby-title"><?php echo $hobby['title']; ?></h3>
                        <div class="hobby-person"><?php echo $hobby['person']; ?></div>
                        <p class="hobby-desc"><?php echo $hobby['description']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-label">Love Language</span>
                <h2 class="section-title">Bahasa Cinta Kami</h2>
                <p class="section-subtitle">Bagaimana kami mengekspresikan dan menerima cinta.</p>
                <div class="section-divider"></div>
            </div>
            <div class="love-language-grid">
                <?php foreach ($loveLanguages as $love): ?>
                    <div class="love-card reveal">
                        <h3 class="love-person"><?php echo $love['person']; ?></h3>
                        <div class="love-primary"><?php echo $love['primary']; ?></div>
                        <div class="love-secondary"><?php echo $love['secondary']; ?></div>
                        <p class="love-desc"><?php echo $love['description']; ?></p>
                        <div class="love-bar">
                            <div class="love-fill" style="width: <?php echo $love['percentage']; ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="section-header reveal" style="margin-top: 60px;">
                <span class="section-label">Compatibility</span>
                <h2 class="section-title" style="font-size: 2rem;">Kecocokan Kami</h2>
            </div>
            <div class="compatibility-grid">
                <?php foreach ($compatibilityData as $compat): ?>
                    <div class="compat-item reveal">
                        <div class="compat-circle" style="background: conic-gradient(var(--color-accent) <?php echo $compat['score'] * 3.6; ?>deg, var(--bg-secondary) 0deg);">
                            <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--color-white); display: flex; align-items: center; justify-content: center;">
                                <span class="compat-value"><?php echo $compat['score']; ?>%</span>
                            </div>
                        </div>
                        <div class="compat-label"><?php echo $compat['aspect']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <section class="section section-alt" id="milestones">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-label">Milestone</span>
                <h2 class="section-title">Jejak Langkah Kami</h2>
                <p class="section-subtitle">Ringkasan perjalanan kami dari tahun ke tahun.</p>
                <div class="section-divider"></div>
            </div>
            <div class="milestones-grid">
                <?php foreach ($milestonesData as $index => $milestone): ?>
                    <div class="milestone-card reveal reveal-delay-<?php echo ($index % 3) + 1; ?>">
                        <div class="milestone-year"><?php echo $milestone['year']; ?></div>
                        <ul class="milestone-list">
                            <?php foreach ($milestone['events'] as $event): ?>
                                <li><?php echo $event; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <section class="section guestbook-section" id="guestbook">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-label">Buku Tamu</span>
                <h2 class="section-title">Tinggalkan Pesan</h2>
                <p class="section-subtitle">Tulis doa, harapan, atau sekadar sapaan untuk kami.</p>
                <div class="section-divider"></div>
            </div>
            <div class="guestbook-form reveal">
                <?php if ($formSuccess): ?>
                    <div class="form-message success">Terima kasih! Pesan Anda telah berhasil dikirim.</div>
                <?php endif; ?>
                <?php if (!empty($formError)): ?>
                    <div class="form-message error"><?php echo $formError; ?></div>
                <?php endif; ?>
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="guestbookForm">
                    <div class="form-honeypot">
                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="guest_name">Nama</label>
                        <input type="text" id="guest_name" name="guest_name" class="form-input" placeholder="Nama Anda" required value="<?php echo isset($_POST['guest_name']) ? sanitize($_POST['guest_name']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="guest_email">Email (Opsional)</label>
                        <input type="email" id="guest_email" name="guest_email" class="form-input" placeholder="email@contoh.com" value="<?php echo isset($_POST['guest_email']) ? sanitize($_POST['guest_email']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="guest_message">Pesan</label>
                        <textarea id="guest_message" name="guest_message" class="form-textarea" placeholder="Tulis pesan manis untuk Nanda & Felys..." required><?php echo isset($_POST['guest_message']) ? sanitize($_POST['guest_message']) : ''; ?></textarea>
                    </div>
                    <button type="submit" name="guestbook_submit" class="form-submit">Kirim Pesan</button>
                </form>
            </div>
            <?php if (!empty($guestbook)): ?>
                <div class="guestbook-entries">
                    <?php foreach (array_slice($guestbook, 0, 6) as $entry): ?>
                        <div class="guest-entry reveal">
                            <div class="guest-header">
                                <div class="guest-avatar" style="background: <?php echo getAvatarColor($entry['name']); ?>">
                                    <?php echo getInitials($entry['name']); ?>
                                </div>
                                <div class="guest-info">
                                    <div class="guest-name"><?php echo $entry['name']; ?></div>
                                    <div class="guest-date"><?php echo isset($entry['timestamp']) ? timeAgo($entry['timestamp']) : $entry['date']; ?></div>
                                </div>
                            </div>
                            <p class="guest-message"><?php echo $entry['message']; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div>
                    <div class="footer-brand">N<span>&</span>F</div>
                    <p class="footer-desc"><?php echo $coupleData['description']; ?> Bersama sejak <?php echo $coupleData['since']; ?>, dan akan terus berlanjut selamanya.</p>
                </div>
                <div>
                    <h4 class="footer-title">Navigasi</h4>
                    <ul class="footer-links">
                        <?php foreach ($navItems as $item): ?>
                            <li><a href="<?php echo $item['href']; ?>"><?php echo $item['label']; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-title">Info</h4>
                    <ul class="footer-links">
                        <li><a href="#home">Beranda</a></li>
                        <li><a href="#about">Tentang Kami</a></li>
                        <li><a href="#story">Cerita Kami</a></li>
                        <li><a href="#guestbook">Buku Tamu</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="footer-copyright">&copy; <?php echo date('Y'); ?> NALYS. Semua hak dilindungi.</div>
                <div class="footer-hearts">Dibuat dengan <span>&#10084;</span> untuk Nanda & Felys</div>
            </div>
        </div>
    </footer>
    <button class="back-to-top" id="backToTop" aria-label="Kembali ke atas">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="18 15 12 9 6 15"></polyline>
        </svg>
    </button>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const preloader = document.getElementById('preloader');
            window.addEventListener('load', function() {
                setTimeout(function() {
                    preloader.classList.add('hidden');
                }, 1500);
            });
            setTimeout(function() {
                preloader.classList.add('hidden');
            }, 3000);
            const navbar = document.getElementById('navbar');
            const backToTop = document.getElementById('backToTop');
            const hamburger = document.getElementById('hamburger');
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileLinks = document.querySelectorAll('.mobile-link');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 80) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
                if (window.scrollY > 500) {
                    backToTop.classList.add('visible');
                } else {
                    backToTop.classList.remove('visible');
                }
                updateActiveNav();
            });
            hamburger.addEventListener('click', function() {
                hamburger.classList.toggle('active');
                mobileMenu.classList.toggle('active');
                document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
            });
            mobileLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    hamburger.classList.remove('active');
                    mobileMenu.classList.remove('active');
                    document.body.style.overflow = '';
                });
            });
            backToTop.addEventListener('click', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
            function updateActiveNav() {
                const sections = document.querySelectorAll('section[id]');
                const navLinks = document.querySelectorAll('.nav-links a');
                let current = '';
                sections.forEach(function(section) {
                    const sectionTop = section.offsetTop - 100;
                    if (window.scrollY >= sectionTop) {
                        current = section.getAttribute('id');
                    }
                });
                navLinks.forEach(function(link) {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + current) {
                        link.classList.add('active');
                    }
                });
            }
            const revealElements = document.querySelectorAll('.reveal');
            const revealObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });
            revealElements.forEach(function(el) {
                revealObserver.observe(el);
            });
            const skillFills = document.querySelectorAll('.skill-fill');
            const skillObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animated');
                        skillObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });
            skillFills.forEach(function(fill) {
                skillObserver.observe(fill);
            });
            const statValues = document.querySelectorAll('.stat-value');
            const statObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const el = entry.target;
                        const target = parseInt(el.getAttribute('data-count'));
                        const suffix = el.textContent.includes('+') ? '+' : '';
                        animateCounter(el, target, suffix);
                        statObserver.unobserve(el);
                    }
                });
            }, { threshold: 0.5 });
            statValues.forEach(function(stat) {
                statObserver.observe(stat);
            });
            function animateCounter(element, target, suffix) {
                let current = 0;
                const increment = target / 60;
                const timer = setInterval(function() {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    element.textContent = Math.floor(current).toLocaleString('id-ID') + suffix;
                }, 30);
            }
            const filterBtns = document.querySelectorAll('.filter-btn');
            const galleryItems = document.querySelectorAll('.gallery-item');
            filterBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    filterBtns.forEach(function(b) { b.classList.remove('active'); });
                    btn.classList.add('active');
                    const filter = btn.getAttribute('data-filter');
                    galleryItems.forEach(function(item) {
                        const category = item.getAttribute('data-category');
                        if (filter === 'all' || category === filter) {
                            item.style.display = 'block';
                            setTimeout(function() {
                                item.style.opacity = '1';
                                item.style.transform = 'translateY(0)';
                            }, 50);
                        } else {
                            item.style.opacity = '0';
                            item.style.transform = 'translateY(20px)';
                            setTimeout(function() {
                                item.style.display = 'none';
                            }, 400);
                        }
                    });
                });
            });
            const guestbookForm = document.getElementById('guestbookForm');
            if (guestbookForm) {
                guestbookForm.addEventListener('submit', function(e) {
                    const name = document.getElementById('guest_name').value.trim();
                    const message = document.getElementById('guest_message').value.trim();
                    if (name.length < 2) {
                        e.preventDefault();
                        alert('Nama terlalu pendek.');
                        return;
                    }
                    if (message.length < 10) {
                        e.preventDefault();
                        alert('Pesan terlalu pendek, minimal 10 karakter.');
                        return;
                    }
                });
            }
            document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        const offsetTop = target.offsetTop - 80;
                        window.scrollTo({
                            top: offsetTop,
                            behavior: 'smooth'
                        });
                    }
                });
            });
            let lastScrollY = window.scrollY;
            window.addEventListener('scroll', function() {
                lastScrollY = window.scrollY;
            });
            const heroDecorations = document.querySelectorAll('.hero-decoration');
            window.addEventListener('mousemove', function(e) {
                const x = e.clientX / window.innerWidth;
                const y = e.clientY / window.innerHeight;
                heroDecorations.forEach(function(dec, index) {
                    const speed = (index + 1) * 10;
                    dec.style.transform = 'translate(' + (x * speed) + 'px, ' + (y * speed) + 'px)';
                });
            });
        });
    </script>
</body>
</html>