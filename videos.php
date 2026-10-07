<?php
// This file is part of Moodle - http://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Local Course Carousel plugin.
 *
 * @package    local_coursecarousel
 * @copyright  2026 Universidade Federal de Minas Gerais
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/coursecarousel/lib_local.php');
require_once($CFG->libdir . '/filelib.php');

/**
 * Fetch and decode a JSON document using Moodle's HTTP client.
 *
 * @param string $url
 * @param array $headers
 * @return array|null
 */
function local_coursecarousel_fetch_json(string $url, array $headers = []): ?array {
    $cache = cache::make('local_coursecarousel', 'videos');
    $cachekey = sha1($url . "\n" . implode("\n", $headers));
    $cached = $cache->get($cachekey);
    if ($cached !== false) {
        return $cached;
    }

    $client = new \curl();
    $timeout = (int)get_config('local_coursecarousel', 'videotimeout');
    $timeout = $timeout > 0 ? min(30, $timeout) : 10;
    $options = [
        'CURLOPT_CONNECTTIMEOUT' => min(5, $timeout),
        'CURLOPT_TIMEOUT' => $timeout,
    ];
    if ($headers) {
        $client->setHeader($headers);
    }
    $response = $client->get($url, [], $options);
    $info = $client->get_info();
    if ($client->get_errno() || ($info['http_code'] ?? 0) !== 200) {
        debugging('Falha ao consultar serviço de vídeo externo.', DEBUG_DEVELOPER);
        return null;
    }
    $data = json_decode($response, true);
    if (!is_array($data)) {
        return null;
    }
    $cache->set($cachekey, $data);
    return $data;
}

/**
 * Renderiza um carrossel de vídeos.
 *
 * @param array $sources Fontes de vídeo e suas playlists.
 * @param string $title Título exibido no carrossel.
 * @param string $carouselid Identificador HTML do carrossel.
 * @return void
 */
function local_coursecarousel_render_video_carousel(
    $sources,
    $title,
    $carouselid
) {

    global $PAGE;

    $renderer = $PAGE->get_renderer('local_coursecarousel');

    $videos = [];

    foreach ($sources as $source) {

        if ($source['type'] === 'eduplay') {

            $videos = array_merge(
                $videos,
                local_coursecarousel_get_eduplay_videos(
                    $source['playlist']
                )
            );

        } else if ($source['type'] === 'youtube') {

            $videos = array_merge(
                $videos,
                local_coursecarousel_get_youtube_videos(
                    $source['playlist']
                )
            );
        }
    }

    if (empty($videos)) {
        return;
    }

    $itemsperslide =
    local_coursecarousel_get_items_per_slide();

    $chunks =
        array_chunk(
            $videos,
            $itemsperslide
        );

    $slides = [];

    foreach ($chunks as $i => $group) {

        $cards = [];

        foreach ($group as $video) {

            $carddata = [

                'tagname' => get_string('video', 'local_coursecarousel'),
                'channelname' => format_string($video['channelname'] ?? ''),
                'viewcount' => $video['viewcount'] ?? null,

                'videotitle' =>
                    format_string(
                    $video['title']
                ),

                'duration' =>
                    '⏱ '.$video['duration'],

                'embedurl' =>
                    $video['embedurl'],

                'videourl' => (new moodle_url(
                    '/local/coursecarousel/video.php',
                    [
                        'source' => $video['source'],
                        'id' => $video['videoid'],
                        'area' => $video['area'] ?? '',
                        'title' => $video['title'],
                    ]
                ))->out(false),
            ];

            $cards[] = [

                'cardhtml' =>
                    $renderer->render_from_template(
                        'local_coursecarousel/video_card',
                        $carddata
                    ),

            ];
        }

        $slides[] = [

            'active' =>
                ($i == 0),

            'courses' =>
                $cards,

        ];
    }

    // A mixed playlist must not advertise an arbitrary channel as the only source.
    $youtubeonly = !empty($videos) && count(array_filter($videos, function($video) {
        return ($video['source'] ?? '') === 'youtube';
    })) === count($videos);
    $channelurls = array_unique(array_column($videos, 'channelurl'));
    $channelurl = $youtubeonly && count($channelurls) === 1 ? reset($channelurls) : '';
    $data = [
        'isyoutube' => $youtubeonly,
        'channelurl' => $channelurl,
        'title' => $title,

        'carouselid' => $carouselid,

        'itemsclass' => 'items-' . $itemsperslide,

        'showbutton' => false,

        'slides' => $slides,

    ];

    echo $renderer->render_carousel(new \local_coursecarousel\output\carousel($data));
}

