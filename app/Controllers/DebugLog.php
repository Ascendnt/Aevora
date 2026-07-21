<?php

namespace App\Controllers;

/**
 * TEMPORARY diagnostic endpoint — superadmin-only tail of the newest log
 * file, since this container can't be shelled into directly. Delete after
 * use (matches the same throwaway pattern as the earlier DebugUsers command).
 */
class DebugLog extends BaseController
{
    public function tail()
    {
        if (! is_superadmin()) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        $files = glob(WRITEPATH . 'logs/*.log');
        if (! $files) {
            return $this->response->setContentType('text/plain')->setBody('No log files found.');
        }

        usort($files, static fn ($a, $b) => filemtime($b) <=> filemtime($a));
        $latest = $files[0];

        $lines = file($latest);
        $tail  = implode('', array_slice($lines, -300));

        return $this->response->setContentType('text/plain')->setBody($latest . "\n\n" . $tail);
    }
}
