<?php

/**
 * icon_helper — bespoke inline-SVG icon family for the HRIS.
 *
 * A single, hand-drawn line-icon set drawn on a consistent 24×24 grid with a
 * 1.6 stroke, round caps/joins and a subtle organic curve so the whole app
 * shares one intentional visual voice (replaces the generic Tabler icon font).
 *
 * Usage in views:
 *   <?= icon('dashboard') ?>
 *   <?= icon('plus', 'ic-sm') ?>
 *   <?= icon('leaf', 'text-clay', 18) ?>
 *
 * Every icon inherits `currentColor`, so colour is controlled by the parent.
 */

if (! function_exists('icon')) {
    /**
     * @param string $name  icon key (see $paths below; unknown → 'dot')
     * @param string $class extra classes for the <svg>
     * @param int    $size  pixel size (width = height)
     */
    function icon(string $name, string $class = '', int $size = 20): string
    {
        static $paths = null;

        if ($paths === null) {
            $paths = icon_library();
        }

        $inner = $paths[$name] ?? ($paths[icon_alias($name)] ?? $paths['dot']);
        $cls   = trim('ic ' . $class);

        return '<svg class="' . esc($cls, 'attr') . '" width="' . $size . '" height="' . $size . '" '
            . 'viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" '
            . 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
            . $inner . '</svg>';
    }
}

if (! function_exists('icon_alias')) {
    /** Maps legacy Tabler names to the closest icon in the library. */
    function icon_alias(string $name): string
    {
        $map = [
            'layout-dashboard' => 'dashboard',
            'user-circle'      => 'user',
            'id-badge-2'       => 'badge',
            'calendar-off'     => 'calendar-x',
            'calendar-cog'     => 'calendar-gear',
            'calendar-star'    => 'calendar-star',
            'calendar-time'    => 'calendar-clock',
            'shield-lock'      => 'shield-key',
            'shield-check'     => 'shield-check',
            'logout-2'         => 'logout',
            'login-2'          => 'login',
            'file-type-pdf'    => 'file-pdf',
            'file-spreadsheet' => 'file-sheet',
            'map-pin'          => 'pin',
            'user-plus'        => 'user-add',
        ];

        return $map[$name] ?? 'dot';
    }
}

