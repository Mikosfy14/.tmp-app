<?php

if (! function_exists('get_contextual_back')) {
    /**
     * Get a contextual back URL and friendly label based on HTTP_REFERER or 'ref' query parameter.
     * Features open-redirect protection, self-loop prevention, query string preservation, and static fallback.
     *
     * @param string $defaultUrl   Default fallback route path or absolute URL (e.g. '/projects')
     * @param string $defaultLabel Default label text (e.g. 'Kembali ke Project Tracker')
     * @return array{url: string, label: string}
     */
    function get_contextual_back(string $defaultUrl, string $defaultLabel): array
    {
        $resolvedDefaultUrl = str_starts_with($defaultUrl, 'http') ? $defaultUrl : base_url($defaultUrl);

        $request = service('request');

        // 1. Explicit query parameter override ('ref')
        $refParam = (string) $request->getGet('ref');
        if ($refParam !== '') {
            $refMap = [
                'dashboard'   => ['/dashboard', 'Kembali ke Dashboard'],
                'kinerja-tim' => ['/kinerja-tim', 'Kembali ke Kinerja Tim'],
                'projects'    => ['/projects', 'Kembali ke Project Tracker'],
                'aplikasi'    => ['/aplikasi', 'Kembali ke Kelola Aplikasi'],
                'users'       => ['/users', 'Kembali ke Kelola Pengguna'],
                'profile'     => ['/profile', 'Kembali ke Profil'],
            ];

            if (isset($refMap[$refParam])) {
                return [
                    'url'   => base_url($refMap[$refParam][0]),
                    'label' => $refMap[$refParam][1],
                ];
            }
        }

        // 2. HTTP_REFERER detection
        $referer = (string) $request->getServer('HTTP_REFERER');
        if ($referer === '') {
            return [
                'url'   => $resolvedDefaultUrl,
                'label' => $defaultLabel,
            ];
        }

        $appHost = parse_url(base_url(), PHP_URL_HOST);
        $refererHost = parse_url($referer, PHP_URL_HOST);
        $refererPort = parse_url($referer, PHP_URL_PORT);
        $appPort = parse_url(base_url(), PHP_URL_PORT);

        // Security check: Must belong to the exact same application host
        if ($refererHost === null || strtolower($refererHost) !== strtolower((string) $appHost)) {
            return [
                'url'   => $resolvedDefaultUrl,
                'label' => $defaultLabel,
            ];
        }

        // Port check if specified
        if ($appPort !== null && $refererPort !== null && (int) $refererPort !== (int) $appPort) {
            return [
                'url'   => $resolvedDefaultUrl,
                'label' => $defaultLabel,
            ];
        }

        $refererPath = (string) parse_url($referer, PHP_URL_PATH);
        $currentPath = (string) parse_url(current_url(), PHP_URL_PATH);

        // Self-loop prevention: If referer path is the current page, fallback
        if ($refererPath === $currentPath) {
            return [
                'url'   => $resolvedDefaultUrl,
                'label' => $defaultLabel,
            ];
        }

        // Detail-to-Detail circular loop prevention:
        // 1. User detail page should never treat a child drilldown (/projects/detail, etc.) or another detail page as a back destination.
        if (str_contains($currentPath, '/users/detail') && (str_contains($refererPath, '/projects/detail') || str_contains($refererPath, '/aplikasi/detail') || str_contains($refererPath, '/users/detail'))) {
            return [
                'url'   => $resolvedDefaultUrl,
                'label' => $defaultLabel,
            ];
        }

        // 2. Application detail page should never treat another detail page as a back destination.
        if (str_contains($currentPath, '/aplikasi/detail') && (str_contains($refererPath, '/projects/detail') || str_contains($refererPath, '/users/detail') || str_contains($refererPath, '/aplikasi/detail'))) {
            return [
                'url'   => $resolvedDefaultUrl,
                'label' => $defaultLabel,
            ];
        }

        // 3. Project detail page should never treat project detail or application detail as a back destination.
        if (str_contains($currentPath, '/projects/detail') && (str_contains($refererPath, '/projects/detail') || str_contains($refererPath, '/aplikasi/detail'))) {
            return [
                'url'   => $resolvedDefaultUrl,
                'label' => $defaultLabel,
            ];
        }

        // Ignore transient, form mutation, or action paths (e.g. edit/create form pages or POST actions)
        $ignoredPatterns = [
            '/login',
            '/logout',
            '/delete',
            '/attempt',
            '/reset-password',
            '/activate',
            '/deactivate',
            '/edit',
            '/create',
            '/tambah',
            '/ubah',
            '/store',
            '/update',
            '/export',
            '/download',
        ];
        foreach ($ignoredPatterns as $pattern) {
            if (str_contains($refererPath, $pattern)) {
                return [
                    'url'   => $resolvedDefaultUrl,
                    'label' => $defaultLabel,
                ];
            }
        }

        // Contextual dynamic label detection
        $label = $defaultLabel;
        if (str_contains($refererPath, '/dashboard')) {
            $label = 'Kembali ke Dashboard';
        } elseif (str_contains($refererPath, '/kinerja-tim')) {
            $label = 'Kembali ke Kinerja Tim';
        } elseif (str_contains($refererPath, '/projects/detail')) {
            $label = 'Kembali ke Detail Project';
        } elseif (str_contains($refererPath, '/projects')) {
            $label = 'Kembali ke Project Tracker';
        } elseif (str_contains($refererPath, '/aplikasi/detail')) {
            $label = 'Kembali ke Detail Aplikasi';
        } elseif (str_contains($refererPath, '/aplikasi')) {
            $label = 'Kembali ke Kelola Aplikasi';
        } elseif (str_contains($refererPath, '/users/detail')) {
            $label = 'Kembali ke Detail Pengguna';
        } elseif (str_contains($refererPath, '/users')) {
            $label = 'Kembali ke Kelola Pengguna';
        } elseif (str_contains($refererPath, '/profile')) {
            $label = 'Kembali ke Profil';
        }

        return [
            'url'   => $referer,
            'label' => $label,
        ];
    }
}
