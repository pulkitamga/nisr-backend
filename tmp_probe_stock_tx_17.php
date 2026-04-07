<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$rows = App\Models\ProductStockTransaction::query()
    ->where('remarks', 'like', '%Refund Settlement #17%')
    ->orWhere('remarks', 'like', '%Refund settlement #17%')
    ->get(['id','product_stock_id','type','quantity','reason','remarks','from_branch_id','to_branch_id','created_at']);
foreach ($rows as $row) { echo json_encode($row->toArray(), JSON_UNESCAPED_UNICODE), PHP_EOL; }
