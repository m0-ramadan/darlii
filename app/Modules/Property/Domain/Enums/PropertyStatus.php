<?php
namespace App\Modules\Property\Domain\Enums;
enum PropertyStatus:string {case Draft='draft';case PendingReview='pending_review';case Published='published';case Rejected='rejected';case Sold='sold';case Rented='rented';case Expired='expired';case Archived='archived';}
