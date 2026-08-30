<?php
namespace App\Http\Requests; use Illuminate\Foundation\Http\FormRequest;
class StoreLeadRequest extends FormRequest {public function authorize():bool{return true;} public function rules():array{return ['name'=>['required','string','max:120'],'email'=>['nullable','email','max:190'],'phone'=>['nullable','string','max:50'],'message'=>['nullable','string','max:5000'],'property_id'=>['nullable','integer','exists:properties,id'],'project_id'=>['nullable','integer','exists:projects,id'],'source'=>['nullable','string','max:50']];}}
