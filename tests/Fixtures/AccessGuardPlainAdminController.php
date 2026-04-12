<?php

namespace Tests\Fixtures;

use App\Http\Controllers\Controller;

class AccessGuardPlainAdminController extends Controller
{
    public function __invoke()
    {
        return response('ok');
    }
}
