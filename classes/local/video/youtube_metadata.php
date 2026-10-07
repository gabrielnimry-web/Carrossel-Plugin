<?php
// SPDX-License-Identifier: GPL-3.0-or-later
namespace local_coursecarousel\local\video;
defined('MOODLE_INTERNAL') || die();

/** Optional, provider-owned metadata; no fabricated values when the API omits a field. */
final class youtube_metadata {
    public static function from_resource(array $resource, array $playlistitem = []): array {
        $snippet = $resource['snippet'] ?? [];
        $fallback = $playlistitem['snippet'] ?? [];
        $channel = $snippet['channelTitle'] ?? $fallback['videoOwnerChannelTitle'] ?? '';
        $channelid = $snippet['channelId'] ?? $fallback['videoOwnerChannelId'] ?? '';
        $views = $resource['statistics']['viewCount'] ?? null;
        return [
            'channelname' => is_string($channel) ? $channel : '',
            'channelurl' => is_string($channelid) && preg_match('/^UC[A-Za-z0-9_-]{22}$/D', $channelid)
                ? 'https://www.youtube.com/channel/' . $channelid : '',
            'viewcount' => (is_string($views) || is_int($views)) && preg_match('/^\d+$/D', (string)$views)
                ? (string)$views : null,
        ];
    }
}