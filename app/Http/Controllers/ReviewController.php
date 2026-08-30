<?php
namespace App\Http\Controllers; use App\Models\Property; use App\Models\Review; use Illuminate\Http\Request;
class ReviewController {public function store(Request $request,Property $property){$data=$request->validate(['rating'=>['required','integer','between:1,5'],'title'=>['nullable','string','max:150'],'comment'=>['required','string','max:3000']]);Review::updateOrCreate(['user_id'=>$request->user()->id,'reviewable_type'=>Property::class,'reviewable_id'=>$property->id],$data+['status'=>'pending']);return back()->with('success',__('messages.review_pending'));}}
