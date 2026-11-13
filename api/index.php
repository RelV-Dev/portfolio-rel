<?php
require_once __DIR__ . '/../config.php';
// Unknown paths must not be indexed as copies of the homepage.
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (!in_array($requestPath, ['/', '/api/index.php'], true)) {
    http_response_code(404);
    header('X-Robots-Tag: noindex');
    echo '<!doctype html><html lang="id"><meta charset="utf-8"><title>Halaman tidak ditemukan | RelV</title><h1>Halaman tidak ditemukan</h1><a href="/">Kembali ke portofolio</a></html>';
    exit;
}


require_once __DIR__ . '/../lib/public-data.php';
header('Cache-Control: no-store');
$gallery_result = portfolio_gallery();
$gallery_failed = $gallery_result === null;
$gallery = $gallery_result ?? [];

$projects = [];
$awards = [];
$certificates = [];

foreach ($gallery as $item) {
    if (!$item['is_visible']) {
        continue;
    }
    if ($item['category'] === 'projects') {
        $projects[] = $item;
    } elseif ($item['category'] === 'awards') {
        $awards[] = $item;
    } elseif ($item['category'] === 'certificates') {
        $certificates[] = $item;
    }
}

$person_awards = [];
foreach ($awards as $item) {
    $person_awards[] = $item['title'];
}
// Prestasi non-DB (tidak ada di tabel portfolio_gallery) ditambahkan manual
$person_awards[] = "Google Student Ambassador (Oktober 2026 - Present)";
if (empty($person_awards)) {
    $person_awards = ["National and International Achievements in Technology"];
}

