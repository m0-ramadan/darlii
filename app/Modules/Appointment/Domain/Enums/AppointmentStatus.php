<?php
namespace App\Modules\Appointment\Domain\Enums;
enum AppointmentStatus:string {case Requested='requested';case Confirmed='confirmed';case Rescheduled='rescheduled';case Completed='completed';case Cancelled='cancelled';case NoShow='no_show';}
