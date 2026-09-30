<?php

namespace App\Enums;

enum SesiKonselingEvent: string
{
    case Approval = 'approval';
    case Reschedule = 'reschedule';
    case Cancellation = 'cancellation';
}