if (! function_exists('icon_library')) {
    function icon_library(): array
    {
        return [
            // — navigation —
            'dashboard'  => '<rect x="3.5" y="3.5" width="7" height="8.5" rx="1.6"/><rect x="3.5" y="15" width="7" height="5.5" rx="1.6"/><rect x="13.5" y="3.5" width="7" height="5.5" rx="1.6"/><rect x="13.5" y="12" width="7" height="8.5" rx="1.6"/>',
            'bell'       => '<path d="M6.4 10.2a5.6 5.6 0 0 1 11.2 0c0 4 1.3 5.4 2 6.1.4.4.1 1.1-.5 1.1H4.9c-.6 0-.9-.7-.5-1.1.7-.7 2-2.1 2-6.1Z"/><path d="M10 20.2a2.2 2.2 0 0 0 4 0"/>',
            'user'       => '<circle cx="12" cy="8.4" r="3.4"/><path d="M5.6 19.4c.7-3.2 3.2-5 6.4-5s5.7 1.8 6.4 5"/>',
            'users'      => '<circle cx="9.2" cy="8.6" r="3"/><path d="M3.8 19c.6-2.9 2.8-4.6 5.4-4.6 2.6 0 4.8 1.7 5.4 4.6"/><path d="M15.6 6a3 3 0 0 1 .3 5.8"/><path d="M17.2 14.7c1.9.5 3.3 1.9 3.7 4.1"/>',
            'files'      => '<path d="M8 4.5h5.2L18 9v8a1.6 1.6 0 0 1-1.6 1.6H8A1.6 1.6 0 0 1 6.4 17V6.1A1.6 1.6 0 0 1 8 4.5Z"/><path d="M13 4.6V9h4.4"/>',
            'clock'      => '<circle cx="12" cy="12" r="8.2"/><path d="M12 7.6V12l3 2"/>',
            'calendar-x' => '<rect x="4" y="5.4" width="16" height="14.6" rx="2"/><path d="M4 9.4h16M8 3.6v3.4M16 3.6v3.4"/><path d="m10.4 13.4 3.2 3.2m0-3.2-3.2 3.2"/>',
            'receipt'    => '<path d="M6 3.6h12v16.8l-2.4-1.4-2.4 1.4-2.4-1.4-2.4 1.4L6 20.4Z"/><path d="M9 8.4h6M9 12h6"/>',
            'sitemap'    => '<rect x="9.4" y="3.6" width="5.2" height="4.4" rx="1"/><rect x="3.4" y="15.8" width="5.2" height="4.4" rx="1"/><rect x="15.4" y="15.8" width="5.2" height="4.4" rx="1"/><path d="M12 8v3.6M6 15.8v-1.8a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1.8"/>',
            'badge'      => '<rect x="4.5" y="3.5" width="15" height="17" rx="2.2"/><path d="M9.5 3.5v2.2h5V3.5"/><circle cx="12" cy="11" r="2.3"/><path d="M8.6 17.2c.5-1.7 1.8-2.6 3.4-2.6s2.9.9 3.4 2.6"/>',
            'shield-key' => '<path d="M12 3.2 5.4 5.8v5.1c0 4.2 2.8 7.4 6.6 9 3.8-1.6 6.6-4.8 6.6-9V5.8Z"/><circle cx="12" cy="10.4" r="1.8"/><path d="M12 12.2v3.2m0-1.4h1.4"/>',
            'shield-check' => '<path d="M12 3.2 5.4 5.8v5.1c0 4.2 2.8 7.4 6.6 9 3.8-1.6 6.6-4.8 6.6-9V5.8Z"/><path d="m9.2 11.6 2 2 3.6-3.8"/>',
            'moon'       => '<path d="M19.5 14.3A7.6 7.6 0 0 1 9.7 4.5a7.6 7.6 0 1 0 9.8 9.8Z"/>',
            'sun'        => '<circle cx="12" cy="12" r="4"/><path d="M12 2.6v2.2M12 19.2v2.2M4.6 4.6l1.6 1.6M17.8 17.8l1.6 1.6M2.6 12h2.2M19.2 12h2.2M4.6 19.4l1.6-1.6M17.8 6.2l1.6-1.6"/>',
            'logout'     => '<path d="M14 7.5V5.4A1.6 1.6 0 0 0 12.4 3.8H6.2A1.6 1.6 0 0 0 4.6 5.4v13.2a1.6 1.6 0 0 0 1.6 1.6h6.2a1.6 1.6 0 0 0 1.6-1.6v-2.1"/><path d="M9.5 12h10.4m0 0-3-3m3 3-3 3"/>',
            'login'      => '<path d="M10 7.5V5.4a1.6 1.6 0 0 1 1.6-1.6h6.2a1.6 1.6 0 0 1 1.6 1.6v13.2a1.6 1.6 0 0 1-1.6 1.6h-6.2A1.6 1.6 0 0 1 10 18.6v-2.1"/><path d="M3.6 12H14m0 0-3-3m3 3-3 3"/>',
            'settings'   => '<circle cx="12" cy="12" r="2.8"/><path d="M12 2.8l1.3 2.2 2.5-.5.4 2.5 2.3 1-.9 2.4 1.6 2-2 1.6.4 2.5-2.5.5-1.1 2.3-2-1.5-2 1.5-1.1-2.3-2.5-.5.4-2.5-2-1.6 1.6-2-.9-2.4 2.3-1 .4-2.5 2.5.5Z" opacity=".55"/>',
            'refresh'    => '<path d="M20 7.8A8 8 0 1 0 21 12"/><path d="M20 3.6v4.4h-4.4"/>',

            // — actions —
            'plus'       => '<path d="M12 5.4v13.2M5.4 12h13.2"/>',
            'upload'     => '<path d="M12 15.4V4.6m0 0-3.4 3.4M12 4.6l3.4 3.4"/><path d="M5 15v2.8a1.6 1.6 0 0 0 1.6 1.6h10.8a1.6 1.6 0 0 0 1.6-1.6V15"/>',
            'download'   => '<path d="M12 4.6v10.8m0 0 3.4-3.4M12 15.4l-3.4-3.4"/><path d="M5 15v2.8a1.6 1.6 0 0 0 1.6 1.6h10.8a1.6 1.6 0 0 0 1.6-1.6V15"/>',
            'trash'      => '<path d="M4.8 6.6h14.4M9 6.6V4.9a1.2 1.2 0 0 1 1.2-1.2h3.6A1.2 1.2 0 0 1 15 4.9v1.7M6.4 6.6l.9 12a1.6 1.6 0 0 0 1.6 1.5h6.2a1.6 1.6 0 0 0 1.6-1.5l.9-12"/><path d="M10.2 10.2v6M13.8 10.2v6"/>',
            'pin'        => '<path d="M12 21c4-4.4 6-7.6 6-10.4A6 6 0 0 0 6 10.6C6 13.4 8 16.6 12 21Z"/><circle cx="12" cy="10.4" r="2.2"/>',
            'printer'    => '<path d="M7 9V4.4h10V9"/><path d="M7 17H5.6A1.6 1.6 0 0 1 4 15.4v-4A1.6 1.6 0 0 1 5.6 9.8h12.8A1.6 1.6 0 0 1 20 11.4v4a1.6 1.6 0 0 1-1.6 1.6H17"/><rect x="7" y="14.4" width="10" height="5.4" rx="1"/><path d="M16.4 12.4h.01"/>',
            'file-pdf'   => '<path d="M8 4.5h5.2L18 9v9.4A1.6 1.6 0 0 1 16.4 20H8a1.6 1.6 0 0 1-1.6-1.6V6.1A1.6 1.6 0 0 1 8 4.5Z"/><path d="M13 4.6V9h4.4"/><path d="M9 15.4h.9a1 1 0 0 0 0-2H9v3.6m3.4-3.6v3.6h.7a1 1 0 0 0 1-1v-1.6a1 1 0 0 0-1-1Zm3.6 0v3.6m0-1.8h1.4"/>',
            'file-sheet' => '<path d="M8 4.5h5.2L18 9v9.4A1.6 1.6 0 0 1 16.4 20H8a1.6 1.6 0 0 1-1.6-1.6V6.1A1.6 1.6 0 0 1 8 4.5Z"/><path d="M13 4.6V9h4.4"/><rect x="8.8" y="12.4" width="6.4" height="5" rx=".6"/><path d="M8.8 14.9h6.4M12 12.4v5"/>',
            'checklist'  => '<path d="M9.4 6.4h9.2M9.4 12h9.2M9.4 17.6h9.2"/><path d="m4.4 5.6 1 1 1.8-2M4.4 11.4l1 1 1.8-2M4.4 17.2l1 1 1.8-2"/>',
            'arrow-right'=> '<path d="M4.6 12h14.8m0 0-5-5m5 5-5 5"/>',
            'calendar-clock' => '<rect x="4" y="5.4" width="16" height="14.6" rx="2"/><path d="M4 9.4h16M8 3.6v3.4M16 3.6v3.4"/><circle cx="12" cy="14.6" r="3"/><path d="M12 13v1.6l1.1.8"/>',
            'calendar-star'  => '<rect x="4" y="5.4" width="16" height="14.6" rx="2"/><path d="M4 9.4h16M8 3.6v3.4M16 3.6v3.4"/><path d="m12 12 .9 1.8 2 .3-1.4 1.4.3 2-1.8-.9-1.8.9.3-2L9.1 14l2-.3Z"/>',
            'calendar-gear'  => '<rect x="4" y="5.4" width="16" height="14.6" rx="2"/><path d="M4 9.4h16M8 3.6v3.4M16 3.6v3.4"/><circle cx="12" cy="14.4" r="1.8"/><path d="M12 11.2v1M12 17.6v-1M9.2 14.4h1M14.8 14.4h-1"/>',

            // — landing / feature glyphs —
            'building'   => '<rect x="5.4" y="3.6" width="13.2" height="16.8" rx="1.6"/><path d="M9 7.4h2M13 7.4h2M9 11h2M13 11h2M9 14.6h2M13 14.6h2M10.4 20.4v-3h3.2v3"/>',
            'branch'     => '<circle cx="6.4" cy="6" r="2.2"/><circle cx="17.6" cy="6" r="2.2"/><circle cx="12" cy="18" r="2.2"/><path d="M6.4 8.2v2a2 2 0 0 0 2 2h7.2a2 2 0 0 0 2-2v-2M12 12.4v3.4"/>',
            'wallet'     => '<path d="M4.4 7.6A1.6 1.6 0 0 1 6 6h11.4a1.4 1.4 0 0 1 1.4 1.4v.6"/><rect x="3.6" y="7.8" width="16.8" height="11.4" rx="2"/><path d="M20.4 12.4h-3.2a1.8 1.8 0 0 0 0 3.6h3.2"/>',
            'home'       => '<path d="M4.6 10.4 12 4.4l7.4 6v8.2a1.4 1.4 0 0 1-1.4 1.4H6a1.4 1.4 0 0 1-1.4-1.4Z"/><path d="M9.6 20V13.4h4.8V20"/>',
            'timer'      => '<circle cx="12" cy="13.4" r="7"/><path d="M12 13.4V9.6M9.6 3.6h4.8M18.6 7l1.4-1.4"/>',
            'spark'      => '<path d="M12 3.4c.6 4.2 1.8 5.4 6 6-4.2.6-5.4 1.8-6 6-.6-4.2-1.8-5.4-6-6 4.2-.6 5.4-1.8 6-6Z"/>',
            'layers'     => '<path d="m12 4.2 8 4-8 4-8-4Z"/><path d="m4 12.2 8 4 8-4M4 16.2l8 4 8-4"/>',
            'chevron-down' => '<path d="m6.5 9.5 5.5 5 5.5-5"/>',
            'search'     => '<circle cx="10.6" cy="10.6" r="6.2"/><path d="m15.2 15.2 4 4"/>',
            'import'     => '<path d="M12 3.6v10m0 0 3.4-3.4M12 13.6l-3.4-3.4"/><path d="M4.8 16.4v1.4a2.2 2.2 0 0 0 2.2 2.2h10a2.2 2.2 0 0 0 2.2-2.2v-1.4"/>',
            'play'       => '<circle cx="12" cy="12" r="8.4"/><path d="M10.2 8.8 15 12l-4.8 3.2Z"/>',
            'mail'       => '<rect x="3.6" y="5.6" width="16.8" height="12.8" rx="2"/><path d="m4.4 7 7.6 5.4L19.6 7"/>',
            'leaf'       => '<path d="M12 3.8C8 6.7 6 9.5 6 13a6 6 0 0 0 12 0c0-3.5-2-6.3-6-9.2Z"/><path d="M12 20v-9.4"/>',
            'dot'        => '<circle cx="12" cy="12" r="2.4"/>',
        ];
    }
}
