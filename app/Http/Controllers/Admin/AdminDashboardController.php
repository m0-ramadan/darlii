<?php
namespace App\Http\Controllers\Admin; use App\Models\Lead; use App\Models\Property; use App\Models\Project; use App\Models\User;
class AdminDashboardController {public function __invoke(){return view('admin.dashboard',['stats'=>['properties'=>Property::count(),'pending'=>Property::where('status','pending_review')->count(),'projects'=>Project::count(),'leads'=>Lead::count(),'users'=>User::count()]]);}}