function getPlaceholderColor($icon_type) {
    switch ($icon_type) {
        case 'trophy':
            return ['color' => 'var(--secondary)', 'glow' => 'rgba(188, 19, 254, 0.5)', 'bg' => 'rgba(188, 19, 254, 0.1)', 'icon' => 'fas fa-trophy'];
        case 'award':
            return ['color' => 'var(--primary)', 'glow' => 'rgba(0, 243, 255, 0.5)', 'bg' => 'rgba(0, 243, 255, 0.1)', 'icon' => 'fas fa-award'];
        case 'medal':
            return ['color' => 'var(--tertiary)', 'glow' => 'rgba(0, 255, 157, 0.5)', 'bg' => 'rgba(0, 255, 157, 0.1)', 'icon' => 'fas fa-medal'];
        case 'certificate':
        default:
            return ['color' => 'var(--primary)', 'glow' => 'rgba(0, 243, 255, 0.5)', 'bg' => 'rgba(0, 243, 255, 0.1)', 'icon' => 'fas fa-certificate'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <!-- Basic Meta -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Farel Putra Firmansyah | Portofolio Developer &amp; Desainer</title>

  <meta name="description" content="Portofolio Farel Putra Firmansyah: proyek web dan IoT, desain visual, prestasi, serta perjalanan pendidikan. Jelajahi karya dan hubungi untuk kolaborasi.">
  <meta name="keywords" content="Farel Putra Firmansyah, Google Student Ambassador, student developer Indonesia, Farel SMA 3 Pati, IEEE Competition 2025, FLS3N Poster Pati, IONIC PENS 2025, KRENOVA Pati, IASC Informatika, SEA SMAGA, KIR SMAGA, OSIS SMAN 3 Pati, student research club">
  <meta name="author" content="Farel Putra Firmansyah">
  <meta name="robots" content="index, follow, max-image-preview:large">
  <meta property="og:locale" content="id_ID">
  <meta property="og:site_name" content="RelV — Farel Putra Firmansyah">
  <meta property="og:image:alt" content="Portofolio Farel Putra Firmansyah">
  <meta name="twitter:image:alt" content="Portofolio Farel Putra Firmansyah">

  <link rel="canonical" href="https://relv.biz.id/">

  <link rel="preconnect" href="https://raw.githubusercontent.com" crossorigin>
  <link rel="preconnect" href="https://data-id-card.vercel.app" crossorigin>
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>

  <link rel="icon" href="https://res.cloudinary.com/dvpq5fsef/image/upload/v1780841882/a7ymm1qzpmj4dfjo0opu.png">
  <link rel="apple-touch-icon" sizes="180x180" href="https://res.cloudinary.com/dvpq5fsef/image/upload/v1780841884/vfduze9bhb5zap8au4bh.png">

  <!-- Open Graph (SEO + Beasiswa) -->
  <meta property="og:title" content="Farel Putra Firmansyah | Portofolio Developer &amp; Desainer">
  <meta property="og:description" content="Portofolio Farel Putra Firmansyah: proyek web dan IoT, desain visual, prestasi, serta perjalanan pendidikan. Jelajahi karya dan hubungi untuk kolaborasi.">
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://relv.biz.id/">
  <meta property="og:image" content="https://res.cloudinary.com/dvpq5fsef/image/upload/v1780841885/x3esj1090dipra4braiq.png">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Farel Putra Firmansyah | Portofolio Developer &amp; Desainer">
  <meta name="twitter:description" content="Portofolio Farel Putra Firmansyah: proyek web dan IoT, desain visual, prestasi, serta perjalanan pendidikan. Jelajahi karya dan hubungi untuk kolaborasi.">
  <meta name="twitter:image" content="https://res.cloudinary.com/dvpq5fsef/image/upload/v1780841885/x3esj1090dipra4braiq.png">

  <!-- SEO DB -->
  <script type="application/ld+json">
    <?php
    $personId = 'https://relv.biz.id/#person';
    $schema = ['@context'=>'https://schema.org', '@graph'=>[
      ['@type'=>'Person', '@id'=>$personId, 'name'=>'Farel Putra Firmansyah', 'alternateName'=>'RelV',
       'url'=>'https://relv.biz.id/', 'image'=>'https://res.cloudinary.com/dvpq5fsef/image/upload/c_fill,g_auto,w_400,h_400,q_auto,f_auto/v1780844527/dvpyazydsd46jzrvu6bq.webp',
       'sameAs'=>['https://github.com/RelV-Dev','https://www.instagram.com/zx_frl'], 'award'=>$person_awards],
      ['@type'=>'WebSite','@id'=>'https://relv.biz.id/#website','url'=>'https://relv.biz.id/','name'=>'RelV — Portofolio Farel Putra Firmansyah','inLanguage'=>'id-ID','publisher'=>['@id'=>$personId]],
      ['@type'=>'ProfilePage','@id'=>'https://relv.biz.id/#webpage','url'=>'https://relv.biz.id/', 'name'=>'Portofolio Farel Putra Firmansyah', 'inLanguage'=>'id-ID',
       'isPartOf'=>['@id'=>'https://relv.biz.id/#website'], 'mainEntity'=>['@id'=>$personId]]
    ]];
    foreach ($projects as $item) {
      $work = ['@type'=>'CreativeWork', '@id'=>'https://relv.biz.id/#work-'.(int)$item['id'],
        'url'=>'https://relv.biz.id/#work-'.(int)$item['id'], 'name'=>$item['title'],
        'description'=>$item['description'], 'creator'=>['@id'=>$personId]];
      if (public_url($item['image_url'] ?? '')) $work['image']=public_url($item['image_url']);
      $schema['@graph'][]=$work;
    }
    echo json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE);
    ?>
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">

  <link rel="stylesheet" href="/assets/icons/icons.css">

  <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet" media="print" onload="this.media='all'">



  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2" defer></script>















  <style>
    /* --- RESET & VARIABLES (UPGRADED DESIGN SYSTEM — originalitas tetap) --- */
    :root {
      --primary: #00f3ff;
      --secondary: #bc13fe;
      --tertiary: #00ff9d;
      --firefly: #ccff00;
      --bg-dark: #050505;
      --bg-dark-2: #0a0a0f;
      --bg-card: rgba(20, 20, 25, 0.62);
      --bg-card-hover: rgba(24, 24, 33, 0.78);
      --glass-border: rgba(255, 255, 255, 0.08);
      --glass-border-strong: rgba(255, 255, 255, 0.12);
      --glass-shine: rgba(255, 255, 255, 0.03);
      --text-main: #ffffff;
      --text-muted: #a8adb7;
      --text-faint: #7a7f8a;
      --font-main: 'Outfit', sans-serif;
      --font-mono: 'Space Grotesk', monospace;
      --ease-smooth: cubic-bezier(0.16, 1, 0.3, 1);
      --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
      --ease-out-expo: cubic-bezier(0.16, 1, 0.3, 1);
      --radius-lg: 24px;
      --radius-md: 16px;
      --shadow-soft: 0 10px 40px rgba(0,0,0,0.35);
      --shadow-glow: 0 0 28px rgba(0,243,255,0.18);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      -webkit-font-smoothing: antialiased;
    }

    /* Custom cursor only on non-touch devices */
    @media (hover: hover) and (pointer: fine) {
      * { cursor: none !important; }
    }

    ::-webkit-scrollbar {
      width: 8px;
    }

    ::-webkit-scrollbar-track {
      background: var(--bg-dark);
    }

    ::-webkit-scrollbar-thumb {
      background: var(--glass-border);
      border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: var(--primary);
    }

    html {
      scroll-behavior: smooth;
      scrollbar-gutter: stable;
    }

    /* Skip link for accessibility */
    .skip-link {
      position: absolute;
      left: -9999px;
      top: 12px;
      background: #fff;
      color: #000;
      padding: 10px 16px;
      border-radius: 999px;
      font-weight: 700;
      z-index: 100000;
    }
    .skip-link:focus { left: 12px; }

    /* Scroll progress bar */
    #scrollProgress {
      position: fixed;
      top: 0; left: 0;
      width: 0%;
      height: 2px;
      background: linear-gradient(90deg, var(--primary), var(--secondary), var(--tertiary));
      z-index: 10002;
      transform-origin: left;
      opacity: 0.95;
      pointer-events: none;
    }

    /* Subtle page grain + vignette — premium but not noisy */
    body::after {
      content: '';
    }

    body {
      font-family: var(--font-main);
      background:
        radial-gradient(900px 600px at 15% 10%, rgba(188,19,254,0.09), transparent 60%),
        radial-gradient(1000px 700px at 85% 85%, rgba(0,243,255,0.07), transparent 60%),
        var(--bg-dark);
      color: var(--text-main);
      overflow-x: hidden;
      position: relative;
      line-height: 1.6;
    }

    /* Class untuk mematikan scroll saat animasi opening */
    body.no-scroll {
      overflow: hidden;
      height: 100vh;
    }

    /* --- 1. OPENING ANIMATION (PREMIUM WITH PARTICLES) --- */
    #preloader {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100vh;
      background: #000;
      z-index: 99999;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      transition: opacity 0.6s cubic-bezier(0.85, 0, 0.15, 1), transform 0.6s cubic-bezier(0.85, 0, 0.15, 1);
      overflow: hidden;
    }

    #preloader.slide-up {
      opacity: 0;
      transform: scale(1.05);
      pointer-events: none;
    }

    .loader-content {
      position: relative;
      font-family: var(--font-mono);
      font-size: 3rem;
      font-weight: 800;
      color: #fff;
      letter-spacing: 5px;
      text-transform: uppercase;
      overflow: hidden;
      z-index: 2;
    }

    .loader-text {
      position: relative;
      display: inline-block;
      clip-path: inset(0 100% 0 0);
      animation: textSwipeAnim 0.6s cubic-bezier(0.77, 0, 0.175, 1) forwards 0.2s;
    }

    .loader-text::before,
    .loader-text::after {
      content: attr(data-text);
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: #000;
    }

    .loader-text::before {
      left: 2px;
      text-shadow: -1px 0 var(--primary);
      animation: glitch-anim-1 0.8s infinite linear alternate-reverse;
      opacity: 0.6;
    }

    .loader-text::after {
      left: -2px;
      text-shadow: 1px 0 #bc13fe;
      animation: glitch-anim-2 1s infinite linear alternate-reverse;
      opacity: 0.6;
    }

    .loader-bar-container {
      position: relative;
      width: 200px;
      height: 2px;
      margin-top: 24px;
      background: rgba(255,255,255,0.06);
      border-radius: 2px;
      overflow: hidden;
      z-index: 2;
    }

    .loader-bar {
      width: 0;
      height: 100%;
      background: linear-gradient(90deg, var(--primary), #bc13fe, var(--primary));
      background-size: 200% 100%;
      border-radius: 2px;
      box-shadow: 0 0 12px var(--primary), 0 0 24px rgba(0, 243, 255, 0.3);
      transition: width 0.3s ease-out;
      animation: loaderBarShimmer 1.5s linear infinite;
    }

    .loader-percent {
      font-family: var(--font-mono);
      font-size: 0.75rem;
      color: rgba(255,255,255,0.4);
      margin-top: 12px;
      letter-spacing: 3px;
      z-index: 2;
    }

    /* Preloader Particles */
    .preloader-particles {
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      pointer-events: none;
      z-index: 1;
    }

    .preloader-particle {
      position: absolute;
      width: 2px;
      height: 2px;
      background: var(--primary);
      border-radius: 50%;
      opacity: 0;
      animation: particleFloat 3s ease-in-out infinite;
    }

    .preloader-particle:nth-child(odd) {
      background: #bc13fe;
    }

    @keyframes particleFloat {
      0% { opacity: 0; transform: translateY(100vh) scale(0); }
      30% { opacity: 0.8; }
      100% { opacity: 0; transform: translateY(-20vh) scale(1.5); }
    }

    @keyframes loaderBarShimmer {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }

    /* Keyframes Opening */
    @keyframes textSwipeAnim {
      0% {
        clip-path: inset(0 100% 0 0);
        transform: translateX(-20px);
      }
      100% {
        clip-path: inset(0 0 0 0);
        transform: translateX(0);
      }
    }

    @keyframes glitch-anim-1 {
      0% { clip-path: inset(20% 0 80% 0); }
      100% { clip-path: inset(30% 0 20% 0); }
    }

    @keyframes glitch-anim-2 {
      0% { clip-path: inset(10% 0 60% 0); }
      100% { clip-path: inset(0% 0 80% 0); }
    }

    /* --- BACKGROUND ELEMENTS --- */
    .cursor-bulb {
      position: fixed;
      top: 0;
      left: 0;
      width: 500px;
      height: 500px;
      background: conic-gradient(from 0deg,
          var(--primary) 0deg, transparent 60deg,
          var(--secondary) 120deg, transparent 180deg,
          var(--tertiary) 240deg, transparent 300deg,
          var(--primary) 360deg);
      border-radius: 50%;
      pointer-events: none;
      z-index: 9999;
      transform: translate(-50%, -50%);
      mix-blend-mode: screen;
      filter: blur(50px);
      opacity: 0.15;
      animation: rotateFlower 10s linear infinite;
    }

    @keyframes rotateFlower {
      0% {
        transform: translate(-50%, -50%) rotate(0deg);
      }

      100% {
        transform: translate(-50%, -50%) rotate(360deg);
      }
    }

    .cursor-dot {
      position: fixed;
      top: 0;
      left: 0;
      width: 8px;
      height: 8px;
      background: #fff;
      border-radius: 50%;
      pointer-events: none;
      z-index: 10000;
      transform: translate(-50%, -50%);
      mix-blend-mode: difference;
      transition: width 0.3s, height 0.3s, background 0.3s, border 0.3s, transform 0.1s;
    }

    .cursor-dot.active-hover {
      width: 50px;
      height: 50px;
      background: transparent;
      border: 2px solid var(--primary);
      box-shadow: 0 0 15px var(--primary);
      mix-blend-mode: normal;
      animation: pulseCursor 1.5s infinite;
    }

    .cursor-dot.active-hover::after,
    .cursor-dot.active-hover::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      background: var(--primary);
      transform: translate(-50%, -50%);
    }

    .cursor-dot.active-hover::after {
      width: 2px;
      height: 10px;
    }

    .cursor-dot.active-hover::before {
      width: 10px;
      height: 2px;
    }

    @keyframes pulseCursor {
      0% {
        transform: translate(-50%, -50%) scale(1) rotate(0deg);
        opacity: 1;
      }

      50% {
        transform: translate(-50%, -50%) scale(1.1) rotate(45deg);
        opacity: 0.8;
      }

      100% {
        transform: translate(-50%, -50%) scale(1) rotate(90deg);
        opacity: 1;
      }
    }

    body::before,
    body::after {
      content: '';
      position: fixed;
      width: 600px;
      height: 600px;
      border-radius: 50%;
      filter: blur(100px);
      z-index: -1;
      opacity: 0.15;
      animation: floatOrb 10s infinite alternate;
    }

    body::before {
      background: var(--secondary);
      top: -100px;
      left: -100px;
    }

    body::after {
      background: var(--primary);
      bottom: -100px;
      right: -100px;
      animation-delay: -5s;
    }

    @keyframes floatOrb {
      0% {
        transform: translate(0, 0) scale(1);
      }

      100% {
        transform: translate(50px, 50px) scale(1.1);
      }
    }

    /* --- NAVIGATION --- */
    nav {
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 1000;
      width: 90%;
      max-width: 1000px;
      padding: 12px 30px;
      background: rgba(10, 10, 12, 0.65);
      backdrop-filter: blur(16px) saturate(180%);
      -webkit-backdrop-filter: blur(16px) saturate(180%);
      border: 1px solid var(--glass-border);
      border-radius: 100px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
      transition: all 0.4s ease;
    }

    nav:hover {
      border-color: rgba(255, 255, 255, 0.2);
      box-shadow: 0 15px 50px rgba(0, 243, 255, 0.1);
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 1.5rem;
      font-weight: 800;
      font-family: var(--font-mono);
      color: #fff;
      text-decoration: none;
      letter-spacing: -0.5px;
    }

    .logo i {
      color: var(--primary);
      text-shadow: 0 0 10px var(--primary);
    }

    .nav-links {
      display: flex;
      gap: 35px;
      list-style: none;
    }

    .nav-links a {
      color: var(--text-muted);
      text-decoration: none;
      font-weight: 500;
      font-size: 0.95rem;
      position: relative;
      transition: color 0.3s ease;
    }

    .nav-links a:hover,
    .nav-links a.active {
      color: #fff;
    }

    .nav-links a::after {
      content: '';
      position: absolute;
      bottom: -4px;
      left: 50%;
      transform: translateX(-50%) scaleX(0);
      width: 20px;
      height: 2px;
      background: var(--primary);
      box-shadow: 0 0 8px var(--primary);
      border-radius: 2px;
      transition: transform 0.3s ease;
    }

    .nav-links a:hover::after {
      transform: translateX(-50%) scaleX(1);
    }

    .menu-toggle {
      display: none;
      flex-direction: column;
      gap: 6px;
      cursor: pointer;
      padding: 5px;
      z-index: 1001;
    }

    .menu-toggle span {
      width: 24px;
      height: 2px;
      background: #fff;
      border-radius: 2px;
      transition: all 0.3s cubic-bezier(0.68, -0.6, 0.32, 1.6);
    }

    /* --- HERO SECTION --- */
    .hero {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 120px 5% 80px;
      position: relative;
      overflow: hidden;
    }

    .hero-content {
      display: flex;
      align-items: center;
      justify-content: space-between;
      max-width: 1200px;
      width: 100%;
      gap: 50px;
    }

    .hero-text {
      flex: 1;
      z-index: 2;
    }

    .hero-greeting {
      font-family: var(--font-mono);
      color: var(--primary);
      font-size: clamp(0.85rem, 1.6vw, 1.05rem);
      margin-bottom: 14px;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      letter-spacing: 0.24em;
      text-transform: uppercase;
      opacity: 0.95;
    }
    .hero-greeting::before{
      content:'';
      width: 28px; height: 2px;
      background: linear-gradient(90deg, var(--primary), transparent);
      border-radius: 2px;
      opacity: 0.9;
    }

    .hero-text h1 {
      font-size: clamp(2.4rem, 5.2vw, 4.6rem);
      font-weight: 800;
      line-height: 0.95;
      margin-bottom: 14px;
      background: linear-gradient(135deg, #fff 0%, #c9d1e1 55%, #a8adb7 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      letter-spacing: -0.035em;
      text-wrap: balance;
    }

    .typing-container {
      font-size: clamp(1.35rem, 2.2vw, 2rem);
      font-weight: 600;
      color: var(--secondary);
      margin-bottom: 22px;
      min-height: 40px;
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 4px;
    }

    .typing-text {
      display: inline;
    }

    .typing-char {
      display: inline-block;
      opacity: 0;
      transform: translateY(8px) scale(0.8);
      animation: charReveal 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
      background: linear-gradient(135deg, var(--primary), #bc13fe);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .typing-char.deleting {
      animation: charDelete 0.15s cubic-bezier(0.55, 0.085, 0.68, 0.53) forwards;
    }

    @keyframes charReveal {
      0% { opacity: 0; transform: translateY(8px) scale(0.8); filter: blur(4px); }
      100% { opacity: 1; transform: translateY(0) scale(1); filter: blur(0); }
    }

    @keyframes charDelete {
      0% { opacity: 1; transform: translateY(0) scale(1); filter: blur(0); }
      100% { opacity: 0; transform: translateY(-8px) scale(0.6); filter: blur(4px); }
    }

    .cursor {
      display: inline-block;
      width: 3px;
      height: 30px;
      background: linear-gradient(180deg, var(--primary), #bc13fe);
      margin-left: 5px;
      animation: cursorBlink 1s ease-in-out infinite;
      border-radius: 2px;
      box-shadow: 0 0 8px rgba(0, 243, 255, 0.5);
    }

    @keyframes cursorBlink {
      0%, 100% { opacity: 1; box-shadow: 0 0 8px rgba(0, 243, 255, 0.5); }
      50% { opacity: 0.2; box-shadow: 0 0 2px rgba(0, 243, 255, 0.2); }
    }

    .text-reveal-anim {
      color: rgba(255, 255, 255, 0.1);
      filter: blur(5px);
      transition: all 1s cubic-bezier(0.16, 1, 0.3, 1);
      transform: translateY(10px);
    }

    .text-reveal-anim.revealed {
      color: var(--text-muted);
      filter: blur(0);
      transform: translateY(0);
    }

    .hero-description {
      font-size: clamp(1rem, 1.35vw, 1.08rem);
      margin-bottom: 32px;
      max-width: 560px;
      line-height: 1.75;
      color: #c9ced8;
      text-wrap: pretty;
    }

    .social-links {
      display: flex;
      gap: 14px;
      flex-wrap: wrap;
    }

    .social-btn {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--glass-border);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      text-decoration: none;
      transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      position: relative;
      overflow: hidden;
    }

    .social-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      opacity: 0;
      transition: opacity 0.3s ease;
      z-index: -1;
    }

    .social-btn:hover {
      transform: translateY(-5px);
      border-color: transparent;
    }

    .social-btn:hover::before {
      opacity: 1;
    }

    .hero-image-container {
      flex: 1;
      display: flex;
      justify-content: center;
      position: relative;
    }

    .image-wrapper {
      position: relative;
      width: min(400px, 78vw);
      height: min(400px, 78vw);
      z-index: 1;
      transition: transform 0.7s var(--ease-smooth);
    }

    .profile-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 30px;
      mask-image: linear-gradient(to bottom, black 82%, transparent 100%);
      -webkit-mask-image: linear-gradient(to bottom, black 82%, transparent 100%);
      position: relative;
      z-index: 2;
      transform: rotate(-3deg);
      transition: transform 0.6s var(--ease-smooth), filter 0.6s var(--ease-smooth), border-color 0.6s;
      border: 1px solid rgba(255, 255, 255, 0.14);
      box-shadow: 0 18px 50px rgba(0,0,0,0.45);
    }

    .image-wrapper:hover .profile-img {
      transform: rotate(0deg) scale(1.02);
      border-color: rgba(0,243,255,0.22);
    }

    .image-glow {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 100%;
      height: 100%;
      background: radial-gradient(circle, rgba(0, 243, 255, 0.2) 0%, rgba(188, 19, 254, 0.2) 100%);
      filter: blur(60px);
      z-index: 0;
      border-radius: 50%;
      animation: pulseGlow 5s infinite alternate;
    }

    section {
      padding: 100px 5%;
    }

    .section-header {
      text-align: center;
      margin-bottom: 70px;
    }

    .section-title {
      font-size: clamp(1.9rem, 3.4vw, 3rem);
      font-weight: 800;
      display: inline-block;
      background: linear-gradient(to right, #fff 15%, #c9ced8 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      position: relative;
      padding-bottom: 16px;
      letter-spacing: -0.02em;
      text-wrap: balance;
    }

    .section-title::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 60px;
      height: 4px;
      background: linear-gradient(90deg, var(--primary), var(--secondary));
      border-radius: 4px;
    }

    /* --- 3. EDUCATION (STICKY JOURNEY) REVISED --- */
    #education {
      /* UPDATE: Diperpanjang menjadi 500vh agar scroll terasa lebih lambat dan panjang */
      height: 500vh;
      padding: 0;
      position: relative;
    }

    .education-sticky-wrapper {
      position: sticky;
      top: 0;
      height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      overflow: hidden;
      padding: 0 5%;
    }

    .education-container {
      position: relative;
      max-width: 1200px;
      width: 100%;
      margin: 0 auto;
    }

    .journey-line {
      position: absolute;
      top: 30px;
      left: 0;
      width: 100%;
      height: 2px;
      background-image: linear-gradient(90deg, rgba(255, 255, 255, 0.2) 50%, transparent 50%);
      background-size: 20px 100%;
      z-index: 0;
    }

    .journey-line-progress {
      position: absolute;
      top: 30px;
      left: 0;
      width: 0%;
      height: 3px;
      background: linear-gradient(90deg, var(--primary), var(--secondary));
      z-index: 0;
      box-shadow: 0 0 10px var(--primary);
      /* UPDATE: Hapus transisi CSS agar JS Handle full smooth (Lerp) atau gunakan linear minimal */
      /* Kita akan handle via JS requestAnimationFrame untuk ultra smooth */
    }

    .journey-firefly-container {
      position: absolute;
      right: -10px;
      top: 50%;
      transform: translateY(-50%);
      z-index: 5;
      width: 20px;
      height: 20px;
    }

    .firefly-glow {
      width: 12px;
      height: 12px;
      background: var(--firefly);
      border-radius: 50%;
      box-shadow: 0 0 15px var(--firefly), 0 0 30px var(--primary);
      animation: fireflyMeander 2s ease-in-out infinite alternate, fireflyPulse 1s ease-in-out infinite alternate;
    }

    @keyframes fireflyMeander {
      0% {
        transform: translate(-5px, -10px);
      }

      50% {
        transform: translate(0px, 5px);
      }

      100% {
        transform: translate(5px, -10px);
      }
    }

    @keyframes fireflyPulse {
      0% {
        opacity: 0.8;
        transform: scale(0.9);
      }

      100% {
        opacity: 1;
        transform: scale(1.2);
      }
    }

    .journey-items {
      display: flex;
      justify-content: space-between;
      position: relative;
      z-index: 1;
    }

    .journey-item {
      flex: 1;
      text-align: center;
      padding: 0 15px;
      position: relative;
      opacity: 0.2;
      transform: translateY(20px);
      transition: all 0.5s ease;
      filter: blur(2px);
    }

    .journey-item.visible {
      opacity: 1;
      transform: translateY(0);
      filter: blur(0);
    }

    .journey-icon {
      width: 60px;
      height: 60px;
      background: var(--bg-dark);
      border: 2px solid var(--glass-border);
      border-radius: 50%;
      margin: 0 auto 35px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: var(--text-muted);
      transition: all 0.3s ease;
      position: relative;
      z-index: 2;
    }

    .journey-item.active .journey-icon {
      border-color: var(--primary);
      color: var(--primary);
      background: rgba(0, 243, 255, 0.1);
      box-shadow: 0 0 20px rgba(0, 243, 255, 0.4);
      transform: scale(1.1);
    }

    .journey-card {
      background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02));
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border: 1px solid var(--glass-border);
      padding: 20px;
      border-radius: 16px;
      text-align: left;
      transition: transform 0.35s var(--ease-smooth), border-color 0.35s var(--ease-smooth), box-shadow 0.35s var(--ease-smooth);
      cursor: pointer;
      box-shadow: 0 8px 32px rgba(0,0,0,0.24);
    }
    .journey-card:hover {
      transform: translateY(-6px);
      border-color: rgba(0,243,255,0.22);
      box-shadow: 0 16px 40px rgba(0,0,0,0.35), 0 0 20px rgba(0,243,255,0.12);
    }

    .journey-card h3 {
      font-size: 1.1rem;
      color: #fff;
      margin-bottom: 5px;
    }

    .journey-year {
      font-family: var(--font-mono);
      color: var(--secondary);
      font-size: 0.85rem;
      margin-bottom: 10px;
      display: block;
    }

    .journey-card p {
      line-height: 1.5;
    }

    /* --- JOURNEY MODAL --- */
    .journey-modal {
      position: fixed;
      inset: 0;
      z-index: 10000;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      transition: opacity 0.3s ease;
    }

    .journey-modal[hidden] {
      display: none !important;
    }

    .journey-modal-overlay {
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(10px);
    }

    .journey-modal-card {
      background: rgba(10, 10, 15, 0.85);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 24px;
      padding: 30px;
      max-width: 600px;
      width: 100%;
      position: relative;
      z-index: 1;
      max-height: 85vh;
      overflow-y: auto;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
      animation: modalFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes modalFadeIn {
      from { opacity: 0; transform: scale(0.95) translateY(10px); }
      to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .journey-modal-close {
      position: absolute;
      top: 20px;
      right: 20px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--glass-border);
      color: #fff;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .journey-modal-close:hover {
      background: var(--danger);
      border-color: transparent;
      transform: rotate(90deg);
    }

    .journey-modal-header {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .journey-modal-icon-wrapper {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: rgba(0, 243, 255, 0.1);
      border: 1px solid var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
      color: var(--primary);
    }

    .journey-modal-year {
      font-family: var(--font-mono);
      color: var(--secondary);
      font-size: 0.85rem;
      display: block;
      margin-bottom: 2px;
    }

    .journey-modal-header h3 {
      font-size: 1.25rem;
      color: #fff;
    }

    .journey-modal-section {
      margin-bottom: 20px;
    }

    .journey-modal-section h4 {
      font-size: 0.95rem;
      color: #fff;
      margin-bottom: 12px;
      font-family: var(--font-mono);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .journey-modal-section h4 i {
      color: var(--primary);
    }

    .journey-modal-section ul {
      list-style: none;
      padding-left: 0;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .journey-modal-section li {
      font-size: 0.9rem;
      line-height: 1.6;
      color: var(--text-muted);
      position: relative;
      padding-left: 20px;
    }

    .journey-modal-section li::before {
      content: "✦";
      position: absolute;
      left: 0;
      color: var(--secondary);
    }

    .journey-modal-gallery {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
      margin-top: 10px;
    }

    .journey-modal-gallery-item {
      position: relative;
      border-radius: 12px;
      overflow: hidden;
      aspect-ratio: 16/10;
      border: 1px solid var(--glass-border);
      cursor: zoom-in;
      transition: all 0.3s ease;
    }

    .journey-modal-gallery-item:hover {
      transform: translateY(-3px);
      border-color: var(--primary);
      box-shadow: 0 10px 20px rgba(0, 243, 255, 0.2);
    }

    .journey-modal-gallery-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    /* Placeholder untuk bukti yang gambarnya belum di-upload */
    .journey-modal-gallery-item.cert-placeholder {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 8px;
      cursor: default;
      background: linear-gradient(135deg, rgba(5, 5, 5, 0.9), rgba(0, 243, 255, 0.08));
    }

    .journey-modal-gallery-item.cert-placeholder i {
      font-size: 2rem;
      color: var(--primary);
      filter: drop-shadow(0 0 12px rgba(0, 243, 255, 0.5));
    }

    .journey-modal-gallery-item-title {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      padding: 8px;
      background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
      color: #fff;
      font-size: 0.75rem;
      text-align: center;
      font-family: var(--font-mono);
    }

    @media (max-width: 600px) {
      .journey-modal-gallery {
        grid-template-columns: 1fr;
      }
      .journey-modal-card {
        padding: 20px;
      }
    }

    /* --- SKILLS --- */
    .skills-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .skill-category {
      background: rgba(20, 20, 25, 0.4);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 24px;
      padding: 35px;
      transition: all 0.4s ease;
      position: relative;
      overflow: hidden;
    }

    .skill-category::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(90deg, var(--primary), transparent);
      opacity: 0.5;
    }

    .skill-category:hover {
      border-color: rgba(255, 255, 255, 0.1);
      transform: translateY(-6px);
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255,255,255,0.04) inset;
    }

    .skill-header {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 30px;
    }

    .skill-header i {
      font-size: 1.8rem;
      color: var(--primary);
    }

    .skill-header h3 {
      font-size: 1.4rem;
      margin: 0;
    }

    .skill-icons {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }

    .tech-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
      width: 70px;
      transition: transform 0.3s ease;
    }

    .tech-item:hover {
      transform: translateY(-4px);
    }

    .tech-icon-box {
      width: 50px;
      height: 50px;
      background: rgba(255, 255, 255, 0.03);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      border: 1px solid transparent;
      transition: all 0.3s ease;
    }

    .tech-item:hover .tech-icon-box {
      background: rgba(255, 255, 255, 0.08);
      border-color: var(--primary);
      box-shadow: 0 0 15px rgba(0, 243, 255, 0.2);
    }

    .tech-name {
      font-size: 0.8rem;
      color: var(--text-muted);
      text-align: center;
    }

    .fa-js {
      color: #f7df1e;
    }

    .fa-python {
      color: #3776ab;
    }

    .fa-react {
      color: #61dafb;
    }

    .fa-php {
      color: #777bb4;
    }

    .custom-icon {
      font-weight: bold;
      font-family: var(--font-mono);
      font-size: 1rem !important;
    }

    /* --- GITHUB ACTIVITY SECTION (NEW 3D FEATURE) --- */
    .github-activity-section {
      padding: 50px 5%;
      background: var(--bg-dark);
    }


    .github-card-container {
      max-width: 1200px;
      margin: 0 auto;
      height: 450px;
      /* Sedikit lebih tinggi */
      background: rgba(20, 20, 25, 0.6);
      /* Lebih gelap dikit biar teks kebaca */
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 24px;
      position: relative;
      overflow: hidden;
      display: flex;
      align-items: center;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
    }

    .github-stats-info {
      width: 35%;
      min-width: 300px;
      padding: 36px;
      z-index: 10;
      background: linear-gradient(90deg, rgba(5, 5, 5, 0.96) 0%, rgba(5, 5, 5, 0.55) 65%, transparent 100%);
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      border-right: 1px solid rgba(255,255,255,0.06);
    }

    .github-stats-info h3 {
      font-size: 1.8rem;
      margin-bottom: 5px;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .github-username {
      font-family: var(--font-mono);
      color: var(--secondary);
      margin-bottom: 25px;
      font-size: 0.9rem;
    }

    .stat-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      padding-bottom: 5px;
    }

    .stat-label {
      color: var(--text-muted);
      font-size: 0.9rem;
    }

    .stat-value {
      color: var(--primary);
      font-family: var(--font-mono);
      font-weight: bold;
    }

    .stat-value.highlight {
      color: var(--firefly);
      text-shadow: 0 0 10px rgba(204, 255, 0, 0.3);
    }

    #github-3d-canvas {
      position: absolute;
      /* Tetap absolute */
      top: 0;
      right: 0;
      width: 65% !important;
      /* Sisa ruangan (100% - 35%) */
      height: 100% !important;
      outline: none;
      touch-action: none;
      z-index: 1;
    }

     @media (max-width: 900px) {
      .github-card-container {
        flex-direction: column;
        height: auto;
      }
      .github-stats-info {
        width: 100%;
        min-width: 0;
        background: rgba(5, 5, 5, 0.86);
        padding: 28px;
        border-right: none;
        border-bottom: 1px solid rgba(255,255,255,0.06);
      }
      #github-3d-canvas {
        position: relative;
        width: 100% !important;
        height: 380px !important;
      }
    }

    /* --- AI ASSISTANT CHAT WIDGET (ULTIMATE VERSION) --- */
    .chat-widget-container {
      position: fixed;
      bottom: 30px;
      right: 30px;
      z-index: 100000;
      font-family: var(--font-main);
      display: flex;
      flex-direction: column;
      align-items: flex-end;

      /* Hidden during Intro */
      opacity: 0;
      transform: translateY(20px);
      pointer-events: none;
      transition: all 0.6s cubic-bezier(0.22, 1, 0.36, 1);
    }

    .chat-widget-container.show-widget {
      opacity: 1;
      transform: translateY(0);
      pointer-events: all;
    }

    /* Toggle Button (Stronger Glass) */
    .chat-toggle-btn {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      /* Background lebih transparan agar blur terlihat */
      background: rgba(15, 15, 25, 0.6);
      /* Blur lebih kuat + saturasi agar warna di belakang lebih pop */
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: var(--primary);
      font-size: 1.6rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      z-index: 100002;
    }

    .chat-toggle-btn:hover {
      transform: scale(1.1);
      box-shadow: 0 15px 40px rgba(0, 243, 255, 0.3);
      color: #fff;
      border-color: var(--primary);
    }

    /* Chat Window (Stronger Glass) */
    .chat-window {
      position: absolute;
      bottom: 80px;
      right: 0;
      width: 380px;
      height: 550px;
      /* Background transparan gelap */
      background: rgba(20, 20, 30, 0.7);
      /* Blur sangat kuat dan saturasi tinggi */
      backdrop-filter: blur(50px) saturate(200%);
      -webkit-backdrop-filter: blur(50px) saturate(200%);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 24px;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      transform-origin: bottom right;

      /* State Closed */
      opacity: 0;
      transform: scale(0.8) translateY(20px);
      pointer-events: none;
      visibility: hidden;

      transition:
        opacity 0.3s ease,
        transform 0.4s cubic-bezier(0.16, 1, 0.3, 1),
        visibility 0.3s;

      box-shadow: 0 30px 70px rgba(0, 0, 0, 0.6);
      z-index: 100001;
    }

    .chat-window.active {
      opacity: 1;
      transform: scale(1) translateY(0);
      pointer-events: all;
      visibility: visible;
    }

    /* Header */
    .chat-header {
      padding: 18px 24px;
      background: linear-gradient(90deg, rgba(255, 255, 255, 0.05), transparent);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header-controls {
      display: flex;
      gap: 15px;
      align-items: center;
    }

    .chat-title {
      display: flex;
      flex-direction: column;
      line-height: 1.2;
    }

    .chat-name {
      font-weight: 700;
      color: #fff;
      font-size: 1.05rem;
      letter-spacing: 0.5px;
    }

    .chat-status {
      font-size: 0.75rem;
      color: var(--text-muted);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .status-dot {
      width: 8px;
      height: 8px;
      background: #555;
      border-radius: 50%;
      transition: background 0.3s;
    }

    .status-dot.online {
      background: var(--tertiary);
      box-shadow: 0 0 10px var(--tertiary);
      animation: pulseStatus 2s infinite;
    }

    @keyframes pulseStatus {
      0% {
        opacity: 0.6;
        transform: scale(0.9);
      }

      50% {
        opacity: 1;
        transform: scale(1.1);
      }

      100% {
        opacity: 0.6;
        transform: scale(0.9);
      }
    }

    .header-btn {
      background: none;
      border: none;
      color: var(--text-muted);
      cursor: pointer;
      font-size: 1.1rem;
      transition: all 0.3s;
      padding: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .header-btn:hover {
      color: #fff;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }

    .header-btn.close-chat:hover {
      transform: rotate(90deg);
      color: #ff4757;
    }

    .header-btn.clear-chat:hover {
      color: var(--primary);
    }

    /* Messages Area */
    .chat-messages {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 20px;
      scroll-behavior: smooth;
      background: rgba(0, 0, 0, 0.2);
      /* Sedikit gelap di area pesan */
    }

    .chat-messages::-webkit-scrollbar {
      width: 4px;
    }

    .chat-messages::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.15);
      border-radius: 4px;
    }

    /* Bubbles */
    .message {
      max-width: 85%;
      font-size: 0.95rem;
      line-height: 1.6;
      position: relative;
      animation: popInMsg 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes popInMsg {
      from {
        opacity: 0;
        transform: scale(0.9) translateY(10px);
      }

      to {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
    }

    .message.user {
      align-self: flex-end;
      padding: 12px 18px;
      background: linear-gradient(135deg, var(--primary), #00c6ff);
      color: #000;
      font-weight: 600;
      border-radius: 18px 18px 4px 18px;
      box-shadow: 0 5px 20px rgba(0, 243, 255, 0.2);
    }

    .message.bot {
      align-self: flex-start;
      display: flex;
      gap: 12px;
      max-width: 95%;
    }

    /* FIX: Profile Picture Bulat Sempurna */
    .bot-avatar {
      width: 40px;
      /* Sedikit lebih besar */
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      /* KUNCI AGAR TIDAK GEPENG */
      border: 2px solid var(--secondary);
      flex-shrink: 0;
      /* Agar tidak tergencet flexbox */
      box-shadow: 0 0 15px rgba(188, 19, 254, 0.3);
    }

    .bot-text-content {
      background: rgba(255, 255, 255, 0.07);
      border: 1px solid rgba(255, 255, 255, 0.1);
      padding: 16px;
      border-radius: 18px 18px 18px 4px;
      color: #eee;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(5px);
    }

    /* --- NEW & UPGRADED PRETTIER STYLES --- */
    .bot-text-content strong {
      color: var(--primary);
      font-weight: 800;
    }

    /* Heading Style (# text) */
    .md-heading {
      display: block;
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--secondary);
      margin: 15px 0 8px 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      padding-bottom: 5px;
    }

    .md-heading:first-child {
      margin-top: 0;
    }

    /* Standard Text Link (in paragraph) */
    .md-link {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
      border-bottom: 1px dotted var(--primary);
      transition: all 0.2s;
    }

    .md-link:hover {
      background: rgba(0, 243, 255, 0.1);
      border-bottom-style: solid;
    }

    /* "Visit Project" Chip/Button Style */
    .md-link-chip {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(0, 243, 255, 0.1);
      border: 1px solid rgba(0, 243, 255, 0.4);
      padding: 4px 12px;
      border-radius: 50px;
      color: var(--primary);
      text-decoration: none;
      font-size: 0.8rem;
      font-weight: 600;
      margin: 2px 4px;
      transition: all 0.3s ease;
      vertical-align: middle;
      /* Agar sejajar dengan teks */
      cursor: pointer;
    }

    .md-link-chip::before {
      content: "";
      display: inline-block;
      width: 22px;
      height: 22px;
      background-image: url("https://res.cloudinary.com/dvpq5fsef/image/upload/v1780841885/bi6seszykkbxktobo2gz.gif");
      background-size: contain;
      background-repeat: no-repeat;
      background-position: center;
      margin-right: 6px;
      vertical-align: middle;
    }

    .md-link-chip:hover {
      background: var(--primary);
      color: #000;
      box-shadow: 0 0 15px rgba(0, 243, 255, 0.5);
      transform: translateY(-2px);
    }

    /* Icon khusus link */
    .md-link-chip i,
    .md-link-chip::before {
      font-style: normal;
      font-size: 0.9em;
    }

    /* Email & Phone specific colors (Optional) */
    .md-link-chip.email {
      border-color: #ffaa00;
      background: rgba(255, 170, 0, 0.1);
      color: #ffaa00;
    }

    .md-link-chip.email:hover {
      background: #ffaa00;
      color: #000;
    }

    .md-link-chip.phone {
      border-color: #00ff9d;
      background: rgba(0, 255, 157, 0.1);
      color: #00ff9d;
    }

    .md-link-chip.phone:hover {
      background: #00ff9d;
      color: #000;
    }

    /* Lists & Paragraphs */
    .md-list-item {
      display: flex;
      gap: 8px;
      margin-bottom: 8px;
      line-height: 1.5;
    }

    .md-bullet {
      color: var(--tertiary);
      font-weight: bold;
      flex-shrink: 0;
      margin-top: 1px;
    }

    .md-paragraph {
      margin-bottom: 12px;
      display: block;
    }

    .md-paragraph:last-child {
      margin-bottom: 0;
    }

    /* Input Area (Stronger Glass) */
    .chat-input-area {
      padding: 15px 20px;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(10px);
      border-top: 1px solid rgba(255, 255, 255, 0.15);
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .chat-input {
      flex: 1;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 50px;
      padding: 14px 22px;
      color: #fff;
      font-family: var(--font-main);
      font-size: 0.95rem;
      outline: none;
      transition: 0.3s;
    }

    .chat-input:focus {
      border-color: var(--primary);
      background: rgba(255, 255, 255, 0.12);
      box-shadow: 0 0 20px rgba(0, 243, 255, 0.15);
    }

    .chat-send-btn {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      border: none;
      color: #fff;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s;
      box-shadow: 0 5px 15px rgba(188, 19, 254, 0.4);
      position: relative;
      overflow: hidden;
      font-size: 1.2rem;
    }

    .chat-send-btn:hover {
      transform: scale(1.1) rotate(-10deg);
    }

    /* FLYING ANIMATION */
    .chat-send-btn.flying i {
      animation: paperPlaneFly 0.8s ease-in-out forwards;
    }

    @keyframes paperPlaneFly {
      0% {
        transform: translate(0, 0) scale(1);
        opacity: 1;
      }

      20% {
        transform: translate(-5px, 5px) scale(0.8);
      }

      50% {
        opacity: 1;
      }

      100% {
        transform: translate(60px, -60px) scale(0);
        opacity: 0;
      }
    }

    /* Typing Indicator */
    .typing-indicator {
      margin-left: 58px;
      margin-bottom: 15px;
      padding: 12px 18px;
      background: rgba(255, 255, 255, 0.08);
      border-radius: 20px;
      display: none;
      width: fit-content;
      gap: 6px;
    }

    .typing-indicator.active {
      display: flex;
    }

    .typing-dot {
      width: 8px;
      height: 8px;
      background: var(--text-muted);
      border-radius: 50%;
      animation: bounce 1.4s infinite ease-in-out both;
    }

    .typing-dot:nth-child(1) {
      animation-delay: -0.32s;
    }

    .typing-dot:nth-child(2) {
      animation-delay: -0.16s;
    }

    @keyframes bounce {

      0%,
      80%,
      100% {
        transform: scale(0);
        opacity: 0.5;
      }

      40% {
        transform: scale(1);
        opacity: 1;
      }
    }

    @media (max-width: 480px) {
      .chat-window {
        width: 94vw;
        height: 82vh;
        bottom: 85px;
        right: 0;
      }

      .chat-widget-container {
        right: 3vw;
        bottom: 20px;
      }
    }

    /* --- GALLERY --- */
    .gallery-nav {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-bottom: 50px;
      flex-wrap: wrap;
    }

    .filter-btn {
      padding: 10px 22px;
      background: transparent;
      border: 1px solid var(--glass-border);
      color: var(--text-muted);
      border-radius: 999px;
      cursor: pointer;
      font-family: var(--font-main);
      font-weight: 600;
      transition: all 0.28s var(--ease-smooth);
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
    }
    .filter-btn:hover {
      color: #fff;
      border-color: var(--glass-border-strong);
      background: rgba(255,255,255,0.06);
    }

    .gallery-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 30px;
      max-width: 1300px;
      margin: 0 auto;
    }

    .gallery-card {
      background: var(--bg-card);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      overflow: hidden;
      transition: all 0.4s ease;
      position: relative;
      opacity: 1;
      transform: scale(1);
    }

    .gallery-card.hide {
      display: none;
    }

    .gallery-card.anim-out {
      opacity: 0;
      transform: scale(0.9);
    }

    .gallery-card.anim-in {
      animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) backwards;
    }

    @keyframes popIn {
      from {
        opacity: 0;
        transform: scale(0.9);
      }

      to {
        opacity: 1;
        transform: scale(1);
      }
    }

    .gallery-card:hover {
      transform: translateY(-10px);
      border-color: var(--tertiary);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
    }

    .card-img {
      position: relative;
      height: 220px;
      overflow: hidden;
    }

    .card-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.6s var(--ease-smooth);
    }
    .gallery-card:hover .card-img img {
      transform: scale(1.06);
    }

    .card-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.3s ease;
      gap: 15px;
    }

    .gallery-card:hover .card-overlay {
      opacity: 1;
    }

    .overlay-btn {
      width: 45px;
      height: 45px;
      background: #fff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #000;
      text-decoration: none;
      transform: translateY(20px);
      transition: all 0.3s ease;
    }

    .gallery-card:hover .overlay-btn {
      transform: translateY(0);
    }

    .card-content {
      padding: 25px;
    }

    .card-category {
      font-size: 0.8rem;
      color: var(--primary);
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 10px;
      display: block;
    }

    .card-content h3 {
      font-size: 1.3rem;
      margin-bottom: 10px;
      color: #fff;
    }

    .card-content p {
      color: var(--text-muted);
      font-size: 0.9rem;
      margin-bottom: 0;
    }

    /* --- FOOTER --- */
    footer {
      background: rgba(5, 5, 8, 0.95);
      border-top: 1px solid var(--glass-border);
      padding: 60px 5% 30px;
      text-align: center;
    }

    .footer-logo {
      font-size: 2rem;
      font-weight: 800;
      font-family: var(--font-mono);
      color: #fff;
      margin-bottom: 20px;
      display: inline-block;
    }

    .footer-logo span {
      color: var(--primary);
    }

    .footer-info {
      color: var(--text-muted);
      max-width: 600px;
      margin: 0 auto 40px;
    }

    .copyright {
      color: rgba(255, 255, 255, 0.3);
      font-size: 0.9rem;
      padding-top: 30px;
      border-top: 1px solid rgba(255, 255, 255, 0.05);
    }

    /* --- RESPONSIVE --- */
    @media (max-width: 992px) {
      .hero-content {
        flex-direction: column-reverse;
        text-align: center;
        gap: 40px;
      }

      .hero-text h1 {
        font-size: 3.5rem;
      }

      .typing-container {
        justify-content: center;
      }

      .social-links {
        justify-content: center;
      }

      .hero-description {
        margin: 0 auto 35px;
      }

      /* FIX: MOBILE JOURNEY OVERLAP & LAYOUT */
      #education {
        height: auto !important;
        /* Mobile normal flow */
        padding-bottom: 50px;
      }

      .education-sticky-wrapper {
        position: relative;
        height: auto;
        top: auto;
        overflow: visible;
        display: block;
      }

      .journey-items {
        flex-direction: column;
        gap: 50px;
        padding-top: 50px;
      }

      .journey-line {
        width: 2px;
        height: 100%;
        left: 30px;
        top: 0;
        transform: none;
        background-image: linear-gradient(to bottom, rgba(255, 255, 255, 0.2) 50%, transparent 50%);
        background-size: 100% 20px;
      }

      .journey-line-progress {
        width: 3px;
        height: 0%;
        left: 30px;
        top: 0;
        transform: none;
      }

      .journey-firefly-container {
        right: auto;
        left: 50%;
        top: 100%;
        transform: translate(-50%, -50%);
      }

      .firefly-glow {
        animation: fireflyMeanderMobile 2s ease-in-out infinite alternate, fireflyPulse 1s ease-in-out infinite alternate;
      }

      @keyframes fireflyMeanderMobile {
        0% {
          transform: translate(-10px, -5px);
        }

        50% {
          transform: translate(10px, 0px);
        }

        100% {
          transform: translate(-10px, 5px);
        }
      }

      .journey-item {
        display: block;
        position: relative;
        text-align: left;
        padding-left: 75px;
        opacity: 1;
        /* Pastikan terlihat */
        transform: none;
        filter: none;
      }

      .journey-icon {
        position: absolute;
        left: 0;
        top: 0;
        margin: 0;
      }
    }

    @media (max-width: 768px) {
      nav {
        padding: 12px 20px;
      }

      .menu-toggle {
        display: flex;
        order: 2;
        margin-left: auto;
      }

      .logo {
        order: 1;
      }

      .nav-links {
        position: absolute;
        top: 80px;
        right: 0;
        width: 100%;
        background: rgba(10, 10, 15, 0.95);
        backdrop-filter: blur(20px);
        flex-direction: column;
        padding: 30px;
        gap: 20px;
        border-radius: 20px;
        border: 1px solid var(--glass-border);
        transform: scaleY(0);
        transform-origin: top;
        transition: transform 0.5s cubic-bezier(0.68, -0.6, 0.32, 1.6), opacity 0.3s ease;
        opacity: 0;
        pointer-events: none;
      }

      .nav-links.active {
        transform: scaleY(1);
        opacity: 1;
        pointer-events: all;
      }

      .nav-links li {
        opacity: 0;
        transform: translateY(-20px);
        filter: blur(10px);
        transition: all 0.5s ease;
      }

      .nav-links.active li:nth-child(1) {
        transition-delay: 0.1s;
        opacity: 1;
        transform: translateY(0);
        filter: blur(0);
      }

      .nav-links.active li:nth-child(2) {
        transition-delay: 0.2s;
        opacity: 1;
        transform: translateY(0);
        filter: blur(0);
      }

      .nav-links.active li:nth-child(3) {
        transition-delay: 0.3s;
        opacity: 1;
        transform: translateY(0);
        filter: blur(0);
      }

      .nav-links.active li:nth-child(4) {
        transition-delay: 0.4s;
        opacity: 1;
        transform: translateY(0);
        filter: blur(0);
      }

      .nav-links.active li:nth-child(5) {
        transition-delay: 0.5s;
        opacity: 1;
        transform: translateY(0);
        filter: blur(0);
      }

      .hero-text h1 {
        font-size: 2.5rem;
      }

      .typing-container {
        font-size: 1.5rem;
      }

      .image-wrapper {
        width: 280px;
        height: 280px;
      }

      .section-title {
        font-size: 2.2rem;
      }
    }

    @keyframes pulseGlow {
      0% {
        opacity: 0.5;
        transform: translate(-50%, -50%) scale(1);
      }

      100% {
        opacity: 0.8;
        transform: translate(-50%, -50%) scale(1.1);
      }
    }

    @keyframes blink {

      0%,
      100% {
        opacity: 1;
      }

      50% {
        opacity: 0;
      }
    }

    /* --- PAGINATION --- */
    .pagination-controls {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 6px;
      margin-top: 50px;
      flex-wrap: nowrap;
      padding: 0 10px;
    }

    .pagination-btn {
      min-width: 38px;
      height: 38px;
      border-radius: 10px;
      background: rgba(255, 255, 255, 0.04);
      backdrop-filter: blur(10px);
      border: 1px solid var(--glass-border);
      color: var(--text-muted);
      font-family: var(--font-mono);
      font-size: 0.85rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      cursor: pointer;
      flex-shrink: 0;
      padding: 0 4px;
    }

    .pagination-btn:hover:not(.disabled):not(.active) {
      background: rgba(255, 255, 255, 0.1);
      border-color: rgba(255, 255, 255, 0.2);
      color: #fff;
      transform: translateY(-2px);
    }

    .pagination-btn.active {
      background: var(--primary);
      color: #000;
      border-color: transparent;
      box-shadow: 0 0 20px rgba(0, 243, 255, 0.4);
      font-weight: 700;
    }

    .pagination-btn.disabled {
      opacity: 0.3;
      pointer-events: none;
    }

    .pagination-btn.nav-arrow {
      min-width: 40px;
      width: 40px;
      font-size: 0.9rem;
      background: rgba(0, 243, 255, 0.08);
      border-color: rgba(0, 243, 255, 0.2);
    }

    .pagination-btn.nav-arrow:hover:not(.disabled) {
      background: rgba(0, 243, 255, 0.15);
      border-color: rgba(0, 243, 255, 0.4);
      color: var(--primary);
    }

    .pagination-info {
      font-family: var(--font-mono);
      font-size: 0.75rem;
      color: var(--text-muted);
      margin: 0 4px;
      white-space: nowrap;
      opacity: 0.7;
    }

    /* Mobile pagination compact */
    @media (max-width: 480px) {
      .pagination-controls {
        gap: 4px;
        padding: 0 5px;
      }
      .pagination-btn {
        min-width: 34px;
        height: 34px;
        font-size: 0.8rem;
        border-radius: 8px;
      }
      .pagination-btn.nav-arrow {
        min-width: 36px;
        width: 36px;
      }
    }

    /* --- SKELETON LOADING --- */
    .skeleton {
      background: linear-gradient(90deg, rgba(255,255,255,0.04) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.04) 75%);
      background-size: 200% 100%;
      animation: skeletonShimmer 1.5s ease-in-out infinite;
      border-radius: 12px;
    }

    @keyframes skeletonShimmer {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }

    .skeleton-card {
      height: 280px;
      border-radius: 16px;
      border: 1px solid rgba(255,255,255,0.05);
    }

    .skeleton-text {
      height: 14px;
      border-radius: 6px;
      margin-bottom: 8px;
    }

    .skeleton-text.short { width: 60%; }
    .skeleton-text.medium { width: 80%; }
    .skeleton-text.long { width: 95%; }

    .skeleton-avatar {
      width: 48px;
      height: 48px;
      border-radius: 50%;
    }

    .skeleton-testimonial {
      display: flex;
      flex-direction: column;
      gap: 12px;
      padding: 24px;
      background: rgba(255,255,255,0.02);
      border: 1px solid rgba(255,255,255,0.05);
      border-radius: 16px;
    }

    /* --- TESTIMONIAL SECTION --- */
    .testimonial-section {
      padding: 100px 5%;
      position: relative;
      overflow: hidden;
    }

    .testimonial-section::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 600px;
      height: 600px;
      background: radial-gradient(circle, rgba(188, 19, 254, 0.08) 0%, transparent 70%);
      pointer-events: none;
    }

    .testimonials-grid-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px 0 10px;
    }

    .testimonials-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
      margin-bottom: 40px;
    }

    @media (max-width: 992px) {
      .testimonials-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }
    }

    .testimonial-card {
      background: linear-gradient(180deg, rgba(24,24,33,0.82), rgba(16,16,22,0.62));
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 22px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      height: 100%;
      position: relative;
      opacity: 0;
      animation: cardFadeSlideIn 0.5s var(--ease-smooth) backwards;
      transition: border-color 0.35s var(--ease-smooth), box-shadow 0.35s var(--ease-smooth), transform 0.3s var(--ease-smooth);
      transform-style: preserve-3d;
      --mouse-x: 0px;
      --mouse-y: 0px;
    }

    @keyframes cardFadeSlideIn {
      from {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
        filter: blur(4px);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
        filter: blur(0);
      }
    }

    .testimonial-card::before {
      content: '';
      position: absolute;
      inset: -1px;
      background: radial-gradient(400px circle at var(--mouse-x) var(--mouse-y), rgba(188, 19, 254, 0.35), transparent 55%);
      border-radius: inherit;
      z-index: -1;
      opacity: 0;
      transition: opacity 0.4s ease;
      pointer-events: none;
    }

    .testimonial-card:hover::before {
      opacity: 1;
    }

    .testimonial-card:hover {
      border-color: rgba(188, 19, 254, 0.4);
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
    }

    .testimonial-img-wrapper {
      position: relative;
      height: 210px;
      overflow: hidden;
    }
    .testimonial-img-wrapper img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.6s var(--ease-smooth), filter 0.6s var(--ease-smooth);
    }
    .testimonial-card:hover .testimonial-img-wrapper img {
      transform: scale(1.04);
      filter: brightness(1.05);
    }

    .testimonial-img-overlay {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      padding: 15px 20px;
      background: linear-gradient(transparent, rgba(0,0,0,0.8));
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .testimonial-category-badge {
      padding: 4px 14px;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      background: rgba(188, 19, 254, 0.3);
      color: var(--secondary);
      border: 1px solid rgba(188, 19, 254, 0.4);
    }

    .testimonial-card-body {
      padding: 20px;
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .testimonial-customer {
      font-size: 0.85rem;
      color: var(--text-muted);
      margin-bottom: 5px;
      font-family: var(--font-mono);
    }

    .testimonial-title {
      font-size: 1.25rem;
      font-weight: 700;
      color: #fff;
      margin-bottom: 12px;
      line-height: 1.3;
    }

    .testimonial-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: auto;
      padding-top: 15px;
      border-top: 1px solid rgba(255, 255, 255, 0.05);
    }

    .testimonial-price {
      font-family: var(--font-mono);
      font-weight: 700;
      font-size: 1.1rem;
      color: var(--primary);
      text-shadow: 0 0 10px rgba(0, 243, 255, 0.3);
    }

    .testimonial-date {
      font-size: 0.8rem;
      color: var(--text-muted);
    }

    .testimonial-view-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-top: 15px;
      padding: 10px 20px;
      background: rgba(188, 19, 254, 0.15);
      border: 1px solid rgba(188, 19, 254, 0.3);
      border-radius: 50px;
      color: var(--secondary);
      font-family: var(--font-main);
      font-size: 0.85rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      width: fit-content;
    }

    .testimonial-view-btn:hover {
      background: var(--secondary);
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 5px 20px rgba(188, 19, 254, 0.4);
    }

    .testimonials-pagination {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 12px;
      margin-top: 20px;
    }

    .testimonials-pagination button {
      background: rgba(20, 20, 25, 0.6);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid var(--glass-border);
      color: var(--text-muted);
      width: 44px;
      height: 44px;
      border-radius: 50%;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .testimonials-pagination button:hover {
      background: rgba(188, 19, 254, 0.15);
      color: var(--secondary);
      border-color: rgba(188, 19, 254, 0.4);
      box-shadow: 0 0 15px rgba(188, 19, 254, 0.3);
      transform: translateY(-2px);
    }

    .testimonials-pagination button.active {
      background: var(--secondary);
      color: #fff;
      border-color: transparent;
      box-shadow: 0 0 20px rgba(188, 19, 254, 0.5);
    }

    .testimonials-pagination button:disabled {
      opacity: 0.3;
      cursor: not-allowed;
      transform: none !important;
      box-shadow: none !important;
      background: rgba(20, 20, 25, 0.2);
    }

    .testimonial-empty {
      text-align: center;
      padding: 60px 20px;
      color: var(--text-muted);
    }

    .testimonial-empty i {
      font-size: 3rem;
      margin-bottom: 15px;
      color: rgba(255,255,255,0.1);
    }

    /* --- LIGHTBOX MODAL --- */
    .lightbox-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.85);
      backdrop-filter: blur(10px);
      z-index: 99998;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      visibility: hidden;
      transition: all 0.4s ease;
      padding: 20px;
    }

    .lightbox-overlay.active {
      opacity: 1;
      visibility: visible;
    }

    .lightbox-content {
      background: rgba(15, 15, 20, 0.95);
      backdrop-filter: blur(30px);
      border: 1px solid var(--glass-border);
      border-radius: 24px;
      max-width: 800px;
      width: 100%;
      max-height: 90vh;
      overflow-y: auto;
      transform: scale(0.9) translateY(20px);
      transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
      padding: 0;
    }

    .lightbox-overlay.active .lightbox-content {
      transform: scale(1) translateY(0);
    }

    .lightbox-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 24px 30px;
      border-bottom: 1px solid var(--glass-border);
      position: sticky;
      top: 0;
      background: rgba(15, 15, 20, 0.95);
      backdrop-filter: blur(20px);
      z-index: 2;
      border-radius: 24px 24px 0 0;
    }

    .lightbox-title {
      font-size: 1.3rem;
      font-weight: 700;
      color: #fff;
    }

    .lightbox-close {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--glass-border);
      color: var(--text-muted);
      font-size: 1.2rem;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .lightbox-close:hover {
      background: rgba(255, 77, 87, 0.2);
      border-color: #ff4d57;
      color: #ff4d57;
      transform: rotate(90deg);
    }

    .lightbox-body {
      padding: 30px;
    }

    .lightbox-images {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 15px;
      margin-bottom: 25px;
    }

    .lightbox-img-item {
      position: relative;
      border-radius: 16px;
      overflow: hidden;
      border: 1px solid var(--glass-border);
      aspect-ratio: 1;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .lightbox-img-item:hover {
      border-color: var(--primary);
      transform: scale(1.03);
    }

    .lightbox-img-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .lightbox-img-label {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      padding: 8px;
      background: linear-gradient(transparent, rgba(0,0,0,0.9));
      font-size: 0.7rem;
      text-align: center;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .lightbox-details {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
    }

    .lightbox-detail-item {
      padding: 15px;
      background: rgba(255, 255, 255, 0.03);
      border-radius: 12px;
      border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .lightbox-detail-label {
      font-size: 0.75rem;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 5px;
    }

    .lightbox-detail-value {
      font-size: 1rem;
      color: #fff;
      font-weight: 600;
    }

    .lightbox-detail-value.price {
      color: var(--primary);
      font-family: var(--font-mono);
      font-size: 1.2rem;
    }

    .lightbox-links {
      display: flex;
      gap: 12px;
      margin-top: 20px;
      flex-wrap: wrap;
    }

    .lightbox-link-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 20px;
      border-radius: 50px;
      font-size: 0.85rem;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
      font-family: var(--font-main);
    }

    .lightbox-link-btn.project {
      background: rgba(0, 243, 255, 0.15);
      border: 1px solid rgba(0, 243, 255, 0.3);
      color: var(--primary);
    }

    .lightbox-link-btn.project:hover {
      background: var(--primary);
      color: #000;
    }

    .lightbox-link-btn.video {
      background: rgba(255, 0, 0, 0.15);
      border: 1px solid rgba(255, 0, 0, 0.3);
      color: #ff4444;
    }

    .lightbox-link-btn.video:hover {
      background: #ff4444;
      color: #fff;
    }

    /* Lightbox fullscreen image viewer */
    .lightbox-fullimg {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.95);
      z-index: 99999;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
      cursor: zoom-out;
    }

    .lightbox-fullimg.active {
      opacity: 1;
      visibility: visible;
    }

    .lightbox-fullimg img {
      max-width: 90%;
      max-height: 90%;
      object-fit: contain;
      border-radius: 8px;
    }

    /* Mobile responsive for lightbox */
    @media (max-width: 768px) {
      .lightbox-images {
        grid-template-columns: 1fr;
        gap: 10px;
      }

      .lightbox-img-item {
        aspect-ratio: 16/9;
      }

      .lightbox-details {
        grid-template-columns: 1fr;
      }

      .lightbox-content {
        border-radius: 16px;
      }

      .lightbox-body {
        padding: 20px;
      }

    }

    /* Hide cursor elements on touch devices + coarse pointer */
    @media (hover: none), (pointer: coarse) {
      .cursor-bulb, .cursor-dot, #cursorCanvas {
        display: none !important;
      }
    }

    /* --- GUESTBOOK SECTION --- */
    .guestbook-section {
      padding: 10px 0 40px;
      position: relative;
    }

    .guestbook-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      overflow: hidden;
    }

    .guestbook-header-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      padding-bottom: 15px;
    }

    .guestbook-subtitle {
      font-size: 1.5rem;
      font-weight: 700;
      color: #fff;
      font-family: var(--font-main);
      letter-spacing: 0.5px;
      position: relative;
    }

    .guestbook-subtitle::after {
      content: '';
      position: absolute;
      bottom: -16px;
      left: 0;
      width: 50px;
      height: 2px;
      background: var(--secondary);
      box-shadow: 0 0 10px rgba(188, 19, 254, 0.8);
    }

    .guestbook-trigger-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 24px;
      background: rgba(188, 19, 254, 0.15);
      border: 1px solid rgba(188, 19, 254, 0.3);
      border-radius: 50px;
      color: var(--secondary);
      font-family: var(--font-main);
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      box-shadow: 0 0 10px rgba(188, 19, 254, 0.1);
    }

    .guestbook-trigger-btn:hover {
      background: var(--secondary);
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 5px 20px rgba(188, 19, 254, 0.4);
    }

    .guestbook-grid {
      display: flex;
      flex-wrap: nowrap;
      gap: 20px;
      width: max-content;
      will-change: transform;
    }

    .guestbook-card {
      background: linear-gradient(180deg, rgba(24,24,33,0.7), rgba(16,16,22,0.5));
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid var(--glass-border);
      border-radius: 18px;
      padding: 18px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      transition: transform 0.32s var(--ease-smooth), border-color 0.32s var(--ease-smooth), box-shadow 0.32s var(--ease-smooth);
      width: 320px;
      flex-shrink: 0;
      box-shadow: 0 8px 32px rgba(0,0,0,0.22);
    }
    .guestbook-card:hover {
      border-color: rgba(188, 19, 254, 0.28);
      transform: translateY(-4px);
      box-shadow: 0 14px 36px rgba(0, 0, 0, 0.34);
    }

    .guestbook-card-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      border-bottom: 1px solid rgba(255, 255, 255, 0.03);
      padding-bottom: 8px;
    }

    .guestbook-card-info {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .guestbook-card-name {
      font-size: 0.95rem;
      font-weight: 600;
      color: #fff;
    }

    .guestbook-card-stars {
      display: flex;
      gap: 3px;
      color: #ffaa00;
      font-size: 0.82rem;
      margin-top: 2px;
      text-shadow: 0 0 6px rgba(255, 170, 0, 0.3);
    }

    .guestbook-card-date {
      font-size: 0.75rem;
      color: var(--text-muted);
      font-family: var(--font-mono);
      margin-top: 2px;
    }

    .guestbook-card-msg {
      font-size: 0.88rem;
      color: rgba(255, 255, 255, 0.8);
      line-height: 1.5;
      word-break: break-word;
    }

    .guestbook-empty {
      text-align: center;
      padding: 30px;
      color: var(--text-muted);
      font-size: 0.95rem;
      border: 1px dashed rgba(255, 255, 255, 0.05);
      border-radius: 16px;
      background: rgba(20, 20, 25, 0.2);
      width: 100%;
    }

    /* --- GUESTBOOK MODAL OVERLAY --- */
    .guestbook-modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.75);
      backdrop-filter: blur(15px);
      -webkit-backdrop-filter: blur(15px);
      z-index: 99999;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .guestbook-modal-overlay.active {
      opacity: 1;
      pointer-events: auto;
    }

    .guestbook-modal-card {
      background: rgba(20, 20, 25, 0.85);
      border: 1px solid var(--glass-border);
      box-shadow: 0 30px 70px rgba(0, 0, 0, 0.5);
      border-radius: 24px;
      padding: 35px;
      width: 90%;
      max-width: 500px;
      position: relative;
      transform: scale(0.9) translateY(20px);
      transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .guestbook-modal-overlay.active .guestbook-modal-card {
      transform: scale(1) translateY(0);
    }

    .guestbook-modal-close {
      position: absolute;
      top: 20px;
      right: 20px;
      background: transparent;
      border: none;
      color: var(--text-muted);
      font-size: 1.8rem;
      cursor: pointer;
      transition: color 0.3s ease;
      line-height: 1;
    }

    .guestbook-modal-close:hover {
      color: #fff;
    }

    .guestbook-modal-title {
      font-size: 1.4rem;
      font-weight: 700;
      color: #fff;
      margin-bottom: 8px;
    }

    .guestbook-modal-desc {
      font-size: 0.88rem;
      color: var(--text-muted);
      margin-bottom: 25px;
    }

    .guestbook-form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-bottom: 20px;
    }

    .guestbook-form-group label {
      font-size: 0.85rem;
      font-weight: 600;
      color: rgba(255, 255, 255, 0.7);
    }

    .guestbook-form-group input,
    .guestbook-form-group textarea {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      padding: 12px 16px;
      color: #fff;
      font-family: var(--font-main);
      font-size: 0.92rem;
      transition: all 0.3s ease;
    }

    .guestbook-form-group input:focus,
    .guestbook-form-group textarea:focus {
      outline: none;
      border-color: var(--secondary);
      background: rgba(255, 255, 255, 0.08);
      box-shadow: 0 0 15px rgba(188, 19, 254, 0.2);
    }

    .guestbook-form-group textarea {
      resize: none;
      height: 110px;
    }

    .star-rating-wrapper {
      display: flex;
      gap: 8px;
      font-size: 1.6rem;
      color: rgba(255, 255, 255, 0.2);
    }

    .star-btn {
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .star-btn.hovered,
    .star-btn.active {
      color: #ffaa00;
      text-shadow: 0 0 10px rgba(255, 170, 0, 0.4);
      transform: scale(1.15);
    }

    .guestbook-submit-btn {
      width: 100%;
      padding: 12px;
      background: linear-gradient(135deg, var(--secondary), var(--primary));
      border: none;
      border-radius: 12px;
      color: #fff;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 5px 15px rgba(188, 19, 254, 0.3);
    }

    .guestbook-submit-btn:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(188, 19, 254, 0.5);
    }

    .guestbook-submit-btn:disabled {
      background: rgba(255, 255, 255, 0.1);
      color: var(--text-muted);
      cursor: not-allowed;
      box-shadow: none;
    }

    /* ── UPGRADED LOOK: motion, hover & responsive polish (tetap originalitas) ── */
    /* Reveal on scroll */
    .reveal { opacity: 0; transform: translateY(18px); transition: opacity 0.7s var(--ease-smooth), transform 0.7s var(--ease-smooth); }
    .reveal.in { opacity: 1; transform: translateY(0); }
    @media (prefers-reduced-motion: reduce) { .reveal { opacity: 1; transform: none; } }

    /* Card hover lift — ganti translateY kasar jadi lebih halus */
    .gallery-card, .testimonial-card, .skill-category, .guestbook-card {
      will-change: transform, box-shadow;
    }
    .gallery-card:hover, .testimonial-card:hover {
      transform: translateY(-8px);
    }

    /* Focus visible — accessibility tanpa ubah look */
    a:focus-visible, button:focus-visible, input:focus-visible, textarea:focus-visible, select:focus-visible {
      outline: 2px solid var(--primary);
      outline-offset: 2px;
      border-radius: 6px;
    }

    /* Hero refined: subtle parallax glow follows scroll via CSS var */
    .hero { position: relative; isolation: isolate; }
    .hero::before {
      content: '';
      position: absolute;
      inset: -10% 0 0 0;
      background: radial-gradient(700px 400px at 70% 20%, rgba(0,243,255,0.08), transparent 65%),
                  radial-gradient(600px 400px at 30% 70%, rgba(188,19,254,0.07), transparent 65%);
      pointer-events: none;
      z-index: 0;
      opacity: calc(0.6 + 0.4 * var(--hero-parallax, 0));
      transform: translateY(calc(var(--hero-parallax, 0) * -18px));
      transition: transform 0.2s linear;
    }

    /* Gallery filter pill — active lebih premium */
    .filter-btn.active {
      background: linear-gradient(135deg, var(--primary), var(--secondary)) !important;
      color: #000 !important;
      border-color: transparent !important;
      box-shadow: 0 8px 28px rgba(0,243,255,0.28), 0 0 0 1px rgba(255,255,255,0.06) inset;
    }

    /* Pagination polish */
    .pagination-btn, .testimonials-pagination button {
      transition: transform 0.2s var(--ease-smooth), box-shadow 0.2s var(--ease-smooth), background 0.2s, border-color 0.2s;
    }

    /* Social buttons — icon lebih hidup */
    .social-btn { position: relative; isolation: isolate; }
    .social-btn i { transition: transform 0.35s var(--ease-spring); }
    .social-btn:hover i { transform: scale(1.08); }

    /* Nav blur lebih refined saat scroll */
    nav.is-scrolled {
      background: rgba(10,10,12,0.78) !important;
      border-color: var(--glass-border-strong) !important;
      box-shadow: 0 10px 40px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.04) inset !important;
    }

    /* Guestbook marquee — pause halus, tidak lompat; pause juga saat keyboard focus */
    .guestbook-container { mask-image: linear-gradient(to right, transparent, black 28px, black calc(100% - 28px), transparent); -webkit-mask-image: linear-gradient(to right, transparent, black 28px, black calc(100% - 28px), transparent); }
    .guestbook-grid:focus-within { animation-play-state: paused; }

    /* Gallery: image skeleton before load */
    .card-img img { background: linear-gradient(90deg, rgba(255,255,255,0.04), rgba(255,255,255,0.07), rgba(255,255,255,0.04)); background-size: 200% 100%; animation: skeletonShimmer 1.8s ease-in-out infinite; }
    .card-img img[loading="lazy"]:not([src=""]){ animation: none; background: transparent; }

    /* Improve text contrast slightly without changing palette */
    .text-reveal-anim { color: rgba(255,255,255,0.62); }
    .text-reveal-anim.revealed { color: #c9ced8; }

    /* ── MOTION SYSTEM v2 (elegan & subtle — originalitas tetap) ── */

    /* Staggered entrance: dipakai bila suatu grid diberi .stagger-group.
       Fill mode 'backwards': setelah selesai style kembali natural supaya
       :hover transform tetap bekerja. (Gallery & guestbook punya mekanisme
       entrance sendiri dan sengaja tidak diberi class ini.) */
    .stagger-group.in-view > * {
      animation: staggerIn 0.7s var(--ease-smooth) backwards;
      animation-delay: calc(min(var(--i, 0), 8) * 70ms);
    }
    @keyframes staggerIn {
      from { opacity: 0; transform: translateY(22px) scale(0.98); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Section title: underline menggambar diri + blur-to-focus saat masuk viewport */
    .section-title {
      opacity: 0;
      transform: translateY(14px);
      filter: blur(6px);
      transition:
        opacity 0.8s var(--ease-smooth),
        transform 0.8s var(--ease-smooth),
        filter 0.8s var(--ease-smooth);
    }
    .section-title.in-view { opacity: 1; transform: translateY(0); filter: blur(0); }
    .section-title::after {
      transform: translateX(-50%) scaleX(0);
      transform-origin: center;
      transition: transform 0.9s var(--ease-smooth) 0.25s;
    }
    .section-title.in-view::after { transform: translateX(-50%) scaleX(1); }

    /* Skill icon box: tilt halus + glow border saat hover (desktop) */
    @media (hover: hover) and (pointer: fine) {
      .tech-icon-box {
        transition: transform 0.4s var(--ease-spring), box-shadow 0.4s var(--ease-smooth), border-color 0.4s;
      }
      .tech-item:hover .tech-icon-box {
        transform: translateY(-3px) rotate(-2deg);
        box-shadow: 0 0 14px rgba(0, 243, 255, 0.18);
      }
      /* Journey icon: pulsa halus saat item aktif.
         Glow di-render pseudo-element ::after (opacity-only) agar animasi
         tetap di compositor — bukan box-shadow (paint tiap frame). */
      .journey-item .journey-icon::after {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: inherit;
        box-shadow: 0 0 20px rgba(0, 243, 255, 0.4), 0 0 60px rgba(0, 243, 255, 0.15);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.4s var(--ease-smooth);
      }
      .journey-item.active .journey-icon::after {
        animation: journeyIconPulse 2.4s var(--ease-smooth) infinite;
      }
      @keyframes journeyIconPulse {
        0%, 100% { opacity: 0.55; }
        50% { opacity: 1; }
      }
    }

    /* Hover glow konsisten antar card (gallery/testimonial/journey/guestbook) */
    .guestbook-card:hover {
      border-color: rgba(188, 19, 254, 0.28);
      box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35), 0 0 18px rgba(188, 19, 254, 0.1);
    }
    .journey-card:hover {
      box-shadow: 0 16px 40px rgba(0,0,0,0.35), 0 0 20px rgba(0,243,255,0.12);
    }

    /* Hero profile image: parallax tilt halus mengikuti mouse (desktop, JS-driven via CSS vars) */
    .image-wrapper {
      transform: perspective(900px)
        rotateX(calc(var(--tilt-y, 0) * -4deg))
        rotateY(calc(var(--tilt-x, 0) * 5deg));
      transition: transform 0.5s var(--ease-smooth);
      will-change: transform;
    }
    /* Glow orbit halus di belakang foto profil */
    .image-glow::after {
      content: '';
      position: absolute;
      inset: -12%;
      border-radius: 50%;
      border: 1px solid transparent;
      background:
        linear-gradient(var(--bg-dark), var(--bg-dark)) padding-box,
        conic-gradient(from calc(var(--orbit-angle, 0deg)), rgba(0,243,255,0.55), transparent 25%, rgba(188,19,254,0.55) 50%, transparent 75%, rgba(0,243,255,0.55)) border-box;
      -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
      -webkit-mask-composite: xor;
      mask-composite: exclude;
      opacity: 0.5;
      animation: orbitSpin 14s linear infinite;
      pointer-events: none;
    }
    @keyframes orbitSpin {
      to { --orbit-angle: 360deg; }
    }
    @property --orbit-angle {
      syntax: '<angle>';
      initial-value: 0deg;
      inherits: false;
    }

    /* Shimmer gradient pelan pada nama hero */
    .hero-text h1 {
      background: linear-gradient(110deg, #fff 25%, #7ee9f2 42%, #d67bff 58%, #fff 75%);
      background-size: 220% 100%;
      -webkit-background-clip: text;
      background-clip: text;
      animation: h1Shimmer 9s var(--ease-smooth) infinite;
    }
    @keyframes h1Shimmer {
      0%, 100% { background-position: 0% 0; }
      50% { background-position: 100% 0; }
    }

    /* Social buttons: glow border berurutan saat hover group */
    .social-links:hover .social-btn { border-color: rgba(255,255,255,0.14); }
    .social-links .social-btn:hover { border-color: transparent; }

    /* Lightbox & modal: masuk dengan spring scale */
    .lightbox-content, .guestbook-modal-content, .journey-modal-panel {
      transition-timing-function: var(--ease-spring);
    }

    /* ── GLOBAL REDUCED MOTION: hormati preferensi user di SEMUA animasi ── */
    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
      }
      .stagger-group > *, .section-title, .text-reveal-anim, .reveal,
      .journey-item, .testimonial-card, .gallery-card {
        opacity: 1 !important;
        transform: none !important;
        filter: none !important;
      }
      .image-glow::after, .cursor-bulb, .cursor-dot, #cursorCanvas,
      .preloader-particle, .firefly-glow { display: none !important; }
    }

    /* ── FASE 5: PERFORMA & MOBILE ──
       backdrop-filter di card non-kritis = repaint mahal di HP.
       Di ≤768px ganti dengan background solid lebih pekat — kedalaman
       glass tetap terasa, GPU dibebaskan. Navbar & modal (kritis untuk
       keterbacaan konten bergerak di belakangnya) tetap blur. */
    @media (max-width: 768px) {
      .journey-card,
      .skill-category,
      .testimonial-card,
      .guestbook-card,
      .chat-window,
      .chat-input-area,
      .github-card-container {
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
      }
      .journey-card   { background: linear-gradient(180deg, rgba(30,30,38,0.92), rgba(18,18,24,0.88)); }
      .skill-category { background: rgba(20, 20, 25, 0.88); }
      .testimonial-card { background: linear-gradient(180deg, rgba(24,24,33,0.95), rgba(16,16,22,0.9)); }
      .guestbook-card { background: linear-gradient(180deg, rgba(24,24,33,0.93), rgba(16,16,22,0.88)); }
      .chat-window,
      .chat-input-area { background: rgba(16,16,22,0.92); }
      .github-card-container { background: rgba(20,20,25,0.9); }

      /* will-change dibersihkan setelah entrance selesai (hemat memori layer) */
      .image-wrapper { will-change: auto; }
    }

    /* ── RESPONSIVE UPGRADE (mobile/tablet) ── */
    @media (max-width: 768px) {
      .hero { padding: 96px 5% 48px; min-height: auto; }
      .hero-text h1 { letter-spacing: -1.2px; }
      .section-title { font-size: clamp(1.7rem, 6vw, 2.2rem); }
      .section-header { margin-bottom: 38px; }
      .hero-content { gap: 32px; }
      section { padding: 64px 5%; }
      .gallery-grid { gap: 18px; grid-template-columns: 1fr; }
      .testimonials-grid { gap: 16px; }
      .testimonial-section { padding: 64px 5%; }
      .github-card-container { margin: 0 auto; }
      .skills-grid { gap: 18px; }
      .skill-category { padding: 24px; }
      .guestbook-header-row { flex-direction: column; gap: 14px; align-items: stretch; }
      .guestbook-trigger-btn { width: 100%; justify-content: center; }
    }
    @media (max-width: 480px) {
      nav { top: 12px; width: 94%; padding: 10px 18px; }
      .hero-greeting { font-size: 0.95rem; }
      .typing-container { font-size: 1.35rem; }
      .gallery-nav { gap: 10px; margin-bottom: 28px; }
      .filter-btn { padding: 9px 18px; font-size: 0.9rem; }
      .card-content { padding: 18px; }
      .card-content h3 { font-size: 1.15rem; }
    }
    /* Tablet landscape — 2 kolom gallery lebih lega */
    @media (min-width: 769px) and (max-width: 1100px) {
      .gallery-grid { grid-template-columns: repeat(2, 1fr); }
      .testimonials-grid { grid-template-columns: repeat(2, 1fr); }
    }
  </style>
  <link rel="stylesheet" href="/portfolio.css">
  <script src="/portfolio-ui.js" defer></script>
  <noscript><style>#preloader{display:none}body.no-scroll{overflow:auto}[data-aos],.text-reveal-anim{opacity:1!important;transform:none!important}</style></noscript>
</head>

<body>
  <a href="#home" class="skip-link" data-nosnippet>Lewati ke konten utama</a>
  <div id="scrollProgress" aria-hidden="true"></div>
  <div id="preloader" hidden>
    <div class="preloader-particles" id="preloaderParticles"></div>
    <div class="loader-content">
      <div class="loader-text" data-text="RELV-DEV">RELV-DEV</div>
    </div>
    <div class="loader-bar-container">
      <div class="loader-bar" id="loaderBar"></div>
    </div>
    <div class="loader-percent" id="loaderPercent">0%</div>
  </div>

  <div class="cursor-bulb" id="cursorBulb"></div>
  <div class="cursor-dot" id="cursorDot"></div>
  <canvas id="cursorCanvas" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; pointer-events: none; z-index: 9998;"></canvas>

  <nav aria-label="Main navigation">
    <a href="https://relv.biz.id/" class="logo">
      <i class="fas fa-code"></i> RelV
    </a>

    <button class="menu-toggle" type="button" aria-label="Open navigation menu" aria-expanded="false" aria-controls="primaryNav">
      <span></span>
      <span></span>
      <span></span>
    </button>

    <ul class="nav-links" id="primaryNav">
      <li><a href="#home" class="active">Beranda</a></li>
      <li><a href="#featured">Karya</a></li>
      <li><a href="#education">Perjalanan</a></li>
      <li><a href="#skills">Keahlian</a></li>
      <li><a href="#testimonials">Klien</a></li>
    </ul>
  </nav>

  <section id="home" class="hero" tabindex="-1">
    <div class="hero-content">
      <div class="hero-text" data-aos="fade-right" data-aos-duration="1000">
        <span class="hero-greeting"><span class="availability-dot" aria-hidden="true"></span> PENGEMBANG · DESAINER</span>
        <h1>Farel Putra<br>Firmansyah</h1>
        <div class="typing-container">
          Saya&nbsp;<span class="typing-text"></span><span class="cursor"></span>
        </div>
        <p class="hero-description text-reveal-anim">
          Saya Farel, pengembang dan pegiat teknologi dengan pengalaman di pemrograman, cloud computing, dan desain visual. Melalui proyek, riset, dan kegiatan kepemimpinan, saya mengeksplorasi cara teknologi membantu menyelesaikan masalah.
        </p>
        <div class="hero-actions">
          <a class="hero-cta primary" href="#featured">Jelajahi karya <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
          <a class="hero-cta" href="mailto:contact@relv.biz.id">Hubungi saya <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></a>
        </div>
        <div class="social-links">
          <a href="https://www.instagram.com/zx_frl" target="_blank" class="social-btn" aria-label="Instagram">
            <i class="fab fa-instagram"></i>
          </a>
          <a href="mailto:contact@relv.biz.id" class="social-btn" aria-label="Email">
            <i class="fas fa-envelope"></i>
          </a>
          <a href="https://github.com/RelV-Dev" target="_blank" class="social-btn" aria-label="GitHub">
            <i class="fab fa-github"></i>
          </a>
          <a href="https://wa.me/6285712225507?text=Halo%2C%20saya%20mendapatkan%20kontak%20Anda%20melalui%20website%20portofolio.%0A%0ASaya%20tertarik%20untuk%20berdiskusi%20mengenai%20kebutuhan%20yang%20saya%20miliki.%20Apakah%20saat%20ini%20Anda%20tersedia%20untuk%20konsultasi%3F%0A%0ATerima%20kasih." target="_blank" class="social-btn" aria-label="WhatsApp">
            <i class="fab fa-whatsapp"></i>
          </a>
        </div>
      </div>

      <div class="hero-image-container" data-aos="fade-left" data-aos-duration="1000"><span class="portrait-caption">KREATIVITAS &amp; TEKNOLOGI</span>
        <div class="image-wrapper">
          <div class="image-glow"></div>
          <img src="https://res.cloudinary.com/dvpq5fsef/image/upload/c_fill,g_auto,w_400,h_400,q_auto,f_auto/v1780844527/dvpyazydsd46jzrvu6bq.webp" alt="Farel Putra Firmansyah" class="profile-img" width="400" height="400" fetchpriority="high" decoding="async">
        </div>
      </div>
    </div>
  </section>

  <section id="featured" class="featured-section">
    <p class="section-kicker">PROYEK UNGGULAN</p><h2 class="section-title">Dari ide menjadi karya.</h2>
    <div class="featured-projects">
    <?php foreach (array_slice($projects, 0, 3) as $index => $project): ?>
      <article class="featured-project">
        <div class="featured-image"><?php if (public_url($project['image_url'] ?? '')): ?><img src="<?= htmlspecialchars(public_url($project['image_url'])) ?>" alt="<?= htmlspecialchars($project['title']) ?>" loading="lazy" width="800" height="500"><?php endif; ?></div>
        <div><span class="section-kicker">0<?= $index + 1 ?> · <?= htmlspecialchars($project['sub_category']) ?></span><h3><?= htmlspecialchars($project['title']) ?></h3><p><?= htmlspecialchars($project['description']) ?></p>
        <div class="hero-actions"><?php if (public_url($project['live_url'] ?? '')): ?><a class="hero-cta primary" href="<?= htmlspecialchars(public_url($project['live_url'])) ?>" target="_blank" rel="noopener noreferrer">Lihat demo ↗</a><?php endif; ?><?php if (public_url($project['github_url'] ?? '')): ?><a class="hero-cta" href="<?= htmlspecialchars(public_url($project['github_url'])) ?>" target="_blank" rel="noopener noreferrer">Kode sumber ↗</a><?php endif; ?><a class="hero-cta" href="#work-<?= (int)$project['id'] ?>">Lihat di galeri</a></div></div>
      </article>
    <?php endforeach; ?>
    </div>
  <?php if (!$projects): ?><p class="data-notice">Proyek belum tersedia saat ini. <a href="/#featured">Muat ulang</a> atau kunjungi <a href="https://github.com/RelV-Dev" target="_blank" rel="noopener noreferrer">GitHub saya</a>.</p><?php endif; ?>
  </section>


  <section id="skills" style="background: var(--bg-dark);">
    <div class="section-header" data-aos="fade-up">
      <h2 class="section-title">Keahlian</h2>
    </div>

    <div class="skills-grid">
      <div class="skill-category" data-aos="fade-up" data-aos-delay="0">
        <div class="skill-header">
          <i class="fas fa-terminal"></i>
          <h3>Development</h3>
        </div>
        <div class="skill-icons">
          <div class="tech-item">
            <div class="tech-icon-box" style="border-color: #f7df1e;"><i class="fab fa-js fa-js"></i></div>
            <span class="tech-name">JavaScript</span>
          </div>
          <div class="tech-item">
            <div class="tech-icon-box" style="border-color: #3776ab;"><i class="fab fa-python fa-python"></i></div>
            <span class="tech-name">Python</span>
          </div>
          <div class="tech-item">
            <div class="tech-icon-box" style="border-color:#00599C;">
              <img loading="lazy" src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/cplusplus/cplusplus-original.svg" alt="C++" style="width:22px;height:22px;" />
            </div>
            <span class="tech-name">C++</span>
          </div>
          <div class="tech-item">
            <div class="tech-icon-box" style="border-color: #61dafb;"><i class="fab fa-react fa-react"></i></div>
            <span class="tech-name">React</span>
          </div>
          <div class="tech-item">
            <div class="tech-icon-box" style="border-color:#fffff8;">
              <img loading="lazy" src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/nextjs/nextjs-original.svg" alt="Next.js" style="width:22px;height:22px;" />
            </div>
            <span class="tech-name">Next.js</span>
          </div>
          <div class="tech-item">
            <div class="tech-icon-box" style="border-color: #777bb4;"><i class="fab fa-php fa-php"></i></div>
            <span class="tech-name">PHP</span>
          </div>
          <div class="tech-item">
            <div class="tech-icon-box" style="border-color: #336791;"> <img loading="lazy" src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg" alt="SQL" style="width:22px;height:22px;" /></div>
            <span class="tech-name">SQL</span>
          </div>
          <div class="tech-item">
            <div class="tech-icon-box" style="border-color:#FFCA28;">
              <img loading="lazy" src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/firebase/firebase-original.svg" alt="Firebase" style="width:22px;height:22px;" />
            </div>
            <span class="tech-name">Firebase</span>
          </div>
          <div class="tech-item">
            <div class="tech-icon-box" style="border-color:#3ECF8E;">
              <img loading="lazy" src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/supabase/supabase-original.svg" alt="Supabase" style="width:22px;height:22px;" />
            </div>
            <span class="tech-name">Supabase</span>
          </div>
        </div>
      </div>

      <div class="skill-category" data-aos="fade-up" data-aos-delay="200">
        <div class="skill-header">
          <i class="fas fa-bezier-curve" style="color: var(--secondary);"></i>
          <h3>Visual Design</h3>
        </div>
        <div class="skill-icons">
          <div class="tech-item">
            <div class="tech-icon-box"><img loading="lazy" src="https://cdn.brandfetch.io/id9mVQlyB1/w/400/h/400/theme/dark/icon.jpeg?c=1dxbfHSJFAPEGdCLU4o5B" alt="Canva" style="width:22px;height:22px" /></div>
            <span class="tech-name">Canva</span>
          </div>
          <div class="tech-item">
            <div class="tech-icon-box"><img loading="lazy" src="https://raw.githubusercontent.com/brand-icons/brands/refs/heads/master/icons/color/photoshop.svg" alt="Photoshop" style="width:22px;height:22px" /></div>
            <span class="tech-name">Photoshop</span>
          </div>
          <div class="tech-item">
            <div class="tech-icon-box"><img loading="lazy" src="https://raw.githubusercontent.com/brand-icons/brands/refs/heads/master/icons/color/illustrator.svg" alt="Illustrator" style="width:22px;height:22px" /></div>
            <span class="tech-name">Illustrator</span>
          </div>
          <div class="tech-item">
            <div class="tech-icon-box"><img loading="lazy" src="https://raw.githubusercontent.com/brand-icons/brands/refs/heads/master/icons/color/figma.svg" alt="Figma" style="width:22px;height:22px" /></div>
            <span class="tech-name">Figma</span>
          </div>
          <div class="tech-item">
            <div class="tech-icon-box"><img loading="lazy" src="https://cdn.brandfetch.io/id5BGrK6kq/w/400/h/400/theme/dark/icon.jpeg?c=1dxbfHSJFAPEGdCLU4o5B" alt="Ibis Paint X" style="width:22px;height:22px" /></div>
            <span class="tech-name">Ibis Paint X</span>
          </div>
        </div>
      </div>

      <div class="skill-category" data-aos="fade-up" data-aos-delay="400">
        <div class="skill-header">
          <i class="fas fa-film" style="color: var(--tertiary);"></i>
          <h3>Video Editing</h3>
        </div>
        <div class="skill-icons">
          <div class="tech-item">
            <div class="tech-icon-box"><img loading="lazy" src="https://cdn.brandfetch.io/idUmqKFgE3/w/400/h/400/theme/dark/icon.jpeg?c=1dxbfHSJFAPEGdCLU4o5B" alt="CapCut" style="width:22px;height:22px" /></div>
            <span class="tech-name">CapCut</span>
          </div>
          <div class="tech-item">
            <div class="tech-icon-box"><img loading="lazy" src="https://raw.githubusercontent.com/brand-icons/brands/refs/heads/master/icons/color/aftereffects.svg" alt="After Effects" style="width:22px;height:22px" /></div>
            <span class="tech-name">After Effects</span>
          </div>
          <div class="tech-item">
            <div class="tech-icon-box"><img loading="lazy" src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/90/DaVinci_Resolve_17_logo.svg/960px-DaVinci_Resolve_17_logo.svg.png" alt="DaVinci Resolve" style="width:22px;height:22px" /></div>
            <span class="tech-name">DaVinci Resolve</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="education">
    <div class="education-sticky-wrapper">
      <div class="section-header">
        <h2 class="section-title">Perjalanan saya</h2>
      </div>

      <div class="education-container" id="eduContainer">
        <div class="journey-line"></div>
        <div class="journey-line-progress" id="journeyProgress">
          <div class="journey-firefly-container">
            <div class="firefly-glow"></div>
          </div>
        </div>

        <div class="journey-items">
          <div class="journey-item">
            <div class="journey-icon">
              <i class="fas fa-microchip"></i>
            </div>
            <div class="journey-card" data-period="smp">
              <span class="journey-year">2019 - 2022</span>
              <h3>SMP Negeri 4 Pati</h3>
              <p class="text-reveal-anim">Mulai mendalami komputer dan dasar logika pemrograman.</p>
              <span style="font-size:0.75rem;color:var(--primary);margin-top:10px;display:block;font-family:var(--font-mono);"><i class="fas fa-info-circle"></i> Lihat detail</span>
            </div>
          </div>

          <div class="journey-item">
            <div class="journey-icon">
              <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="journey-card" data-period="sma">
              <span class="journey-year">2023 - Present</span>
              <h3>SMA Negeri 3 Pati</h3>
              <p class="text-reveal-anim">Koordinator IT dan ketua SEA, aktif dalam Karya Ilmiah Remaja (KIR), pengembangan teknologi, dan kompetisi akademik.</p>
              <span style="font-size:0.75rem;color:var(--primary);margin-top:10px;display:block;font-family:var(--font-mono);"><i class="fas fa-info-circle"></i> Lihat detail</span>
            </div>
          </div>

          <div class="journey-item">
            <div class="journey-icon">
              <i class="fas fa-university"></i>
            </div>
            <div class="journey-card" data-period="unair">
              <span class="journey-year">2026 - Present</span>
              <h3>Universitas Airlangga</h3>
              <p class="text-reveal-anim">Google Student Ambassador — mengenalkan teknologi, program, dan inisiatif Google kepada mahasiswa.</p>
              <span style="font-size:0.75rem;color:var(--primary);margin-top:10px;display:block;font-family:var(--font-mono);"><i class="fas fa-info-circle"></i> Lihat detail</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>



  <section id="gallery">
    <p class="section-kicker">PILIHAN KARYA</p>
    <p class="section-intro">Jelajahi proyek, teknologi yang digunakan, serta pencapaian yang mendukung perjalanan saya.</p>
    <?php if ($gallery_failed): ?><div class="data-notice" role="status">Galeri belum berhasil dimuat. <a href="/#gallery">Coba lagi</a></div><?php endif; ?>
    <div class="section-header" data-aos="fade-up">
      <h2 class="section-title">Karya & pencapaian</h2>
    </div>

    <div class="gallery-nav" data-aos="fade-up">
      <button class="filter-btn active" data-filter="all">Semua</button>
      <button class="filter-btn" data-filter="projects">Proyek</button>
      <button class="filter-btn" data-filter="certificates">Sertifikat</button>
      <button class="filter-btn" data-filter="awards">Prestasi</button>
    </div>

    <div class="gallery-grid">
      <?php foreach ($gallery as $item): ?>
        <?php if (!$item['is_visible']) continue; ?>
        <div class="gallery-card" id="work-<?= (int)$item['id'] ?>" data-category="<?= htmlspecialchars($item['category']) ?>">
          <div class="card-img">
            <?php if (!empty($item['image_url'])): ?>
              <img loading="lazy" src="<?= htmlspecialchars(public_url($item['image_url']), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['title']) ?>">
              <?php if (!empty($item['github_url']) || !empty($item['live_url']) || !empty($item['detail_url'])): ?>
              <div class="card-overlay">
                <?php if (!empty($item['github_url'])): ?>
                  <a href="<?= htmlspecialchars(public_url($item['github_url']), ENT_QUOTES, 'UTF-8') ?>" class="overlay-btn" target="_blank" title="Code"><i class="fab fa-github"></i></a>
                <?php endif; ?>
                <?php if (!empty($item['live_url'])): ?>
                  <a href="<?= htmlspecialchars(public_url($item['live_url']), ENT_QUOTES, 'UTF-8') ?>" class="overlay-btn" target="_blank" title="Live Demo"><i class="fas fa-link"></i></a>
                <?php endif; ?>
                <?php if (!empty($item['detail_url'])): ?>
                  <?php
                    $overlay_icon = 'fas fa-link';
                    if ($item['category'] === 'awards') { $overlay_icon = 'fas fa-trophy'; }
                    elseif ($item['category'] === 'certificates') { $overlay_icon = 'fas fa-certificate'; }
                  ?>
                  <a href="<?= htmlspecialchars(public_url($item['detail_url']), ENT_QUOTES, 'UTF-8') ?>" class="overlay-btn" target="_blank" title="View"><i class="<?= $overlay_icon ?>"></i></a>
                <?php endif; ?>
              </div>
              <?php endif; ?>
            <?php else: ?>
              <?php
                $p = getPlaceholderColor($item['icon_type']);
              ?>
              <div class="card-img-placeholder" style="height: 220px; background: linear-gradient(135deg, rgba(5, 5, 5, 0.9), <?= $p['bg'] ?>); display: flex; align-items: center; justify-content: center; border-bottom: 1px solid rgba(255, 255, 255, 0.05); position: relative;">
                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(circle at center, <?= $p['bg'] ?> 0%, transparent 70%); pointer-events: none;"></div>
                <i class="<?= $p['icon'] ?>" style="font-size: 3.5rem; color: <?= $p['color'] ?>; filter: drop-shadow(0 0 15px <?= $p['glow'] ?>);"></i>
                <?php if (!empty($item['github_url']) || !empty($item['live_url']) || !empty($item['detail_url'])): ?>
                <div class="card-overlay">
                  <?php if (!empty($item['github_url'])): ?>
                    <a href="<?= htmlspecialchars(public_url($item['github_url']), ENT_QUOTES, 'UTF-8') ?>" class="overlay-btn" target="_blank" title="Code"><i class="fab fa-github"></i></a>
                  <?php endif; ?>
                  <?php if (!empty($item['live_url'])): ?>
                    <a href="<?= htmlspecialchars(public_url($item['live_url']), ENT_QUOTES, 'UTF-8') ?>" class="overlay-btn" target="_blank" title="Live Demo"><i class="fas fa-link"></i></a>
                  <?php endif; ?>
                  <?php if (!empty($item['detail_url'])): ?>
                    <?php
                      $overlay_icon = 'fas fa-link';
                      if ($item['category'] === 'awards') { $overlay_icon = 'fas fa-trophy'; }
                      elseif ($item['category'] === 'certificates') { $overlay_icon = 'fas fa-certificate'; }
                    ?>
                    <a href="<?= htmlspecialchars(public_url($item['detail_url']), ENT_QUOTES, 'UTF-8') ?>" class="overlay-btn" target="_blank" title="View"><i class="<?= $overlay_icon ?>"></i></a>
                  <?php endif; ?>
                </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
          <div class="card-content">
            <span class="card-category"><?= htmlspecialchars($item['sub_category']) ?></span>
            <h3><?= htmlspecialchars($item['title']) ?></h3>
            <p class="text-reveal-anim"><?= htmlspecialchars($item['description']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>

        <!-- Google Student Ambassador (bukan dari DB; gambar menyusul, di-edit manual) -->
        <div class="gallery-card" data-category="awards">
          <div class="card-img">
            <div class="card-img-placeholder" style="height: 220px; background: linear-gradient(135deg, rgba(5, 5, 5, 0.9), rgba(188, 19, 254, 0.1)); display: flex; align-items: center; justify-content: center; border-bottom: 1px solid rgba(255, 255, 255, 0.05); position: relative;">
              <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(circle at center, rgba(188, 19, 254, 0.1) 0%, transparent 70%); pointer-events: none;"></div>
              <i class="fab fa-google" style="font-size: 3.5rem; color: var(--secondary); filter: drop-shadow(0 0 15px rgba(188, 19, 254, 0.5));"></i>
            </div>
          </div>
          <div class="card-content">
            <span class="card-category">Award - 2026</span>
            <h3>Google Student Ambassador</h3>
            <p class="text-reveal-anim">Selected as a Google Student Ambassador, representing Google at Universitas Airlangga and engaging students with Google's technologies, programs, and initiatives.</p>
          </div>
        </div>
    </div>

    <div class="pagination-controls" id="galleryPagination">
      <!-- Pagination buttons injected by JS -->
    </div>
  </section>

  <section class="github-activity-section" id="github" aria-labelledby="github-title" data-aos="fade-up">
    <div class="section-header gh-section-heading">
      <div><p class="section-kicker">CODE IN ORBIT</p><h2 class="section-title" id="github-title">Semesta kontribusi.</h2></div>
      <p>Setiap hari, satu jejak. Jelajahi aktivitas GitHub saya dalam ruang tiga dimensi.</p>
    </div>
    <div class="github-card-container">
      <aside class="github-stats-info" aria-label="Statistik GitHub">
        <div class="gh-profile-mark"><i class="fab fa-github" aria-hidden="true"></i><span>DEVELOPER LOG<br><b id="gh-username">@RelV-Dev</b></span></div>
        <div class="gh-primary-stat"><span class="stat-value highlight" id="gh-contributions">—</span><span>kontribusi tahun ini</span></div>
        <div class="stat-row"><span class="stat-label">Repositori</span><span class="stat-value" id="gh-repos">—</span></div>
        <div class="stat-row"><span class="stat-label">Total commit</span><span class="stat-value" id="gh-commits">—</span></div>
        <div class="gh-language-block"><p class="gh-caption">BAHASA TERATAS</p><div id="gh-languages"><span class="gh-muted">Menunggu data GitHub</span></div></div>
        <a class="gh-profile-link" href="https://github.com/RelV-Dev" target="_blank" rel="noopener noreferrer">Jelajahi repositori <span aria-hidden="true">↗</span></a>
      </aside>
      <div class="gh-observatory">
        <div class="gh-stage-heading"><span><span class="gh-beacon" aria-hidden="true"></span> CONTRIBUTION ORBIT</span><span>3D EXPLORER / 01</span></div>
        <div id="github-3d-canvas" aria-label="Visualisasi kontribusi GitHub"></div>
        <div class="gh-toolbar" role="group" aria-label="Kontrol visualisasi" hidden>
          <div class="gh-view-switch"><button type="button" data-gh-view="orbit" aria-pressed="true">Orbit</button><button type="button" data-gh-view="top" aria-pressed="false">Dari atas</button></div>
          <button type="button" id="gh-reset" aria-label="Kembalikan sudut pandang awal" title="Reset sudut pandang">↺</button>
          <button type="button" id="gh-motion" aria-pressed="false">Jeda</button>
        </div>
        <div class="gh-data-state"><span id="gh-data-status" role="status">Profil siap dijelajahi · menunggu data GitHub</span><button type="button" id="gh-data-retry" hidden>Coba lagi</button></div>
        <div class="gh-stage-footer"><span id="gh-interaction-hint">Seret untuk memutar · pilih batang untuk detail</span><div class="gh-legend" aria-label="Warna dan tinggi menunjukkan jumlah kontribusi"><span>Sedikit</span><i></i><i></i><i></i><i></i><i></i><span>Banyak</span></div></div>
        <div class="gh-inspector" hidden>
          <div class="gh-day-readout" aria-live="polite" aria-atomic="true"><span id="gh-day-date">Pilih hari</span><strong id="gh-day-count">—</strong></div>
          <div class="gh-timeline"><label class="gh-caption" for="gh-day-slider">JELAJAHI HARI <span id="gh-date-range"></span></label><input type="range" id="gh-day-slider" min="0" max="0" value="0" aria-label="Pilih tanggal kontribusi"></div>
          <button type="button" id="gh-greet" title="Sapa astronaut">Sapa astronaut <span aria-hidden="true">✧</span></button>
        </div>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS SECTION -->
  <section id="testimonials" class="testimonial-section">
    <div class="section-header" data-aos="fade-up">
      <h2 class="section-title">Pengalaman klien</h2>
    </div>

    <div class="testimonials-grid-container" data-aos="fade-up">
      <div class="testimonials-grid" id="testimonialGrid">
        <!-- Skeleton Loading Placeholders -->
        <div class="skeleton-testimonial skeleton" id="skelTestimonial1">
          <div style="display:flex;gap:12px;align-items:center">
            <div class="skeleton skeleton-avatar"></div>
            <div style="flex:1">
              <div class="skeleton skeleton-text short"></div>
              <div class="skeleton skeleton-text" style="width:40%;height:10px"></div>
            </div>
          </div>
          <div class="skeleton skeleton-text long"></div>
          <div class="skeleton skeleton-text medium"></div>
          <div class="skeleton skeleton-text short"></div>
        </div>
        <div class="skeleton-testimonial skeleton" id="skelTestimonial2">
          <div style="display:flex;gap:12px;align-items:center">
            <div class="skeleton skeleton-avatar"></div>
            <div style="flex:1">
              <div class="skeleton skeleton-text short"></div>
              <div class="skeleton skeleton-text" style="width:40%;height:10px"></div>
            </div>
          </div>
          <div class="skeleton skeleton-text long"></div>
          <div class="skeleton skeleton-text medium"></div>
          <div class="skeleton skeleton-text short"></div>
        </div>
        <div class="skeleton-testimonial skeleton" id="skelTestimonial3">
          <div style="display:flex;gap:12px;align-items:center">
            <div class="skeleton skeleton-avatar"></div>
            <div style="flex:1">
              <div class="skeleton skeleton-text short"></div>
              <div class="skeleton skeleton-text" style="width:40%;height:10px"></div>
            </div>
          </div>
          <div class="skeleton skeleton-text long"></div>
          <div class="skeleton skeleton-text medium"></div>
          <div class="skeleton skeleton-text short"></div>
        </div>
      </div>
      <div class="testimonials-pagination" id="testimonialPagination">
        <!-- Injected dynamically by JS -->
      </div>
    </div>

    <div class="testimonial-empty" id="testimonialEmpty" style="display:none;">
      <i class="fas fa-box-open"></i>
      <p>No testimonials yet. Check back soon!</p>
    </div>
  </section>

  <!-- GUESTBOOK SECTION -->
  <section id="guestbook" class="guestbook-section" data-aos="fade-up">
    <div class="guestbook-container">
      <div class="guestbook-header-row">
        <h3 class="guestbook-subtitle">Guestbook</h3>
        <button id="openGuestbookModalBtn" class="guestbook-trigger-btn">
          <i class="fas fa-pen-fancy"></i> Tulis pesan
        </button>
      </div>

      <button type="button" id="pauseGuestbook" class="motion-toggle" aria-pressed="false">Jeda gerakan</button>
      <div class="guestbook-grid" id="guestbookGrid">
        <!-- Messages loaded dynamically -->
      </div>
      <div class="guestbook-empty" id="guestbookEmpty" style="display:none;">
        <p>Belum ada pesan. Jadilah yang pertama menulis di guestbook!</p>
      </div>
    </div>
  </section>

  <!-- GUESTBOOK MODAL OVERLAY -->
  <div class="guestbook-modal-overlay" id="guestbookModalOverlay">
    <div class="guestbook-modal-card">
      <button class="guestbook-modal-close" id="closeGuestbookModalBtn">&times;</button>
      <h4 class="guestbook-modal-title">Tulis pesan</h4>
      <p class="guestbook-modal-desc">Tinggalkan pesan, jejak digital, atau saran Anda.</p>

      <form id="guestbookForm"><div class="honeypot" aria-hidden="true"><label>Website<input id="guestbookWebsite" tabindex="-1" autocomplete="off"></label></div>
        <div class="guestbook-form-group">
          <label for="guestbookName">Nama Anda</label>
          <input type="text" id="guestbookName" maxlength="30" required placeholder="Tulis nama Anda...">
        </div>
        <div class="guestbook-form-group">
          <label>Rating Anda</label>
          <div class="star-rating-wrapper" id="starRatingWrapper">
            <i class="far fa-star star-btn" data-value="1"></i>
            <i class="far fa-star star-btn" data-value="2"></i>
            <i class="far fa-star star-btn" data-value="3"></i>
            <i class="far fa-star star-btn" data-value="4"></i>
            <i class="far fa-star star-btn" data-value="5"></i>
          </div>
          <input type="hidden" id="guestbookRating" value="5">
        </div>
        <div class="guestbook-form-group">
          <label for="guestbookMessage">Pesan</label>
          <textarea id="guestbookMessage" maxlength="200" required placeholder="Tulis pesan singkat Anda (maks 200 karakter)..."></textarea>
        </div>
        <button type="submit" class="guestbook-submit-btn" id="guestbookSubmitBtn">
          <span>Kirim Pesan</span>
        </button>
      </form>
    </div>
  </div>

  <div class="chat-widget-container" id="chatWidgetContainer">
    <div class="chat-window" id="chatWindow" role="dialog" aria-label="Asisten portofolio Farel" inert>
      <div class="chat-header">
        <div class="chat-title">
          <span class="chat-name">Farel AI Assistant</span>
          <span class="chat-status" id="apiStatus">
            <span class="status-dot" id="statusDot"></span>
            <span id="statusText">Connecting...</span>
          </span>
        </div>
        <div class="header-controls">
          <button class="header-btn clear-chat" id="newChatBtn" title="New Chat (Clear History)">
            <i class="fas fa-trash-alt"></i>
          </button>
          <button class="header-btn close-chat" id="chatCloseBtn" title="Close">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>

      <div class="chat-messages" id="chatMessages" role="log" aria-live="polite" aria-relevant="additions">
        <div class="message bot" id="welcomeMsg">
          <img src="https://res.cloudinary.com/dvpq5fsef/image/upload/c_fill,g_auto,w_100,h_100,q_auto,f_auto/v1780844527/dvpyazydsd46jzrvu6bq.webp" alt="Bot" class="bot-avatar">
          <div class="bot-text-content">
            Hi! 👋 Saya asisten virtual Farel.<br>
            Tanyakan profil, keahlian, proyek, atau kontak Farel. Jawaban saya hanya bersumber dari konten portofolio ini.
          </div>
        </div>
      </div>

      <div class="typing-indicator" id="typingIndicator">
        <span class="typing-dot"></span>
        <span class="typing-dot"></span>
        <span class="typing-dot"></span>
      </div>

      <div class="chat-prompts" aria-label="Contoh pertanyaan">
        <button type="button" data-chat-question="Apa keahlian Farel?">Keahlian</button>
        <button type="button" data-chat-question="Apa proyek Farel?">Proyek</button>
        <button type="button" data-chat-question="Bagaimana menghubungi Farel?">Kontak</button>
      </div>
      <form class="chat-input-area" id="chatForm">
        <input type="text" class="chat-input" id="chatInput" aria-label="Pertanyaan tentang portofolio" maxlength="1000" placeholder="Ketik pesan..." autocomplete="off">
        <button type="submit" class="chat-send-btn" id="chatSendBtn" aria-label="Kirim pesan">
          <i class="fas fa-paper-plane"></i>
        </button>
      </form>
    </div>

    <button class="chat-toggle-btn" id="chatToggleBtn" aria-label="Buka asisten portofolio" aria-controls="chatWindow" aria-expanded="false">
      <i class="fas fa-robot"></i>
    </button>
  </div>


  <!-- LIGHTBOX MODAL -->
  <div class="lightbox-overlay" id="lightboxOverlay">
    <div class="lightbox-content">
      <div class="lightbox-header">
        <h3 class="lightbox-title" id="lightboxTitle">Project Details</h3>
        <button class="lightbox-close" id="lightboxClose"><i class="fas fa-times"></i></button>
      </div>
      <div class="lightbox-body" id="lightboxBody">
        <!-- Content injected by JS -->
      </div>
    </div>
  </div>

  <!-- JOURNEY DETAIL MODAL -->
  <div class="journey-modal" id="journeyModal" hidden>
    <div class="journey-modal-overlay" id="journeyModalOverlay"></div>
    <div class="journey-modal-card">
      <button class="journey-modal-close" id="journeyModalClose">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <div class="journey-modal-header">
        <div class="journey-modal-icon-wrapper" id="journeyModalIcon"></div>
        <div>
          <span class="journey-modal-year" id="journeyModalYear"></span>
          <h3 id="journeyModalTitle"></h3>
        </div>
      </div>
      <div class="journey-modal-body">
        <div class="journey-modal-section">
          <h4><i class="fa-solid fa-award"></i> Prestasi & peran</h4>
          <ul id="journeyModalAchievements"></ul>
        </div>
        <div class="journey-modal-section" id="journeyOrgsSection" style="display:none;">
          <h4><i class="fa-solid fa-users"></i> Organisasi & kegiatan</h4>
          <ul id="journeyModalOrgs"></ul>
        </div>
        <div class="journey-modal-section" id="journeyCertsSection">
          <h4><i class="fa-solid fa-images"></i> Sertifikat & dokumentasi</h4>
          <div class="journey-modal-gallery" id="journeyModalGallery"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- FULLSCREEN IMAGE VIEWER -->
  <div class="lightbox-fullimg" id="lightboxFullImg">
    <img src="" alt="Full size preview" id="fullImgSrc">
  </div>

  <section id="contact" class="contact-section">
    <p class="section-kicker">MARI TERHUBUNG</p><h2>Punya ide untuk diwujudkan?</h2>
    <p>Diskusikan proyek, kolaborasi, atau pertanyaan tentang karya saya.</p>
    <div class="hero-actions"><a class="hero-cta primary" href="mailto:contact@relv.biz.id">Kirim email <span aria-hidden="true">↗</span></a><a class="hero-cta" href="https://github.com/RelV-Dev" target="_blank" rel="noopener noreferrer">Lihat GitHub</a></div>
  </section>
  <footer>
    <div class="footer-content">
      <a href="#" class="footer-logo">Rel<span>V</span></a>
      <p class="footer-info">
        Pati, Jawa Tengah, Indonesia<br>
        Menghubungkan kode, kreativitas, dan kolaborasi.
      </p>
      <div class="copyright">
        &copy; <?= date("Y") ?> Farel Putra Firmansyah. All rights reserved.
      </div>
    </div>
  </footer>


  <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js" defer></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Init AOS with custom settings
    if (window.AOS) AOS.init({
      duration: 800,
      easing: 'ease-out-cubic',
      once: true,
      offset: 50
    });

    // --- 1. PRELOADER LOGIC (PREMIUM WITH PARTICLES & PROGRESS) ---
    document.getElementById('preloader')?.remove();
    document.body.classList.remove('no-scroll');

    // Initialize immediately: the enclosing callback already runs on DOMContentLoaded.
    (() => {
      // UI Elements
      const chatWidget = document.getElementById('chatWidgetContainer');
      const chatWindow = document.getElementById('chatWindow');
      const chatToggleBtn = document.getElementById('chatToggleBtn');
      const chatCloseBtn = document.getElementById('chatCloseBtn');
      const newChatBtn = document.getElementById('newChatBtn');
      const chatForm = document.getElementById('chatForm');
      const chatInput = document.getElementById('chatInput');
      const chatMessages = document.getElementById('chatMessages');
      const chatSendBtn = document.getElementById('chatSendBtn');
      const typingIndicator = document.getElementById('typingIndicator');
      const statusText = document.getElementById('statusText');
      const statusDot = document.getElementById('statusDot');

      // Config
      const API_URL = "/api/chat.php";
      const API_STATUS_URL = "/api/chat.php";
      const STORAGE_KEY = 'portfolioChatV2';
      let previousSourceIds = [];
      let previousQuestion = '';
      const EXPIRY_TIME = 10 * 60 * 1000; // 10 Menit

      // --- 0. INTRO & STATUS ---
      setTimeout(() => {
        chatWidget.classList.add('show-widget');
        checkApiStatus();
      }, 3000);

      async function checkApiStatus() {
        try {
          const response = await fetch(API_STATUS_URL, { method: 'GET' });
          const data = await response.json();
          if (response.ok && data.ready) {
            statusText.innerText = "Siap dicoba";
            statusDot.classList.add('online');
            chatInput.placeholder = "AI Ready. Ketik pesan...";
          } else { throw new Error(); }
        } catch (e) {
          statusText.innerText = "Belum tersedia";
          statusDot.classList.remove('online');
          statusDot.style.background = "#ff4757";
        }
      }

      // --- 1. TOGGLE CHAT ---
      function toggleChat() {
        chatWindow.classList.toggle('active');
        const open = chatWindow.classList.contains('active');
        chatWindow.inert = !open;
        chatToggleBtn.setAttribute('aria-expanded', String(open));
        if (!open) chatToggleBtn.focus();
        if (chatWindow.classList.contains('active')) {
          setTimeout(() => {
            chatInput.focus();
            scrollToBottom();
          }, 300);
        }
      }
      chatToggleBtn.addEventListener('click', toggleChat);
      document.addEventListener('keydown', e => { if (e.key === 'Escape' && chatWindow.classList.contains('active')) toggleChat(); });
      chatCloseBtn.addEventListener('click', toggleChat);
      newChatBtn.addEventListener('click', () => {
        newChatBtn.style.transform = "rotate(180deg)";
        setTimeout(() => newChatBtn.style.transform = "rotate(0)", 300);
        clearHistory();
      });

      // --- 2. HISTORY ---
      function readHistory() {
        try {
          const data = JSON.parse(localStorage.getItem(STORAGE_KEY));
          if (!data || !Number.isFinite(data.timestamp) || Date.now() - data.timestamp > EXPIRY_TIME || !Array.isArray(data.messages)) return [];
          return data.messages.filter(m => m && ['user','bot'].includes(m.sender) && typeof m.text === 'string').slice(-50);
        } catch { return []; }
      }
      function loadHistory() {
        const messages = readHistory();
        if (messages.length) document.getElementById('welcomeMsg')?.remove();
        messages.forEach(msg => appendMessageUI(msg.sender, msg.text, false));
      }
      function saveHistory(sender, text) {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify({timestamp: Date.now(), messages: [...readHistory(), {sender, text}].slice(-50)})); } catch {}
      }
      function clearHistory() {
        previousSourceIds=[]; previousQuestion='';
        try { localStorage.removeItem(STORAGE_KEY); } catch {}
        chatMessages.innerHTML = `
        <div class="message bot" id="welcomeMsg">
            <img src="https://res.cloudinary.com/dvpq5fsef/image/upload/c_fill,g_auto,w_100,h_100,q_auto,f_auto/v1780844527/dvpyazydsd46jzrvu6bq.webp" alt="Bot" class="bot-avatar">
            <div class="bot-text-content">History dihapus. Mari mulai obrolan baru! 🚀</div>
        </div>`;
      }
      loadHistory();

      // --- 3. ADVANCED FORMATTER (AUTO-CHIP) ---
      function formatText(text) {
        const content = document.createElement('div');
        content.textContent = text;
        return content.innerHTML;
      }
      function appendSources(sources) {
        const message = chatMessages.lastElementChild?.querySelector('.bot-text-content');
        if (!message || !Array.isArray(sources)) return;
        const links = document.createElement('div');
        links.className = 'chat-sources';
        for (const source of sources) {
          if (!/^#(?:home|education|skills|gallery|work-\d+)$/.test(source.url)) continue;
          const link = document.createElement('a');
          link.href = source.url;
          link.textContent = source.title;
          link.className = 'md-link-chip';
          link.addEventListener('click', toggleChat);
          links.appendChild(link);
        }
        message.appendChild(links);
        scrollToBottom();
      }
      document.querySelectorAll('[data-chat-question]').forEach(button => button.addEventListener('click', () => {
        chatInput.value = button.dataset.chatQuestion;
        chatForm.requestSubmit();
      }));
      // --- 4. UI HELPERS ---
      function appendMessageUI(sender, text, save = true) {
        if (sender === 'user' && document.getElementById('welcomeMsg')) document.getElementById('welcomeMsg').remove();

        const msgDiv = document.createElement('div');
        msgDiv.classList.add('message', sender);

        if (sender === 'user') {
          msgDiv.textContent = text;
        } else {
          const avatar = `<img src="https://res.cloudinary.com/dvpq5fsef/image/upload/c_fill,g_auto,w_100,h_100,q_auto,f_auto/v1780844527/dvpyazydsd46jzrvu6bq.webp" alt="Bot" class="bot-avatar">`;
          const content = `<div class="bot-text-content">${formatText(text)}</div>`;
          msgDiv.innerHTML = avatar + content;
        }
        chatMessages.appendChild(msgDiv);
        scrollToBottom();
        if (save) saveHistory(sender, text);
      }

      function scrollToBottom() { chatMessages.scrollTop = chatMessages.scrollHeight; }

      // --- 5. SUBMIT HANDLER ---
      chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const text = chatInput.value.trim();
        if (!text || chatSendBtn.disabled) return;

        appendMessageUI('user', text);
        chatInput.value = '';

        // Animasi
        chatSendBtn.classList.add('flying');
        chatSendBtn.disabled = true;
        newChatBtn.disabled = true;
        chatMessages.setAttribute('aria-busy', 'true');
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 35000);
        typingIndicator.classList.add('active');
        scrollToBottom();

        setTimeout(() => { chatSendBtn.classList.remove('flying'); }, 800);

        try {
          const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text, previous_source_ids: previousSourceIds, previous_question: previousQuestion }),
            signal: controller.signal
          });
          const data = await response.json();

          typingIndicator.classList.remove('active');
          if (!response.ok) throw new Error(data.error || 'Assistant sedang tidak tersedia.');
          if (data.answer) { previousSourceIds=data.source_ids || []; previousQuestion=text; appendMessageUI('bot', data.answer); appendSources(data.sources); if(data.provider_verified) { statusText.textContent='Terhubung'; statusDot.classList.add('online'); } }
          else appendMessageUI('bot', "Maaf, server sedang sibuk.");

        } catch (err) {
          statusText.textContent='Gangguan koneksi'; statusDot.classList.remove('online');
          typingIndicator.classList.remove('active');
          appendMessageUI('bot', err.name === 'AbortError' ? 'Waktu tunggu habis. Silakan coba lagi.' : (err.message || 'Gagal terhubung ke server.'), false);
        } finally {
          clearTimeout(timeout);
          typingIndicator.classList.remove('active');
          chatMessages.setAttribute('aria-busy', 'false');
          newChatBtn.disabled = false;
          chatSendBtn.disabled = false;
          if (chatWindow.classList.contains('active')) chatInput.focus();
        }
      });
    })();

    // Scroll progress + nav scrolled state + hero parallax + reveal observer
    (function initPolishMotion(){
      const bar = document.getElementById('scrollProgress');
      const nav = document.querySelector('nav');
      let ticking = false;
      function onScroll(){
        if(ticking) return;
        ticking = true;
        requestAnimationFrame(()=>{
          const h = document.documentElement;
          const max = h.scrollHeight - h.clientHeight;
          const p = max > 0 ? (window.scrollY / max) * 100 : 0;
          if(bar) bar.style.width = p.toFixed(2) + '%';
          if(nav) nav.classList.toggle('is-scrolled', window.scrollY > 24);
          document.documentElement.style.setProperty('--hero-parallax', Math.min(window.scrollY / 900, 1).toString());
          ticking = false;
        });
      }
      window.addEventListener('scroll', onScroll, {passive:true});
      onScroll();

      // Light reveal untuk skill category (testimonial-card & gallery-card
      // sudah punya entrance animation sendiri — reveal ganda bikin kedip)
      const revealEls = document.querySelectorAll('.skill-category');
      revealEls.forEach(el=> el.classList.add('reveal'));
      const ro = new IntersectionObserver((entries)=>{
        entries.forEach(e=>{
          if(e.isIntersecting){
            e.target.classList.add('in');
            ro.unobserve(e.target);
          }
        });
      }, {threshold: 0.12, rootMargin: '0px 0px -40px 0px'});
      revealEls.forEach(el=> ro.observe(el));

      // Stagger entrance gallery ditangani sendiri oleh showPage() (popIn + delay),
      // guestbook-grid adalah marquee berjalan — keduanya dikecualikan dari stagger-group.
      // Observer ini hanya untuk section titles (blur-to-focus + underline self-draw).
      const titleObserver = new IntersectionObserver((entries)=>{
        entries.forEach(e=>{
          if(e.isIntersecting){
            e.target.classList.add('in-view');
            titleObserver.unobserve(e.target);
          }
        });
      }, {threshold: 0.4});
      document.querySelectorAll('.section-title').forEach(t => titleObserver.observe(t));

      // Hero profile image: parallax tilt halus mengikuti mouse (desktop only)
      const imageWrapper = document.querySelector('.image-wrapper');
      const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
      const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      if(imageWrapper && finePointer && !reducedMotion){
        const hero = document.getElementById('home');
        hero.addEventListener('mousemove', (e) => {
          const rect = imageWrapper.getBoundingClientRect();
          const cx = rect.left + rect.width / 2;
          const cy = rect.top + rect.height / 2;
          // Normalisasi -1..1 dari posisi mouse relatif ke tengah foto
          const nx = Math.max(-1, Math.min(1, (e.clientX - cx) / (rect.width)));
          const ny = Math.max(-1, Math.min(1, (e.clientY - cy) / (rect.height)));
          imageWrapper.style.setProperty('--tilt-x', nx.toFixed(3));
          imageWrapper.style.setProperty('--tilt-y', ny.toFixed(3));
        }, {passive:true});
        hero.addEventListener('mouseleave', () => {
          imageWrapper.style.setProperty('--tilt-x', '0');
          imageWrapper.style.setProperty('--tilt-y', '0');
        });
      }

      // Respect reduced motion: kill heavy parallax & cursor if needed
      if(reducedMotion){
        document.documentElement.style.setProperty('--hero-parallax','0');
      }
    })();

    // --- Typing Animation (Smooth Character-by-Character with CSS) ---
    const words = ["Pegiat teknologi", "Pengembang", "Desainer visual", "Google Student Ambassador"];
    let wordIndex = 0;
    const typingEl = document.querySelector('.typing-text');

    function typeWord(word, callback) {
      typingEl.innerHTML = '';
      const chars = word.split('');
      let charIdx = 0;

      function addChar() {
        if (charIdx >= chars.length) {
          setTimeout(callback, 2000);
          return;
        }
        const span = document.createElement('span');
        span.className = 'typing-char';
        span.textContent = chars[charIdx] === ' ' ? '\u00A0' : chars[charIdx];
        span.style.animationDelay = '0s';
        typingEl.appendChild(span);
        charIdx++;
        setTimeout(addChar, 70 + Math.random() * 40);
      }
      addChar();
    }

    function deleteWord(callback) {
      const charSpans = typingEl.querySelectorAll('.typing-char');
      let idx = charSpans.length - 1;

      function removeChar() {
        if (idx < 0) {
          typingEl.innerHTML = '';
          setTimeout(callback, 300);
          return;
        }
        charSpans[idx].classList.add('deleting');
        const currentIdx = idx;
        setTimeout(() => {
          if (charSpans[currentIdx] && charSpans[currentIdx].parentNode) {
            charSpans[currentIdx].remove();
          }
        }, 150);
        idx--;
        setTimeout(removeChar, 40);
      }
      removeChar();
    }

    function typingLoop() {
      typeWord(words[wordIndex], () => {
        deleteWord(() => {
          wordIndex = (wordIndex + 1) % words.length;
          if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) typingEl.textContent = words[0];
          else typingLoop();
        });
      });
    }

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) typingEl.textContent = words[0];
    else typingLoop();

    // --- Navbar Logic (accessible + anim-safe) ---
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    function setMenu(open){
      navLinks.classList.toggle('active', open);
      menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      menuToggle.setAttribute('aria-label', open ? 'Close navigation menu' : 'Open navigation menu');
      const spans = menuToggle.querySelectorAll('span');
      if (open) {
        spans[0].style.transform = 'rotate(45deg) translate(5px, 6px)';
        spans[1].style.opacity = '0';
        spans[2].style.transform = 'rotate(-45deg) translate(5px, -6px)';
        document.body.style.overflow = 'hidden';
      } else {
        spans[0].style.transform = 'none';
        spans[1].style.opacity = '1';
        spans[2].style.transform = 'none';
        if(!document.getElementById('journeyModal') || document.getElementById('journeyModal').hidden) document.body.style.overflow = '';
      }
    }
    window.addEventListener('resize', () => { if (window.innerWidth > 992 && navLinks.classList.contains('active')) setMenu(false); });
    menuToggle.addEventListener('click', () => setMenu(!navLinks.classList.contains('active')));
    document.querySelectorAll('.nav-links a').forEach(link => {
      link.addEventListener('click', () => setMenu(false));
    });
    document.addEventListener('keydown', (e)=>{
      if(e.key==='Escape' && navLinks.classList.contains('active')) setMenu(false);
    });
    document.addEventListener('click', (e)=>{
      if(navLinks.classList.contains('active') && !navLinks.contains(e.target) && !menuToggle.contains(e.target)) setMenu(false);
    });

    // --- SCROLL LOGIC & FIREFLY ANIMATION (REVISED: ULTRA SMOOTH LERP) ---
    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed');
        }
      });
    }, { threshold: 0.1 });

    document.querySelectorAll('.text-reveal-anim').forEach(el => {
      revealObserver.observe(el);
    });

    // 3. Journey Sticky Scroll Logic (SMOOTH & SLOW)
    const eduSection = document.getElementById('education');
    const journeyProgress = document.getElementById('journeyProgress');
    const journeyItems = document.querySelectorAll('.journey-item');

    // Variabel untuk Linear Interpolation (Lerp)
    let targetProgress = 0;
    let currentProgress = 0;
    let currentProgressPx = 0;
    let isScrolling = false;

    // Fungsi Lerp sederhana
    function lerp(start, end, factor) {
      return start + (end - start) * factor;
    }

    // Loop animasi RequestAnimationFrame agar gerakan firefly halus (tidak patah-patah)
    function animateScroll() {
      if (document.hidden || eduSection.getBoundingClientRect().bottom < 0 || eduSection.getBoundingClientRect().top > innerHeight) { requestAnimationFrame(animateScroll); return; }
      const isMobile = window.innerWidth <= 992;
      const eduContainer = document.getElementById('eduContainer');

      let activeIndex = -1;
      if (isMobile) {
        // Mobile: snap to icon based on viewport position
        const triggerPoint = window.innerHeight * 0.6;
        journeyItems.forEach((item, index) => {
          const rect = item.getBoundingClientRect();
          if (rect.top <= triggerPoint) {
            activeIndex = index;
          }
        });
      } else {
        // Desktop: map scroll percentage (targetProgress) to active index
        const totalItems = journeyItems.length;
        journeyItems.forEach((item, index) => {
          const threshold = (index / (totalItems - 1 || 1)) * 0.9;
          if (targetProgress >= threshold) {
            activeIndex = index;
          }
        });
      }

      // Calculate target pixels
      let targetProgressPx = 0;
      if (activeIndex >= 0 && activeIndex < journeyItems.length) {
        const activeItem = journeyItems[activeIndex];
        const activeIcon = activeItem.querySelector('.journey-icon');
        if (activeIcon && eduContainer) {
          const containerRect = eduContainer.getBoundingClientRect();
          const iconRect = activeIcon.getBoundingClientRect();
          if (isMobile) {
            const iconCenterY = (iconRect.top + iconRect.height / 2) - containerRect.top;
            targetProgressPx = Math.max(0, iconCenterY);
          } else {
            const iconCenterX = (iconRect.left + iconRect.width / 2) - containerRect.left;
            targetProgressPx = iconCenterX;
          }
        }
      }

      // Smooth interpolation using lerp
      currentProgressPx = lerp(currentProgressPx, targetProgressPx, 0.08);

      if (isMobile) {
        journeyProgress.style.width = '3px';
        journeyProgress.style.height = currentProgressPx.toFixed(2) + 'px';
      } else {
        journeyProgress.style.height = '3px';
        journeyProgress.style.width = currentProgressPx.toFixed(2) + 'px';
      }

      // Update active state and visibility of items
      journeyItems.forEach((item, index) => {
        if (index <= activeIndex) {
          item.classList.add('active');
          item.classList.add('visible');
        } else {
          item.classList.remove('active');
        }
      });

      requestAnimationFrame(animateScroll);
    }

    // Jalankan loop animasi
    animateScroll();

    // Event Scroll hanya bertugas menghitung TARGET, bukan mengubah CSS langsung
    window.addEventListener('scroll', () => {
      // Navbar Logic
      let current = '';
      const sections = document.querySelectorAll('section');
      const scrollY = window.scrollY;
      const windowHeight = window.innerHeight;

      sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const isMobile = window.innerWidth <= 992;
        // Logika height untuk section sticky
        const sectionHeight = (section.getAttribute('id') === 'education' && !isMobile) ? windowHeight : section.clientHeight;

        if (scrollY >= (sectionTop - 200)) {
          current = section.getAttribute('id');
        }
      });

      document.querySelectorAll('.nav-links a').forEach(a => {
        a.classList.remove('active');
        if (a.getAttribute('href').includes(current)) {
          a.classList.add('active');
        }
      });

      // Hitung Target Progress untuk Firefly
      const sectionTop = eduSection.offsetTop;
      const sectionTotalHeight = eduSection.offsetHeight;

      let scrollableDistance = sectionTotalHeight - windowHeight;
      if (scrollableDistance <= 0) scrollableDistance = 1;

      let percent = (scrollY - sectionTop) / scrollableDistance;

      // Clamp value 0-1
      if (percent < 0) percent = 0;
      if (percent > 1) percent = 1;

      // Set target untuk diambil alih oleh fungsi animateScroll()
      targetProgress = percent;
    });

    // --- Custom Cursor & Canvas Particle Engine (throttled + respects reduced-motion) ---
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const cursorBulb = document.getElementById('cursorBulb');
    const cursorDot = document.getElementById('cursorDot');
    const cursorCanvas = document.getElementById('cursorCanvas');
    const ctx = cursorCanvas.getContext('2d');
    let particles = [];

    let mouseX = 0;
    let mouseY = 0;
    let bulbX = 0;
    let bulbY = 0;

    function resizeCanvas() {
      cursorCanvas.width = window.innerWidth;
      cursorCanvas.height = window.innerHeight;
    }
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    class Particle {
      constructor(x, y, color) {
        this.x = x;
        this.y = y;
        this.size = Math.random() * 4 + 2;
        this.speedX = (Math.random() - 0.5) * 6;
        this.speedY = (Math.random() - 0.5) * 6;
        this.color = color;
        this.alpha = 1;
        this.decay = Math.random() * 0.015 + 0.01;
      }

      update() {
        this.x += this.speedX;
        this.y += this.speedY;
        this.speedX *= 0.98;
        this.speedY *= 0.98;
        this.alpha -= this.decay;
      }

      draw() {
        ctx.save();
        ctx.globalAlpha = this.alpha;
        ctx.fillStyle = this.color;
        ctx.shadowBlur = 10;
        ctx.shadowColor = this.color;
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();
      }
    }

    function createBurst(x, y) {
      const colors = ['#00f3ff', '#bc13fe', '#00ff9d', '#ff007c', '#ccff00'];
      const count = 15;
      for (let i = 0; i < count; i++) {
        const randomColor = colors[Math.floor(Math.random() * colors.length)];
        particles.push(new Particle(x, y, randomColor));
      }
    }

    document.addEventListener('mousemove', (e) => {
      mouseX = e.clientX;
      mouseY = e.clientY;

      cursorDot.style.left = mouseX + 'px';
      cursorDot.style.top = mouseY + 'px';

      // Spawn low probability trail
      if (Math.random() < 0.15) {
        const colors = ['#00f3ff', '#bc13fe', '#00ff9d'];
        const randomColor = colors[Math.floor(Math.random() * colors.length)];
        particles.push(new Particle(mouseX, mouseY, randomColor));
      }
    });

    document.addEventListener('mousedown', (e) => {
      cursorDot.style.transform = 'translate(-50%, -50%) scale(0.6)';
      cursorBulb.style.opacity = '0.35';
      createBurst(e.clientX, e.clientY);
    });

    document.addEventListener('mouseup', () => {
      cursorDot.style.transform = 'translate(-50%, -50%) scale(1)';
      cursorBulb.style.opacity = '0.15';
    });

    function animateCursor() {
      const easing = 0.1;
      bulbX += (mouseX - bulbX) * easing;
      bulbY += (mouseY - bulbY) * easing;

      cursorBulb.style.left = bulbX + 'px';
      cursorBulb.style.top = bulbY + 'px';

      // Render particles on Canvas
      ctx.clearRect(0, 0, cursorCanvas.width, cursorCanvas.height);
      for (let i = particles.length - 1; i >= 0; i--) {
        particles[i].update();
        if (particles[i].alpha <= 0) {
          particles.splice(i, 1);
        } else {
          particles[i].draw();
        }
      }

      requestAnimationFrame(animateCursor);
    }

    // Skip cursor particle loop entirely saat user prefer reduced motion
    if (window.matchMedia('(hover: hover) and (pointer: fine)').matches && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      animateCursor();
    }

    const clickableElements = document.querySelectorAll('a, button, .journey-card, .skill-category, .gallery-card, input, textarea');

    clickableElements.forEach(el => {
      el.addEventListener('mouseenter', () => {
        cursorDot.classList.add('active-hover');
      });
      el.addEventListener('mouseleave', () => {
        cursorDot.classList.remove('active-hover');
      });
    });

    // --- Gallery Filter + Pagination (with refined animation timing) ---
    const filterBtns = document.querySelectorAll('.filter-btn');
    const galleryItems = document.querySelectorAll('.gallery-card');
    const ITEMS_PER_PAGE = 4;
    let currentPage = 1;
    let currentFilter = 'all';

    function getFilteredItems() {
      return Array.from(galleryItems).filter(item => {
        return currentFilter === 'all' || item.getAttribute('data-category') === currentFilter;
      });
    }

    function renderPagination() {
      const filtered = getFilteredItems();
      const totalPages = Math.ceil(filtered.length / ITEMS_PER_PAGE);
      const container = document.getElementById('galleryPagination');

      if (totalPages <= 1) {
        container.innerHTML = '';
        return;
      }

      // Adaptive window: show max 3 pages on mobile, 5 on desktop
      const isMobile = window.innerWidth <= 480;
      const maxVisible = isMobile ? 3 : 5;

      // Calculate visible range centered on current page
      let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
      let endPage = Math.min(totalPages, startPage + maxVisible - 1);
      startPage = Math.max(1, endPage - maxVisible + 1);

      let html = `<button class="pagination-btn nav-arrow ${currentPage === 1 ? 'disabled' : ''}" data-page="prev" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>`;

      // First page + ellipsis
      if (startPage > 1) {
        html += `<button class="pagination-btn" data-page="1">1</button>`;
        if (startPage > 2) html += `<span class="pagination-info">…</span>`;
      }

      for (let i = startPage; i <= endPage; i++) {
        html += `<button class="pagination-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
      }

      // Last page + ellipsis
      if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += `<span class="pagination-info">…</span>`;
        html += `<button class="pagination-btn" data-page="${totalPages}">${totalPages}</button>`;
      }

      html += `<button class="pagination-btn nav-arrow ${currentPage === totalPages ? 'disabled' : ''}" data-page="next" aria-label="Next"><i class="fas fa-chevron-right"></i></button>`;

      container.innerHTML = html;

      container.querySelectorAll('.pagination-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const page = btn.getAttribute('data-page');
          if (page === 'prev' && currentPage > 1) currentPage--;
          else if (page === 'next' && currentPage < totalPages) currentPage++;
          else if (page !== 'prev' && page !== 'next') currentPage = parseInt(page);
          showPage();
        });
      });
    }

    function showPage() {
      const filtered = getFilteredItems();
      const start = (currentPage - 1) * ITEMS_PER_PAGE;
      const end = start + ITEMS_PER_PAGE;

      galleryItems.forEach(item => {
        item.classList.add('hide');
        item.classList.remove('anim-in', 'anim-out');
      });

      filtered.forEach((item, index) => {
        if (index >= start && index < end) {
          item.classList.remove('hide');
          item.classList.remove('anim-in');
          // Stagger saat masuk: delay berurutan per kartu, reflow agar animasi restart
          item.style.animationDelay = `${Math.min(index % ITEMS_PER_PAGE, 8) * 60}ms`;
          void item.offsetWidth;
          item.classList.add('anim-in');
        }
      });

      renderPagination();
    }

    filterBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentFilter = btn.getAttribute('data-filter');
        currentPage = 1;
        showPage();
      });
    });

    function revealLinkedWork() {
      if (!/^#work-\d+$/.test(location.hash)) return;
      const index = Array.from(galleryItems).findIndex(item => '#'+item.id === location.hash);
      if (index < 0) return;
      currentFilter='all'; currentPage=Math.floor(index / ITEMS_PER_PAGE)+1;
      filterBtns.forEach(btn=>btn.classList.toggle('active',btn.dataset.filter==='all'));
      showPage(); requestAnimationFrame(()=>document.getElementById(location.hash.slice(1))?.scrollIntoView({block:'center'}));
    }
    window.addEventListener('hashchange', revealLinkedWork);
    showPage(); revealLinkedWork();

    // --- TESTIMONIALS: Supabase + Paginated Grid ---
    const SUPABASE_URL = 'https://srkisngeashoaeiwazcr.supabase.co';
    const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InNya2lzbmdlYXNob2FlaXdhemNyIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODA4MjM4NDcsImV4cCI6MjA5NjM5OTg0N30.85xq9c1lbpPrNOBSm8pSZNqwdv2pTh-MWgJtFunC6wI';

    // Singleton Supabase Client - prevents "Multiple GoTrueClient instances" warning
    const supabaseClient = (SUPABASE_URL !== 'YOUR_SUPABASE_URL')
      ? window.supabase?.createClient(SUPABASE_URL, SUPABASE_ANON_KEY)
      : null;

    let allTestimonials = [];
    let currentTestimonialPage = 1;
    const TESTIMONIALS_PER_PAGE = 3;

    function formatRupiah(num) {
      return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function formatDate(dateStr) {
      const d = new Date(dateStr);
      return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    function getCategoryColor(cat) {
      const colors = {
        'Web Development': { bg: 'rgba(0, 243, 255, 0.2)', color: '#00f3ff', border: 'rgba(0, 243, 255, 0.4)' },
        'Design': { bg: 'rgba(188, 19, 254, 0.2)', color: '#bc13fe', border: 'rgba(188, 19, 254, 0.4)' },
        'IoT': { bg: 'rgba(0, 255, 157, 0.2)', color: '#00ff9d', border: 'rgba(0, 255, 157, 0.4)' },
        'Data Science': { bg: 'rgba(255, 165, 0, 0.2)', color: '#ffa500', border: 'rgba(255, 165, 0, 0.4)' },
        'Video Editing': { bg: 'rgba(255, 68, 68, 0.2)', color: '#ff4444', border: 'rgba(255, 68, 68, 0.4)' },
      };
      return colors[cat] || { bg: 'rgba(255,255,255,0.1)', color: '#fff', border: 'rgba(255,255,255,0.2)' };
    }

    async function loadTestimonials() {
      document.getElementById('testimonialEmpty').textContent = 'Belum ada pengalaman klien yang dipublikasikan.';
      try {
        const supabase = supabaseClient;
        const { data, error } = await supabase
          .from('public_testimonials')
          .select('id,customer_name,project_title,category,project_link,demo_video_url,project_proof')
          .order('id', { ascending: false });

        if (error) throw error;

        allTestimonials = data || [];
        const emptyState = document.getElementById('testimonialEmpty');
        const gridContainer = document.querySelector('.testimonials-grid-container');

        if (allTestimonials.length === 0) {
          if (emptyState) emptyState.style.display = 'block';
          if (gridContainer) gridContainer.style.display = 'none';
          return;
        }

        if (emptyState) emptyState.style.display = 'none';
        if (gridContainer) gridContainer.style.display = 'block';

        currentTestimonialPage = 1;
        renderTestimonialPage(currentTestimonialPage);
      } catch (err) {
        console.warn('Testimonials load failed:', err.message);
        const emptyState = document.getElementById('testimonialEmpty');
        const gridContainer = document.querySelector('.testimonials-grid-container');
        if (emptyState) { emptyState.style.display = 'block'; emptyState.replaceChildren(document.createTextNode('Testimonial gagal dimuat. ')); const retry=document.createElement('button'); retry.textContent='Coba lagi'; retry.onclick=loadTestimonials; emptyState.appendChild(retry); }
        if (gridContainer) gridContainer.style.display = 'none';
      }
    }

    function renderTestimonialPage(pageNumber) {
      const grid = document.getElementById('testimonialGrid');
      if (!grid) return;

      grid.innerHTML = '';
      const startIdx = (pageNumber - 1) * TESTIMONIALS_PER_PAGE;
      const endIdx = startIdx + TESTIMONIALS_PER_PAGE;
      const pageData = allTestimonials.slice(startIdx, endIdx);

      pageData.forEach((t, index) => {
        const catColor = getCategoryColor(t.category);
        const card = document.createElement('div');
        card.className = 'testimonial-card';
        card.testimonialData = t;
        card.style.animationDelay = `${index * 0.1}s`;

        card.innerHTML = `
          <div class="testimonial-img-wrapper">
            <img src="${escapeHtml(safeUrl(t.project_proof) || fallbackImage)}" alt="${escapeHtml(t.project_title)} - Project proof" loading="lazy">
            <div class="testimonial-img-overlay">
              <span class="testimonial-category-badge" style="background:${catColor.bg};color:${catColor.color};border-color:${catColor.border}">${escapeHtml(t.category)}</span>
            </div>
          </div>
          <div class="testimonial-card-body">
            <span class="testimonial-customer"><i class="fas fa-user"></i> ${escapeHtml(t.customer_name)}</span>
            <h3 class="testimonial-title">${escapeHtml(t.project_title)}</h3>
            <div class="testimonial-meta">
            </div>
            <button class="testimonial-view-btn" onclick="openLightbox(this.closest('.testimonial-card'))">
              <i class="fas fa-eye"></i> Lihat detail
            </button>
          </div>
        `;

        grid.appendChild(card);

        // Attach interactive 3D Tilt & cursor glow if hover is supported
        if (window.matchMedia('(hover: hover)').matches) {
          setupCardInteractions(card);
        }
        // staggered reveal feels more premium
        card.classList.remove('in');
        void card.offsetWidth;
        card.classList.add('reveal','in');
      });

      renderPaginationControls(pageNumber);
    }

    function setupCardInteractions(card) {
      card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        card.style.setProperty('--mouse-x', `${x}px`);
        card.style.setProperty('--mouse-y', `${y}px`);

        const width = rect.width;
        const height = rect.height;
        const centerX = width / 2;
        const centerY = height / 2;

        const rotateX = ((centerY - y) / centerY) * 8; // max 8deg
        const rotateY = ((x - centerX) / centerX) * 8; // max 8deg

        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
      });

      card.addEventListener('mouseleave', () => {
        card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
        card.style.setProperty('--mouse-x', '0px');
        card.style.setProperty('--mouse-y', '0px');
      });
    }

    function renderPaginationControls(currentPage) {
      const paginationContainer = document.getElementById('testimonialPagination');
      if (!paginationContainer) return;

      paginationContainer.innerHTML = '';
      const totalPages = Math.ceil(allTestimonials.length / TESTIMONIALS_PER_PAGE);

      if (totalPages <= 1) return;

      // Prev Button
      const prevBtn = document.createElement('button');
      prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
      prevBtn.disabled = currentPage === 1;
      prevBtn.addEventListener('click', () => {
        currentTestimonialPage = currentPage - 1;
        renderTestimonialPage(currentTestimonialPage);
        document.getElementById('testimonials').scrollIntoView({ behavior: 'smooth' });
      });
      paginationContainer.appendChild(prevBtn);

      // Page Numbers
      for (let i = 1; i <= totalPages; i++) {
        const pageBtn = document.createElement('button');
        pageBtn.textContent = i;
        if (i === currentPage) {
          pageBtn.classList.add('active');
        }
        pageBtn.addEventListener('click', () => {
          currentTestimonialPage = i;
          renderTestimonialPage(currentTestimonialPage);
          document.getElementById('testimonials').scrollIntoView({ behavior: 'smooth' });
        });
        paginationContainer.appendChild(pageBtn);
      }

      // Next Button
      const nextBtn = document.createElement('button');
      nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
      nextBtn.disabled = currentPage === totalPages;
      nextBtn.addEventListener('click', () => {
        currentTestimonialPage = currentPage + 1;
        renderTestimonialPage(currentTestimonialPage);
        document.getElementById('testimonials').scrollIntoView({ behavior: 'smooth' });
      });
      paginationContainer.appendChild(nextBtn);
    }


    const fallbackImage = 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="640" height="360"><rect width="640" height="360" fill="#141d28"/><text x="50%" y="50%" text-anchor="middle" fill="#9adfd8" font-size="24">RelV · Proyek</text></svg>');
    function safeUrl(value) { try { const u = new URL(value); return ['https:', 'http:'].includes(u.protocol) ? u.href : ''; } catch { return ''; } }
    document.addEventListener('click', event => { const target = event.target.closest('[data-full-image]'); if (target) openFullImg(target.dataset.fullImage); });
    // --- GUESTBOOK: Supabase Realtime/Fetch & Cooldown ---
    let allGuestbookMessages = [];
    const GUESTBOOK_COOLDOWN_MS = 60 * 1000; // 1 minute

    function escapeHtml(string) {
      const matchHtmlRegExp = /["'&<>]/;
      const str = '' + string;
      const match = matchHtmlRegExp.exec(str);
      if (!match) return str;
      let escape;
      let html = '';
      let index = 0;
      let lastIndex = 0;
      for (index = match.index; index < str.length; index++) {
        switch (str.charCodeAt(index)) {
          case 34: escape = '&quot;'; break; // "
          case 38: escape = '&amp;'; break;  // &
          case 39: escape = '&#39;'; break;  // '
          case 60: escape = '&lt;'; break;   // <
          case 62: escape = '&gt;'; break;   // >
          default: continue;
        }
        if (lastIndex !== index) {
          html += str.substring(lastIndex, index);
        }
        lastIndex = index + 1;
        html += escape;
      }
      return lastIndex !== index ? html + str.substring(lastIndex, index) : html;
    }

    function showToast(message, type = 'success') {
      let container = document.getElementById('toastContainer');
      if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.style.cssText = `
          position: fixed;
          bottom: 30px;
          right: 30px;
          z-index: 100000;
          display: flex;
          flex-direction: column;
          gap: 10px;
          pointer-events: none;
        `;
        document.body.appendChild(container);
      }

      const toast = document.createElement('div');
      toast.style.cssText = `
        padding: 15px 25px;
        border-radius: 12px;
        color: #fff;
        font-family: var(--font-main);
        font-size: 0.9rem;
        font-weight: 600;
        background: rgba(20, 20, 25, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid ${type === 'success' ? 'rgba(0, 255, 157, 0.4)' : 'rgba(255, 68, 68, 0.4)'};
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), 0 0 15px ${type === 'success' ? 'rgba(0, 255, 157, 0.2)' : 'rgba(255, 68, 68, 0.2)'};
        transform: translateY(20px);
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        align-items: center;
        gap: 10px;
        pointer-events: auto;
      `;

      const icon = type === 'success'
        ? '<i class="fas fa-check-circle" style="color: #00ff9d;"></i>'
        : '<i class="fas fa-exclamation-circle" style="color: #ff4444;"></i>';

      toast.innerHTML = `${icon} <span>${escapeHtml(message)}</span>`;
      container.appendChild(toast);

      // Trigger transition
      setTimeout(() => {
        toast.style.transform = 'translateY(0)';
        toast.style.opacity = '1';
      }, 50);

      // Remove after 4s
      setTimeout(() => {
        toast.style.transform = 'translateY(-20px)';
        toast.style.opacity = '0';
        setTimeout(() => {
          toast.remove();
        }, 400);
      }, 4000);
    }

    // Star Rating interactions in Modal Form
    const starBtns = document.querySelectorAll('#starRatingWrapper .star-btn');
    const guestbookRatingInput = document.getElementById('guestbookRating');

    starBtns.forEach(btn => {
      btn.addEventListener('mouseover', () => {
        const hoverVal = parseInt(btn.getAttribute('data-value'), 10);
        highlightStars(hoverVal);
      });

      btn.addEventListener('mouseout', () => {
        const selectedVal = parseInt(guestbookRatingInput.value, 10);
        highlightStars(selectedVal);
      });

      btn.addEventListener('click', () => {
        const clickVal = parseInt(btn.getAttribute('data-value'), 10);
        guestbookRatingInput.value = clickVal;
        highlightStars(clickVal);
      });
    });

    function highlightStars(count) {
      starBtns.forEach(btn => {
        const val = parseInt(btn.getAttribute('data-value'), 10);
        if (val <= count) {
          btn.classList.add('active');
          btn.classList.remove('far');
          btn.classList.add('fas');
        } else {
          btn.classList.remove('active');
          btn.classList.remove('fas');
          btn.classList.add('far');
        }
      });
    }

    // Set default stars on load
    highlightStars(5);

    let marqueeAnimationId = null;
    let marqueePosX = 0;
    let marqueeHalfWidth = 0;
    let isMarqueeHovered = false;

    function renderStars(rating) {
      const fullStars = '<i class="fas fa-star"></i>'.repeat(rating);
      const emptyStars = '<i class="far fa-star"></i>'.repeat(5 - rating);
      return `<div class="guestbook-card-stars">${fullStars}${emptyStars}</div>`;
    }

    async function loadGuestbook() {
      document.getElementById('guestbookEmpty').textContent = 'Belum ada pesan. Jadilah yang pertama menulis!';
      try {
        const supabase = supabaseClient;
        const { data, error } = await supabase
          .from('guestbook')
          .select('*')
          .eq('is_approved', true)
          .order('created_at', { ascending: false });

        if (error) throw error;

        allGuestbookMessages = data || [];
        const grid = document.getElementById('guestbookGrid');
        const emptyState = document.getElementById('guestbookEmpty');

        if (!grid) return;

        // Stop any running marquee animation
        if (marqueeAnimationId) {
          cancelAnimationFrame(marqueeAnimationId);
          marqueeAnimationId = null;
        }

        grid.innerHTML = '';
        if (allGuestbookMessages.length === 0) {
          if (emptyState) emptyState.style.display = 'block';
          return;
        }

        if (emptyState) emptyState.style.display = 'none';

        allGuestbookMessages.forEach((msg) => {
          const card = document.createElement('div');
          card.className = 'guestbook-card';

          const dateStr = new Date(msg.created_at).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
          });

          card.innerHTML = `
            <div class="guestbook-card-header">
              <div class="guestbook-card-info">
                <span class="guestbook-card-name">${escapeHtml(msg.name)}</span>
                ${renderStars(msg.rating || 5)}
              </div>
              <span class="guestbook-card-date">${dateStr}</span>
            </div>
            <p class="guestbook-card-msg">${escapeHtml(msg.message)}</p>
          `;
          grid.appendChild(card);
        });

        // Initialize Marquee if there are elements to scroll
        if (allGuestbookMessages.length > 0) {
          // Clone all elements once to make loop seamless
          const originalCards = Array.from(grid.children);
          originalCards.forEach(card => {
            const clone = card.cloneNode(true);
            clone.setAttribute('aria-hidden','true'); clone.inert=true;
            grid.appendChild(clone);
          });

          // Calculate half width of track
          setTimeout(() => {
            marqueeHalfWidth = grid.scrollWidth / 2;
            marqueePosX = 0;
            startMarqueeLoop();
          }, 100);
        }

      } catch (err) {
        console.warn('Guestbook load failed:', err.message);
        const emptyState = document.getElementById('guestbookEmpty');
        if (emptyState) { emptyState.style.display = 'block'; emptyState.replaceChildren(document.createTextNode('Pesan gagal dimuat. ')); const retry=document.createElement('button'); retry.textContent='Coba lagi'; retry.onclick=loadGuestbook; emptyState.appendChild(retry); }
      }
    }

    let marqueePaused = false;
    const pauseGuestbook = document.getElementById('pauseGuestbook');
    pauseGuestbook.addEventListener('click', () => { marqueePaused=!marqueePaused; pauseGuestbook.setAttribute('aria-pressed', String(marqueePaused)); pauseGuestbook.textContent=marqueePaused?'Lanjutkan gerakan':'Jeda gerakan'; });
    function startMarqueeLoop() {
      const grid = document.getElementById('guestbookGrid');
      if (!grid || marqueeHalfWidth === 0) return;

      const loop = () => {
        if (!isMarqueeHovered && !marqueePaused && !document.hidden && !window.matchMedia('(prefers-reduced-motion: reduce)').matches && !grid.contains(document.activeElement) && grid.getBoundingClientRect().bottom > 0 && grid.getBoundingClientRect().top < innerHeight) {
          marqueePosX -= 0.8; // Speed in pixels per frame
          if (Math.abs(marqueePosX) >= marqueeHalfWidth) {
            marqueePosX = 0;
          }
          grid.style.transform = `translate3d(${marqueePosX}px, 0, 0)`;
        }
        marqueeAnimationId = requestAnimationFrame(loop);
      };

      marqueeAnimationId = requestAnimationFrame(loop);

      // Attach hover hooks
      grid.removeEventListener('mouseenter', onMarqueeEnter);
      grid.removeEventListener('mouseleave', onMarqueeLeave);
      grid.addEventListener('mouseenter', onMarqueeEnter);
      grid.addEventListener('mouseleave', onMarqueeLeave);
    }

    function onMarqueeEnter() {
      isMarqueeHovered = true;
    }

    function onMarqueeLeave() {
      isMarqueeHovered = false;
    }

    // Modal Control
    const guestbookModalOverlay = document.getElementById('guestbookModalOverlay');
    const openGuestbookModalBtn = document.getElementById('openGuestbookModalBtn');
    const closeGuestbookModalBtn = document.getElementById('closeGuestbookModalBtn');
    const guestbookForm = document.getElementById('guestbookForm');
    const guestbookSubmitBtn = document.getElementById('guestbookSubmitBtn');

    function checkCooldown() {
      let lastSent; try { lastSent = localStorage.getItem('guestbook_last_sent'); } catch { return 0; }
      if (lastSent) {
        const remaining = parseInt(lastSent, 10) - Date.now();
        if (remaining > 0) {
          return Math.ceil(remaining / 1000);
        }
      }
      return 0;
    }

    function updateSubmitButtonState() {
      const cooldownSecs = checkCooldown();
      if (cooldownSecs > 0) {
        guestbookSubmitBtn.disabled = true;
        guestbookSubmitBtn.innerHTML = `<span>Tunggu ${cooldownSecs}s...</span>`;
        return false;
      } else {
        guestbookSubmitBtn.disabled = false;
        guestbookSubmitBtn.innerHTML = '<span>Kirim Pesan</span>';
        return true;
      }
    }

    let cooldownTimer = null;
    function startCooldownTimer() {
      if (cooldownTimer) clearInterval(cooldownTimer);
      updateSubmitButtonState();
      cooldownTimer = setInterval(() => {
        const active = updateSubmitButtonState();
        if (active) clearInterval(cooldownTimer);
      }, 1000);
    }

    if (openGuestbookModalBtn) {
      openGuestbookModalBtn.addEventListener('click', () => {
        if (guestbookModalOverlay) {
          guestbookModalOverlay.classList.add('active');
          document.body.style.overflow = 'hidden';
          startCooldownTimer();
        }
      });
    }

    function closeGuestbookModal() {
      if (guestbookModalOverlay) {
        guestbookModalOverlay.classList.remove('active');
        document.body.style.overflow = '';
        if (cooldownTimer) clearInterval(cooldownTimer);
      }
    }

    if (closeGuestbookModalBtn) {
      closeGuestbookModalBtn.addEventListener('click', closeGuestbookModal);
    }

    if (guestbookModalOverlay) {
      guestbookModalOverlay.addEventListener('click', (e) => {
        if (e.target === guestbookModalOverlay) closeGuestbookModal();
      });
    }

    if (guestbookForm) {
      guestbookForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (checkCooldown() > 0) {
          showToast('Silakan tunggu cooldown berakhir sebelum mengirim pesan lagi!', 'error');
          return;
        }

        const nameInput = document.getElementById('guestbookName');
        const messageInput = document.getElementById('guestbookMessage');

        const name = nameInput.value.trim();
        const message = messageInput.value.trim();

        if (!name || !message) {
          showToast('Nama dan pesan wajib diisi!', 'error');
          return;
        }

        guestbookSubmitBtn.disabled = true;
        guestbookSubmitBtn.innerHTML = '<span>Mengirim...</span>';

        try {
          const supabase = supabaseClient;
          const response = await fetch('/api/guestbook.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({name, message, rating:parseInt(guestbookRatingInput.value,10), website:document.getElementById('guestbookWebsite').value}), signal:AbortSignal.timeout(15000)});
          const result = await response.json();
          if (!response.ok) throw new Error(result.error || 'Pesan belum tersimpan.');

          // Set Cooldown
          try { localStorage.setItem('guestbook_last_sent', (Date.now() + GUESTBOOK_COOLDOWN_MS).toString()); } catch {}

          showToast('Pesan terkirim! Sedang menunggu moderasi Admin.', 'success');

          // Reset form
          guestbookForm.reset();
          highlightStars(5);
          guestbookRatingInput.value = "5";
          closeGuestbookModal();

        } catch (err) {
          console.error('Submit guestbook failed:', err);
          showToast('Gagal mengirim pesan: ' + err.message, 'error');
          updateSubmitButtonState();
        }
      });
    }

    // --- INTERACTIVE JOURNEY MODAL ---
    const JOURNEY_DATA = <?= json_encode(json_decode(file_get_contents(__DIR__ . '/../data/journey.json'), true), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    const journeyModal = document.getElementById('journeyModal');
    const journeyModalOverlay = document.getElementById('journeyModalOverlay');
    const journeyModalClose = document.getElementById('journeyModalClose');

    function openJourneyModal(periodKey) {
      const data = JOURNEY_DATA[periodKey];
      if (!data) return;

      document.getElementById('journeyModalIcon').innerHTML = data.icon;
      document.getElementById('journeyModalYear').textContent = data.year;
      document.getElementById('journeyModalTitle').textContent = data.title;

      // Achievements
      const achContainer = document.getElementById('journeyModalAchievements');
      achContainer.innerHTML = data.achievements.map(ach => `<li>${escapeHtml(ach)}</li>`).join('');

      // Organizations
      const orgsSection = document.getElementById('journeyOrgsSection');
      const orgsContainer = document.getElementById('journeyModalOrgs');
      if (data.orgs && data.orgs.length > 0) {
        orgsSection.style.display = 'block';
        orgsContainer.innerHTML = data.orgs.map(org => `<li>${escapeHtml(org)}</li>`).join('');
      } else {
        orgsSection.style.display = 'none';
        orgsContainer.innerHTML = '';
      }

      // Gallery / Certificates
      const galleryContainer = document.getElementById('journeyModalGallery');
      const certsSection = document.getElementById('journeyCertsSection');

      if (data.certs && data.certs.length > 0) {
        certsSection.style.display = 'block';
        galleryContainer.innerHTML = data.certs.map(cert => {
          // URL kosong = gambar belum di-upload -> render placeholder, bukan img rusak
          if (!cert.url) {
            return `
              <div class="journey-modal-gallery-item cert-placeholder">
                <i class="fas fa-image"></i>
                <div class="journey-modal-gallery-item-title">${escapeHtml(cert.title)}</div>
              </div>
            `;
          }
          return `
            <div class="journey-modal-gallery-item" data-full-image="${escapeHtml(safeUrl(cert.url))}" role="button" tabindex="0" aria-label="Perbesar sertifikat">
              <img src="${escapeHtml(safeUrl(cert.url))}" alt="${escapeHtml(cert.title)}" loading="lazy">
              <div class="journey-modal-gallery-item-title">${escapeHtml(cert.title)}</div>
            </div>
          `;
        }).join('');
      } else {
        certsSection.style.display = 'none';
        galleryContainer.innerHTML = '';
      }

      journeyModal.hidden = false;
      document.body.style.overflow = 'hidden'; // Lock background scrolling
    }

    function closeJourneyModal() {
      journeyModal.hidden = true;
      document.body.style.overflow = ''; // Unlock background scrolling
    }

    // Attach timeline click events
    document.querySelectorAll('.journey-card[data-period]').forEach(card => {
      card.addEventListener('click', () => {
        const period = card.getAttribute('data-period');
        openJourneyModal(period);
      });
    });

    journeyModalClose.addEventListener('click', closeJourneyModal);
    journeyModalOverlay.addEventListener('click', closeJourneyModal);

    // --- LIGHTBOX MODAL ---
    function openLightbox(card) {
      const t = card.testimonialData;
      const overlay = document.getElementById('lightboxOverlay');
      const title = document.getElementById('lightboxTitle');
      const body = document.getElementById('lightboxBody');
      const catColor = getCategoryColor(t.category);

      title.textContent = t.project_title;

      body.innerHTML = `
        <div class="lightbox-images">
          ${safeUrl(t.project_proof) ? `
          <div class="lightbox-img-item" data-full-image="${escapeHtml(safeUrl(t.project_proof))}" role="button" tabindex="0" aria-label="Perbesar gambar proyek">
            <img src="${escapeHtml(safeUrl(t.project_proof) || fallbackImage)}" alt="Project proof" loading="lazy">
            <span class="lightbox-img-label">Project Proof</span>
          </div>` : ''}
        </div>

        <div class="lightbox-details">
          <div class="lightbox-detail-item">
            <div class="lightbox-detail-label">Customer</div>
            <div class="lightbox-detail-value">${escapeHtml(t.customer_name)}</div>
          </div>
          <div class="lightbox-detail-item">
            <div class="lightbox-detail-label">Category</div>
            <div class="lightbox-detail-value" style="color:${catColor.color}">${escapeHtml(t.category)}</div>
          </div>
        </div>

        ${(t.project_link || t.demo_video_url) ? `
          <div class="lightbox-links">
            ${safeUrl(t.project_link) ? `<a href="${escapeHtml(safeUrl(t.project_link))}" target="_blank" rel="noopener" class="lightbox-link-btn project"><i class="fas fa-external-link-alt"></i> Buka proyek</a>` : ''}
            ${safeUrl(t.demo_video_url) ? `<a href="${escapeHtml(safeUrl(t.demo_video_url))}" target="_blank" rel="noopener" class="lightbox-link-btn video"><i class="fab fa-youtube"></i> Lihat demo</a>` : ''}
          </div>
        ` : ''}
      `;

      overlay.classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
      document.getElementById('lightboxOverlay').classList.remove('active');
      document.body.style.overflow = '';
    }

    document.getElementById('lightboxClose').addEventListener('click', closeLightbox);
    document.getElementById('lightboxOverlay').addEventListener('click', (e) => {
      if (e.target === e.currentTarget) closeLightbox();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        closeLightbox();
        closeFullImg();
      }
    });

    // --- FULLSCREEN IMAGE VIEWER ---
    function openFullImg(url) {
      const viewer = document.getElementById('lightboxFullImg');
      document.getElementById('fullImgSrc').src = safeUrl(url);
      viewer.classList.add('active');
    }

    function closeFullImg() {
      document.getElementById('lightboxFullImg').classList.remove('active');
      document.getElementById('fullImgSrc').src = '';
    }

    document.getElementById('lightboxFullImg').addEventListener('click', closeFullImg);

    // Load testimonials on DOM ready
    if (SUPABASE_URL !== 'YOUR_SUPABASE_URL') {
      loadTestimonials();
      loadGuestbook();
    } else {
      console.warn('Supabase credentials not configured. Replace YOUR_SUPABASE_URL and YOUR_SUPABASE_ANON_KEY in index.php');
      const emptyState = document.getElementById('testimonialEmpty');
      const gridContainer = document.querySelector('.testimonials-grid-container');
      if (emptyState) emptyState.style.display = 'block';
      if (gridContainer) gridContainer.style.display = 'none';

      const guestbookEmpty = document.getElementById('guestbookEmpty');
      const guestbookSection = document.getElementById('guestbook');
      if (guestbookEmpty) guestbookEmpty.style.display = 'block';
      if (guestbookSection) {
        const grid = guestbookSection.querySelector('.guestbook-grid');
        if (grid) grid.style.display = 'none';
      }
    }

    // --------------------------------------------------------
    // --- NEW: GITHUB 3D GRAPH VISUALIZATION & FETCH ---
    // --------------------------------------------------------
    const githubScriptLoads = new Map();
    let githubLoadPromise;
    function loadGithubScript(src) {
      if (!githubScriptLoads.has(src)) {
        const pending = new Promise((resolve,reject) => {
          const script=document.createElement('script');
          const fail=()=>{clearTimeout(timeout);script.remove();reject(new Error('Dependency unavailable'));};
          const timeout=setTimeout(fail,10000);
          script.src=src;
          script.onload=()=>{clearTimeout(timeout);resolve();};
          script.onerror=fail;
          document.head.appendChild(script);
        }).catch(error=>{githubScriptLoads.delete(src);throw error;});
        githubScriptLoads.set(src,pending);
      }
      return githubScriptLoads.get(src);
    }
    function loadGithubDependencies() {
      if (!githubLoadPromise) githubLoadPromise = (async () => {
        const base='/assets/three-r128/';
        await Promise.all([
          loadGithubScript('/github-scene.js'),
          (async()=>{
            await loadGithubScript(base+'three.min.js');
            await loadGithubScript(base+'OrbitControls.js');
          })()
        ]);
      })().catch(error=>{githubLoadPromise=null;throw error;});
      return githubLoadPromise;
    }
    const githubContainer=document.getElementById('github-3d-canvas');
    const githubLoadButton=document.createElement('button');
    const githubLoadingStatus=document.createElement('p');
    githubLoadButton.className='hero-cta'; githubLoadButton.textContent='Muat visualisasi interaktif';
    githubLoadingStatus.setAttribute('role','status');
    githubContainer.replaceChildren(githubLoadButton);
    let githubInitializing=false, githubInitialized=false;
    githubLoadButton.addEventListener('click', initGithub3D);
    // Begin downloading before the visitor reaches the canvas. Respect data/motion preferences.
    if (!navigator.connection?.saveData && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      const githubPreloadObserver=new IntersectionObserver(entries=>{
        if (entries.some(entry=>entry.isIntersecting)) { githubPreloadObserver.disconnect(); initGithub3D(); }
      }, {rootMargin:'600px 0px',threshold:0});
      githubPreloadObserver.observe(githubContainer);
    }
    async function initGithub3D() {
      if (githubInitializing || githubInitialized) return;
      githubInitializing=true; githubLoadButton.disabled=true;
      githubContainer.setAttribute('aria-busy','true');
      githubLoadingStatus.className='gh-loading';
      githubLoadingStatus.textContent='Menyiapkan orbit kontribusi…';
      githubContainer.replaceChildren(githubLoadingStatus);
      try {
        // Scene creation does not wait for the remote statistics API.
        const fetchData=async()=>{
          try {
            const cached=JSON.parse(sessionStorage.getItem('relv-github-v1') || 'null');
            if (cached && Date.now()-cached.time < 300000 && Array.isArray(cached.data?.contributionGraph)) return cached.data;
          } catch (_) { /* Storage may be disabled. */ }
          const response=await fetch('https://github-stats-api-tau.vercel.app/api/github-stats?username=relv-dev', {signal:AbortSignal.timeout(10000)});
          if (!response.ok) throw new Error('GitHub data unavailable');
          const data=await response.json();
          if (!Array.isArray(data.contributionGraph)) throw new Error('Invalid GitHub data');
          try { sessionStorage.setItem('relv-github-v1',JSON.stringify({time:Date.now(),data})); } catch (_) {}
          return data;
        };
        const pendingData=fetchData().then(data=>({data}),error=>({error}));
        await loadGithubDependencies();
        window.PortfolioGithubScene.mount(githubContainer, {contributionGraph:[],pending:true});
        const status=document.getElementById('gh-data-status');
        const retry=document.getElementById('gh-data-retry');
        const applyData=result=>{
          if (result.error) { status.textContent='Data GitHub belum tersedia. Semesta profil tetap bisa dijelajahi.'; retry.hidden=false; return; }
          const data=result.data;
          document.getElementById('gh-username').textContent=`@${data.username || 'RelV-Dev'}`;
          for (const [id,key] of [['gh-repos','totalRepos'],['gh-commits','totalCommits'],['gh-contributions','contributionsCurrentYear']]) {
            document.getElementById(id).textContent=Number.isFinite(Number(data[key])) ? Number(data[key]).toLocaleString('id-ID') : '—';
          }
          githubContainer._githubScene?.dispose();
          window.PortfolioGithubScene.mount(githubContainer, data);
          status.textContent=data.contributionGraph.length ? 'Data GitHub siap · pilih planet atau hari kontribusi' : 'Belum ada data kontribusi dari GitHub.';
          retry.hidden=true;
        };
        pendingData.then(applyData);
        retry.onclick=async()=>{
          retry.disabled=true; status.textContent='Memuat ulang data GitHub…';
          try { applyData({data:await fetchData()}); } catch(error) { applyData({error}); }
          finally { retry.disabled=false; }
        };
        githubInitialized=true;
      } catch(error) {
        console.warn('GitHub visualization unavailable:',error.message);
        githubLoadingStatus.textContent='Visualisasi belum berhasil dimuat.';
        githubLoadButton.textContent='Coba lagi'; githubLoadButton.disabled=false;
        githubContainer.replaceChildren(githubLoadingStatus,githubLoadButton);
      } finally {
        githubInitializing=false; githubContainer.setAttribute('aria-busy','false');
      }
    }

    // Expose functions globally for inline HTML onclick handlers
    window.openLightbox = openLightbox;
    window.openFullImg = openFullImg;
    window.closeFullImg = closeFullImg;
    window.openJourneyModal = openJourneyModal;
    window.closeJourneyModal = closeJourneyModal;
  });
</script>
</body>

</html>
