<?php

namespace App\Helpers;

use Coolsam\Flatpickr\Forms\Components\Flatpickr;

class MyFlatpickr extends Flatpickr
{
    public function getDefaultStateCasts(): array
    {
        if ($this->isMultiplePicker()) {
            return $stateCasts= [];
        }

        return parent::getDefaultStateCasts();
    }
}

