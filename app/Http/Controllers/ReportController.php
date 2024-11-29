<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function newReport($id)
    {
        $warehouse = Warehouse::findOrFail()
    }

    public function saveReport()
    {

    }

    public function list()
    {

    }
}
