<?php
namespace App\Http\Controllers; use App\Models\Property; use App\Models\Project; use App\Models\Post;
class HomeController {public function __invoke(){return view('home',['properties'=>Property::with(['translations','currency','media'])->where('status','published')->latest('published_at')->limit(6)->get(),'projects'=>Project::with('translations')->where('status','published')->latest('published_at')->limit(3)->get(),'posts'=>Post::with('translations')->where('status','published')->latest('published_at')->limit(3)->get()]);}}
