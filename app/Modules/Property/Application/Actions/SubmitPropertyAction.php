<?php
namespace App\Modules\Property\Application\Actions; use App\Models\Property; class SubmitPropertyAction {public function execute(Property $property):Property{$property->update(['status'=>'pending_review']);return $property->refresh();}}