/**
 * Obtém os vídeos de uma playlist do YouTube.
 *
 * @param string $playlistid Identificador da playlist do YouTube.
 * @return array Lista de vídeos encontrados.
 */
function local_coursecarousel_get_youtube_videos($playlistid) {

    $apikey = get_config('local_coursecarousel', 'youtubeapikey');

    if (empty($apikey) || empty($playlistid)) {
        return [];
    }

    $maxvideos = get_config('local_coursecarousel', 'youtubemaxvideos');
    if ($maxvideos === false) {
        $maxvideos = get_config('local_coursecarousel', 'maxvideos');
    }
    if (empty($maxvideos)) {
        $maxvideos = 20;
    }
    // 1. Playlist items
    $url = new moodle_url(\local_coursecarousel\local\video\endpoints::YOUTUBE_API_PLAYLIST_ITEMS, [
        'part' => 'snippet',
        'maxResults' => min(50, max(1, (int)$maxvideos)),
        'playlistId' => $playlistid,
        'key' => $apikey,
    ]);
    $data = local_coursecarousel_fetch_json($url->out(false));

    if (!$data || empty($data['items'])) {
        return [];
    }
    // 2. Coletar IDs
    $videoids = [];

    foreach ($data['items'] as $item) {
        $videoid = $item['snippet']['resourceId']['videoId'] ?? '';
        if (is_string($videoid) && preg_match('/^[A-Za-z0-9_-]{11}$/D', $videoid)) {
            $videoids[] = $videoid;
        }
    }

    if (!$videoids) {
        return [];
    }
    // 3. UMA chamada para durations
    $idlist = implode(',', $videoids);

    $videourl = new moodle_url(\local_coursecarousel\local\video\endpoints::YOUTUBE_API_VIDEOS, [
        'part' => 'contentDetails,snippet,statistics',
        'id' => $idlist,
        'key' => $apikey,
    ]);
    $videodata = local_coursecarousel_fetch_json($videourl->out(false));

    $durations = [];
    $resources = [];

    if (!empty($videodata['items'])) {

        foreach ($videodata['items'] as $v) {
            if (!empty($v['id'])) { $resources[$v['id']] = $v; }
            if (!empty($v['id']) && !empty($v['contentDetails']['duration'])) {
                $durations[$v['id']] = $v['contentDetails']['duration'];
            }
        }
    }
    // 4. Montar resultado final
    $videos = [];

    foreach ($data['items'] as $item) {

        $videoid = $item['snippet']['resourceId']['videoId'] ?? '';
        if (!in_array($videoid, $videoids, true)) {
            continue;
        }

        $iso = $durations[$videoid] ?? null;

        $duration = '--';

        if ($iso) {
            try {
                $interval = new DateInterval($iso);
            } catch (Exception $e) {
                $interval = null;
                debugging('Duração inválida recebida da API do YouTube.', DEBUG_DEVELOPER);
            }

            if ($interval && $interval->h > 0) {
                $duration = sprintf(
                    '%02d:%02d:%02d',
                    $interval->h,
                    $interval->i,
                    $interval->s
                );
            } else if ($interval) {
                $duration = sprintf(
                    '%02d:%02d',
                    $interval->i,
                    $interval->s
                );
            }
        }

        $metadata = \local_coursecarousel\local\video\youtube_metadata::from_resource($resources[$videoid] ?? [], $item);
        $videos[] = [
            'channelname' => $metadata['channelname'],
            'channelurl' => $metadata['channelurl'],
            'viewcount' => $metadata['viewcount'],
            'source' => 'youtube',
            'videoid' => $videoid,
            'title' => $item['snippet']['title'] ?? get_string('video', 'local_coursecarousel'),
            'duration' => $duration,
            'embedurl' => \local_coursecarousel\local\video\endpoints::youtube_embed($videoid),
        ];
    }

    return $videos;
}

/**
 * Obtém os vídeos de uma playlist do Eduplay.
 *
 * @param string $playlistid Identificador da playlist do Eduplay.
 * @return array Lista de vídeos encontrados.
 */
