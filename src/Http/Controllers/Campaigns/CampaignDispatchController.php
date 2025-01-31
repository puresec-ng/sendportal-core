<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Campaigns;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\CampaignDispatchRequest;
use Sendportal\Base\Interfaces\QuotaServiceInterface;
use Sendportal\Base\Models\Asset;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Models\SendportalCampaignSegment;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;

class CampaignDispatchController extends Controller
{
    /** @var CampaignTenantRepositoryInterface */
    protected $campaigns;

    /**
     * @var QuotaServiceInterface
     */
    protected $quotaService;

    public function __construct(
        CampaignTenantRepositoryInterface $campaigns,
        QuotaServiceInterface $quotaService
    ) {
        $this->campaigns = $campaigns;
        $this->quotaService = $quotaService;
    }

    /**
     * Dispatch the campaign.
     *
     * @throws Exception
     */
    public function send(CampaignDispatchRequest $request, int $id): RedirectResponse
    {
        $campaign = $this->campaigns->find(Sendportal::currentWorkspaceId(), $id, ['email_service', 'messages']);

        if ($campaign->status_id !== CampaignStatus::STATUS_DRAFT) {
            return redirect()->route('sendportal.campaigns.status', $id);
        }

        if (!$campaign->email_service_id) {
            return redirect()->route('sendportal.campaigns.edit', $id)
                ->withErrors(__('Please select an Email Service'));
        }

        $campaign->update([
            'send_to_all' => $request->get('recipients') === 'send_to_all',
        ]);

        $campaign->tags()->sync($request->get('tags'));


        $segmentTags = $request->get('segment_tags') ?? [];
        $excludeSegmentTags = $request->get('exclude_segment_tags') ?? [];

        $totalCampaignSubscriberCount = $campaign->unsent_count ?? 0;


        foreach ($segmentTags as $segment) {
            $excluded_users_id = Asset::where('type', 'segment')
                ->whereIn('contract',$excludeSegmentTags)
                ->distinct('user_id')->pluck('user_id')->toArray();
            $totalCampaignSubscriberCount += Asset::
                                            where('contract', $segment)
                                            ->whereNotIn('user_id', $excluded_users_id)
                                            ->where('type', 'segment')
                                            ->where('total', '>=', 1)
                                            ->distinct('user_id')
                                            ->count();
//            $totalCampaignSubscriberCount += Asset::where('contract', $segment)->where('type', 'segment')->where('total', '>=', 1)->distinct('user_id')->count();

            SendportalCampaignSegment::updateOrCreate(['segment_id' => $segment, 'campaign_id' => $campaign->id], [
                'segment_id' => $segment, 'campaign_id' => $campaign->id
            ]);
        }



        if ($this->quotaService->exceedsQuota($campaign->email_service, $campaign->unsent_count)) {
            return redirect()->route('sendportal.campaigns.edit', $id)
                ->withErrors(__('The number of subscribers for this campaign exceeds your SES quota'));
        }

        $totalUserUnit = \DB::table('user_units')->where('workspace_id', Sendportal::currentWorkspaceId())->first()->unit_balance ?? 0;

        if ($totalUserUnit < $totalCampaignSubscriberCount) {
            return redirect()->route('sendportal.campaigns.edit', $id)
                ->withErrors(__('The number of subscribers for this campaign exceeds your unit quota'));
        }

        $scheduledAt = $request->get('schedule') === 'scheduled' ? Carbon::parse($request->get('scheduled_at')) : now();

        $campaign->update([
            'scheduled_at' => $scheduledAt,
            'status_id' => CampaignStatus::STATUS_QUEUED,
            'save_as_draft' => $request->get('behaviour') === 'draft',
            'excluded_segments' => json_encode($excludeSegmentTags)
        ]);

        return redirect()->route('sendportal.campaigns.status', $id);
    }
}
