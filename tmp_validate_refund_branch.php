<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$rows = Illuminate\Support\Facades\DB::table('inventory_mutations')->where('reference_id', 17)->orWhere('note', 'like', '%Refund settlement #17%')->get();
foreach ($rows as $row) { echo json_encode((array)$row, JSON_UNESCAPED_UNICODE), PHP_EOL; }
