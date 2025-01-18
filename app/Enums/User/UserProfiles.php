<?php

namespace App\Enums\User;

use App\Traits\EnumFunctions;

enum UserProfiles
{
    use EnumFunctions;

    case Patient;
    case Psychologist;
    case Psychiatrist;
    case Nutritionist;
    case Admin;
}
