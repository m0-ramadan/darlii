<?php
namespace App\Http\Controllers; use App\Models\AgentProfile;
class AgentController {public function index(){return view('agents.index',['agents'=>AgentProfile::with(['user','agency','translations'])->where('status','active')->paginate(12)]);} public function show(string $locale,string $slug){$agent=AgentProfile::with(['user','agency','translations'])->where('slug',$slug)->firstOrFail();return view('agents.show',compact('agent'));}}
