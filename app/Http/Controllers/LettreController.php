<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Paragraph;
use Illuminate\Support\Facades\Cache;

class LettreController extends Controller
{
    public function __constract()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $lettreCategory = Category::where('parent_id', Category::MEIWEN_PID)->orderBy('order', 'asc')->get();
        $hotLettres = Paragraph::where('status', 1)->latest()->paginate(5);
        return view('lettre.index', compact('lettreCategory', 'hotLettres'));
    }

    public function hot()
    {
        $lettres = Paragraph::where('status', 1)->latest()->get();
        $title = '最热美文';
        return view('category.lettres', compact('lettres', 'title'));
    }

    public function store(Request $request, Paragraph $paragraph)
    {
        $user = auth()->user();
        Cache::forever("user." . $user->id . "lettre", $paragraph);
        return response(['data' => '选择美文成功'], 201);
    }
}
