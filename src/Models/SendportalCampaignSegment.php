<?php

declare(strict_types=1);

namespace Sendportal\Base\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SendportalCampaignSegment extends Model
{
    use HasFactory;

    protected $table = 'sendportal_campaign_segments';


    protected $guarded = [];
}
