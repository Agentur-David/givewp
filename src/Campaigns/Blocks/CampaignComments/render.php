<?php

use Give\Campaigns\Blocks\CampaignComments\Controller\BlockRenderController;
use Give\Campaigns\Models\Campaign;

$attributes = $attributes ?? [];

/** @var Campaign $campaign */

echo (new BlockRenderController())->render($attributes);
?>
