<?php


function checkPage($id) {
    return  \Sendportal\Base\Models\Message::where('source_type', 'Sendportal\Base\Models\Campaign')
        ->where('source_id', $id)->whereNull('sent_at')->count();
}
