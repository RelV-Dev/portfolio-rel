<?php
/** Only owner-authored public page sections become assistant knowledge. */
function portfolio_sources(): array {
    $html = file_get_contents(__DIR__ . '/../api/index.php');
    $html = preg_replace('/<\?php[\s\S]*?\?>/', '', $html);
    $doc = new DOMDocument();
    @$doc->loadHTML('<?xml encoding="UTF-8">' . $html);
    $xpath = new DOMXPath($doc);
    $sources = [];
    $queries = [
        'profile' => ['//*[@id="home"]//h1 | //*[@id="home"]//*[contains(@class,"hero-description")]', 'Profil', '#home'],
        'journey' => ['//*[@id="education"]//*[contains(concat(" ",normalize-space(@class)," ")," journey-card ")]', 'Pendidikan & pengalaman', '#education'],
        'skills' => ['//*[@id="skills"]//*[contains(concat(" ",normalize-space(@class)," ")," skill-category ")]', 'Keahlian', '#skills'],
        'contact' => ['//*[@id="home"]//*[contains(@class,"social-links")]//a', 'Kontak', '#home'],
    ];
    foreach ($queries as $prefix => [$query, $title, $url]) {
        foreach ($xpath->query($query) as $i => $node) {
            $text = $prefix === 'contact' ? $node->getAttribute('aria-label') . ': ' . $node->getAttribute('href') : $node->textContent;
            $text = trim(preg_replace('/\s+/u', ' ', str_replace('Click for details', '', $text)));
            if ($text !== '') $sources[$prefix . $i] = ['title' => $title, 'text' => $text, 'url' => $url];
        }
    }
    $journey = json_decode(file_get_contents(__DIR__ . '/../data/journey.json'), true);
    foreach ($journey as $key => $entry) {
        foreach (array_merge($entry['achievements'] ?? [], $entry['orgs'] ?? [], array_column($entry['certs'] ?? [], 'title')) as $i => $text) {
            $sources['journey_detail_'.$key.'_'.$i] = ['title'=>$entry['title'], 'text'=>$entry['title'].' ('.$entry['year'].'): '.$text, 'url'=>'#education'];
        }
    }
    require_once __DIR__ . '/public-data.php';
    foreach (portfolio_gallery() ?? [] as $item) {
        $id = (int)$item['id'];
        $sources['gallery'.$id] = ['title'=>$item['title'], 'text'=>mb_substr(trim(strip_tags($item['title'].' — '.($item['description'] ?? ''))),0,1800), 'url'=>'#work-'.$id];
    }
    return $sources;
}

function portfolio_answer(array $selection, array $sources): array {
    $ids = $selection['source_ids'] ?? [];
    if (!is_array($ids) || count($ids) > 3) $ids = [];
    $chosen = [];
    foreach ($ids as $id) {
        if (!is_string($id) || !isset($sources[$id])) return portfolio_answer([], []);
        $chosen[$id] = $sources[$id];
    }
    if (!$chosen) return ['answer' => 'Informasi itu belum tersedia di portofolio ini. Saya hanya dapat membantu tentang profil, pendidikan, keahlian, galeri, dan kontak Farel.', 'sources' => [], 'source_ids' => []];
    // Never display model prose: even an injected model response cannot introduce outside facts.
    return ['answer' => "Berikut informasi yang tercantum di portofolio:\n\n" . implode("\n\n", array_column($chosen, 'text')), 'source_ids' => array_keys($chosen), 'sources' => array_values(array_map(fn($s) => ['title' => $s['title'], 'url' => $s['url']], $chosen))];
}
