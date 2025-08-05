<?php

namespace custif\includes\embed_handlers;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Embed Converter Class
 *
 * Handles the conversion of social media and other URLs to proper embed formats.
 *
 * @since 1.0.0
 */
class Embed_Converter {

	/**
	 * Convert social media URLs into embeddable URLs.
	 *
	 * This function converts URLs from various platforms (YouTube, Instagram, Figma, etc.)
	 * into their proper embed formats.
	 *
	 * @param  string $url  The original URL to convert.
	 *
	 * @return string The converted embed URL or the original URL if invalid.
	 * @since 1.0.0
	 */
	public function convert_social_to_embed( $url ) {
		$parsed_url = wp_parse_url( $url );

		if ( ! isset( $parsed_url['host'] ) ) {
			return $url; // Return original if invalid.
		}

		$host       = str_replace( 'www.', '', $parsed_url['host'] );
		$path       = isset( $parsed_url['path'] ) ? trim( $parsed_url['path'], '/' ) : '';
		$googlepath = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';
		$query      = $parsed_url['query'] ?? '';
		$video_id   = '';

		// YouTube Embed.
		if ( 'youtu.be' === $host ) {
			$video_id = $path;

			return "https://www.youtube.com/embed/$video_id";
		} elseif ( 'youtube.com' === $host ) {
			// Handle shorts: youtube.com/shorts/VIDEO_ID.
			if ( strpos( $path, 'shorts/' ) === 0 ) {
				$video_id = str_replace( 'shorts/', '', $path );

				return "https://www.youtube.com/embed/$video_id";
			}

			// Handle normal video URLs with ?v=.
			parse_str( $query, $query_params );
			$video_id = $query_params['v'] ?? '';

			return $video_id ? "https://www.youtube.com/embed/$video_id" : $url;
		}

		// Instagram Embed (Reels & Posts).
		if ( 'instagram.com' === $host ) {
			$parts = explode( '/', $path );
			if ( count( $parts ) >= 2 && in_array( $parts[0], array( 'p', 'reel', 'tv' ) ) ) {
				return "https://www.instagram.com/{$parts[0]}/{$parts[1]}/embed";
			}
		}

		// Figma Embed.
		if ( 'figma.com' === $host ) {
			return 'https://www.figma.com/embed?embed_host=figma&url=' . urlencode( $url );
		}

		// Spotify Embed.
		if ( 'open.spotify.com' === $host ) {
			$parts = explode( '/', $path );

			if ( str_contains( $path, 'embed' ) ) {
				return $url;
			}

			if ( count( $parts ) >= 2 ) {
				return "https://open.spotify.com/embed/{$parts[0]}/{$parts[1]}";
			}
		}

		// SoundCloud Embed.
		if ( 'soundcloud.com' === $host ) {
			return 'https://w.soundcloud.com/player/?url=' . urlencode( $url );
		}

		// vimeo embed.
		if ( 'vimeo.com' === $host ) {
			if ( str_contains( $path, 'player' ) ) {
				return $url;
			}

			$parts = explode( '/', $path );
			if ( count( $parts ) >= 1 && is_numeric( $parts[0] ) ) {
				return "https://player.vimeo.com/video/{$parts[0]}";
			}
		}

		// X embed.
		if ( 'twitter.com' === $host || 'x.com' === $host ) {
			$path_segments = explode( '/', trim( $path, '/' ) );
			$tweet_id      = end( $path_segments );

			if ( is_numeric( $tweet_id ) ) {
				$tweet_url     = 'https://twitter.com/' . $path_segments[0] . '/status/' . $tweet_id;
				$oembed_params = array(
					'url' => $tweet_url,
				);
				$oembed_url    = add_query_arg( $oembed_params, 'https://publish.twitter.com/oembed' );
				$response      = wp_remote_get( $oembed_url );

				if ( is_wp_error( $response ) ) {
					return '<p>Error fetching tweet. Please try again.</p>';
				}

				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );
				// Return embedded Tweet HTML if available.
				if ( isset( $data['html'] ) ) {
					return $data['html'];
				} else {
					return '<div class="custif-iframe-notice"><p>' . esc_html__(
						'Invalid response.',
						'custom-iframe'
					) . '</p></div>';
				}
			}
		}

		// Embed URL for Google Docs, Sheets, Slides, and Forms.
		if ( 'docs.google.com' === $host ) {
			if ( preg_match( '/\/document\//', $googlepath ) ) {
				if ( ! str_contains( $url, '?embedded=true' ) ) {
					$embed_url = str_replace( '/pub', '/pub?embedded=true', $url );
				} else {
					$embed_url = $url;
				}
			} elseif ( preg_match( '/\/spreadsheets\//', $googlepath ) ) {
				$embed_url = str_replace( '/edit', '/pubhtml', $url );
			} elseif ( preg_match( '/\/presentation\//', $googlepath ) ) {
				$embed_url = str_replace( '/pub', '/embed', $url );
			} elseif ( preg_match( '/\/forms\//', $googlepath ) ) {
				if ( str_contains( $url, '?usp=header' ) ) {
					$embed_url = str_replace( '?usp=header', '?embedded=true', $url );
				} else {
					$embed_url = $url;
				}
			} else {
				return '<div class="custif-iframe-notice"><p>' . esc_html__(
					'Unsupported Google App URL.',
					'custom-iframe'
				) . '</p></div>';
			}

			return esc_url( $embed_url );
		}

		// Wistia Embed (for any wistia.com subdomain).
		if ( str_ends_with( $host, 'wistia.com' ) ) {
			$parts = explode( '/', $path );
			if ( count( $parts ) >= 2 && 'medias' === $parts[0] ) {
				$media_id = $parts[1];

				if ( $media_id ) {
					return "https://fast.wistia.net/embed/iframe/{$media_id}?seo=false&videoFoam=true";
				}
			}
		}

		return $url; // Return original if no match.
	}
}
