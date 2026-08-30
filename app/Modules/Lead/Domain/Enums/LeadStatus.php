<?php
namespace App\Modules\Lead\Domain\Enums;
enum LeadStatus:string {case New='new';case Assigned='assigned';case Contacted='contacted';case Qualified='qualified';case FollowUp='follow_up';case Won='won';case Lost='lost';case Spam='spam';}
