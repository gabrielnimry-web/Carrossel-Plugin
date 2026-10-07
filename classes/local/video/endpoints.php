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

namespace local_coursecarousel\local\video;

/**
 * Stable endpoint definitions for supported video providers.
 */
final class endpoints {
    /** @var string YouTube playlist items endpoint. */
    public const YOUTUBE_API_PLAYLIST_ITEMS = 'https://www.googleapis.com/youtube/v3/playlistItems';
    /** @var string YouTube videos endpoint. */
    public const YOUTUBE_API_VIDEOS = 'https://www.googleapis.com/youtube/v3/videos';
    /** @var string YouTube embed host. */
    public const YOUTUBE_EMBED_HOST = 'https://www.youtube.com';
    /** @var string Privacy-enhanced YouTube embed host. */
    public const YOUTUBE_PRIVACY_EMBED_HOST = 'https://www.youtube-nocookie.com';
    /** @var string Eduplay allowed host. */
    public const EDUPLAY_HOST = 'eduplay.rnp.br';
    /** @var string Eduplay base URL. */
    public const EDUPLAY_BASE_URL = 'https://eduplay.rnp.br';

    /**
     * Build a YouTube embed URL.
     *
     * @param string $videoid Video identifier.
     * @param bool $privacyenhanced Whether to use the privacy-enhanced host.
     * @return string Embed URL.
     */
    public static function youtube_embed(string $videoid, bool $privacyenhanced = false): string {
        $host = $privacyenhanced ? self::YOUTUBE_PRIVACY_EMBED_HOST : self::YOUTUBE_EMBED_HOST;
        return $host . '/embed/' . rawurlencode($videoid);
    }

    /**
     * Build an Eduplay embed URL.
     *
     * @param string $area Eduplay application area.
     * @param string $videoid Video identifier.
     * @return string Embed URL.
     */
    public static function eduplay_embed(string $area, string $videoid): string {
        return self::EDUPLAY_BASE_URL . '/' . $area . '/video/embed/' . rawurlencode($videoid);
    }

    /**
     * Build an Eduplay playlist API URL.
     *
     * @param string $playlistid Playlist identifier.
     * @return string Playlist API URL.
     */
    public static function eduplay_playlist(string $playlistid): string {
        return self::EDUPLAY_BASE_URL . '/api/v1/playlists/' . rawurlencode($playlistid) . '/contents';
    }
}
