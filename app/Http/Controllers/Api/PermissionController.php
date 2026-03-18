<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;

class PermissionController extends Controller
{
    public function sharedWithMe()
    {
        $documents = Document::with([
            'user',
            'permissions' => function ($query) {
                $query->where('user_id', auth()->id());
            }
        ])->whereHas('permissions', function ($query) {
            $query->where('user_id', auth()->id());
        })->latest()->get();

        return response()->json([
            'status' => true,
            'data' => $documents
        ]);
    }
}
