<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class SourceOfFund extends Model
{
    use  Searchable;

    public function scopeActive()
    {
        return $this->where('status', Status::ENABLE);
    }
}