function local_coursecarousel_get_eduplay_videos($playlistid) {

    $clientkey =
        get_config(
            'local_coursecarousel',
            'eduplayclientkey'
        );

    if (empty($clientkey) || empty($playlistid)) {
        return [];
    }

    $maxvideos = get_config('local_coursecarousel', 'eduplaymaxvideos');
    if ($maxvideos === false) {
        $maxvideos = get_config('local_coursecarousel', 'maxvideos');
    }

    if (empty($maxvideos)) {
        $maxvideos = 20;
    }

    $url = new moodle_url(\local_coursecarousel\local\video\endpoints::eduplay_playlist((string)$playlistid), [
        'quantity' => min(100, max(1, (int)$maxvideos)),
        'page' => 1,
    ]);
    $data = local_coursecarousel_fetch_json($url->out(false), ['clientkey: ' . $clientkey]);

    $videos = [];

    if (
        !$data ||
        empty($data['contents'])
    ) {
        return [];
    }

    foreach ($data['contents'] as $item) {

        $embedurl = local_coursecarousel_get_eduplay_embed_url($item['embedUrl'] ?? '');
        if ($embedurl === null) {
            continue;
        }

        $embedparts = local_coursecarousel_get_eduplay_embed_parts($embedurl);
        if ($embedparts === null) {
            continue;
        }

        $videos[] = [

            'source' => 'eduplay',

            'videoid' => $embedparts['id'],

            'area' => $embedparts['area'],

            'title' =>
                $item['name'] ?? get_string('video', 'local_coursecarousel'),

            'duration' =>
                local_coursecarousel_format_eduplay_duration(
                    (int)($item['duration'] ?? 0)
                ),

            'embedurl' => $embedurl,
        ];
    }

    return $videos;
}

/**
 * Formata uma duração do Eduplay.
 *
 * @param int $seconds Duração em segundos.
 * @return string Duração formatada.
 */
function local_coursecarousel_format_eduplay_duration(
    $seconds
) {

    $hours =
        floor(
            $seconds / 3600
        );

    $minutes =
        floor(
            ($seconds % 3600) / 60
        );

    $seconds =
        $seconds % 60;

    if ($hours > 0) {

        return sprintf(
            '%02d:%02d:%02d',
            $hours,
            $minutes,
            $seconds
        );
    }

    return sprintf(
        '%02d:%02d',
        $minutes,
        $seconds
    );
}

/**
 * Allow only HTTPS embeds from the configured video providers.
 *
 * @param string $url
 * @return bool
 */
function local_coursecarousel_is_allowed_embed_url(string $url): bool {
    $parts = parse_url($url);
    if (($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
        return false;
    }
    $host = strtolower($parts['host']);
    $allowedhost = \local_coursecarousel\local\video\endpoints::EDUPLAY_HOST;
    return $host === $allowedhost || substr($host, -strlen('.' . $allowedhost)) === '.' . $allowedhost;
}

/**
 * Convert an Eduplay content URL into its embeddable player URL.
 *
 * The playlists API may return the public content URL instead of the iframe
 * URL. Public URLs open the Eduplay website, while /video/embed/ keeps
 * playback inside Moodle.
 *
 * @param string $url
 * @return string|null
 */
function local_coursecarousel_get_eduplay_embed_url(string $url): ?string {
    if (!local_coursecarousel_is_allowed_embed_url($url)) {
        return null;
    }

    $parts = parse_url($url);
    $path = $parts['path'] ?? '';
    if (!preg_match('#^/(app|portal)/video/(?:embed/)?([^/]+?)/?$#D', $path, $matches)) {
        return null;
    }

    $embedurl = \local_coursecarousel\local\video\endpoints::eduplay_embed(
        $matches[1],
        rawurldecode($matches[2])
    );
    if (!empty($parts['query'])) {
        $embedurl .= '?' . $parts['query'];
    }

    return $embedurl;
}

/**
 * Extract the validated Eduplay player identifier and application area.
 *
 * @param string $url
 * @return array|null
 */
function local_coursecarousel_get_eduplay_embed_parts(string $url): ?array {
    $parts = parse_url($url);
    $path = $parts['path'] ?? '';
    if (!local_coursecarousel_is_allowed_embed_url($url) ||
            !preg_match('#^/(app|portal)/video/embed/([A-Za-z0-9_-]+)/?$#D', $path, $matches)) {
        return null;
    }

    return ['area' => $matches[1], 'id' => $matches[2]];
}
