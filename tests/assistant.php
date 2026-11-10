<?php
require __DIR__ . '/../lib/portfolio-assistant.php';
function check($condition, $label) { if (!$condition) throw new RuntimeException($label); echo "PASS $label\n"; }
$sources = ['profile0' => ['title'=>'Profil', 'text'=>'Farel Putra Firmansyah', 'url'=>'#home']];
$r = portfolio_answer(['source_ids'=>['profile0'], 'answer'=>'Invented fact'], $sources);
check(strpos($r['answer'], 'Farel Putra Firmansyah') !== false, 'Known source quoted');
check(strpos($r['answer'], 'Invented') === false, 'Model prose never displayed');
check($r['sources'][0]['url'] === '#home', 'Source anchor preserved');
foreach ([[], ['source_ids'=>['unknown']], ['source_ids'=>'profile0'], ['source_ids'=>[[]]], ['source_ids'=>['profile0','unknown']], ['source_ids'=>['profile0','profile0','profile0','profile0']]] as $selection) {
 check(portfolio_answer($selection, $sources)['sources'] === [], 'Invalid selection fails closed');
}
check(count(portfolio_answer(['source_ids'=>['profile0','profile0']], $sources)['sources']) === 1, 'Duplicate source removed');
