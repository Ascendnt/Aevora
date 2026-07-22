<?php

namespace App\Controllers;

/**
 * Backing endpoint for the "Aevora Assistant" floating chat widget
 * (see app/Views/partials/assistant.php).
 *
 * There is no AI provider connected in this deployment — no API keys
 * exist for this yet. This controller is the intended future integration
 * point: see the comment block inside ask() for exactly where a real
 * call would go.
 */
class Assistant extends BaseController
{
    /** POST — placeholder chat endpoint. Always replies that it isn't connected yet. */
    public function ask()
    {
        $message = trim((string) $this->request->getPost('message'));

        // ------------------------------------------------------------------
        // FUTURE INTEGRATION POINT
        //
        // This is where a real reply would be produced once an AI provider
        // is wired up. Two realistic options for this app:
        //
        //   1. Call an LLM directly via CodeIgniter's own HTTP client
        //      (no Guzzle needed — this app has no working Composer/vendor
        //      pipeline):
        //
        //        $client = \Config\Services::curlrequest();
        //        $response = $client->post('https://api.anthropic.com/v1/messages', [
        //            'headers' => [
        //                'x-api-key'         => getenv('ANTHROPIC_API_KEY'),
        //                'anthropic-version' => '2023-06-01',
        //                'content-type'      => 'application/json',
        //            ],
        //            'json' => [
        //                'model'      => 'claude-...',
        //                'max_tokens' => 1024,
        //                'messages'   => [['role' => 'user', 'content' => $message]],
        //            ],
        //        ]);
        //        $reply = json_decode($response->getBody(), true);
        //
        //   2. Forward $message (plus employee/company context — see
        //      current_employee(), scoped_company_id()) to an n8n or
        //      Zapier webhook URL and relay its response back to the panel.
        //
        // Whichever is chosen, wrap the call in try/catch and fail
        // gracefully (this is a live external call), same as the holiday
        // API integration elsewhere in this app.
        // ------------------------------------------------------------------

        $brand = hq_company_name();

        return $this->response->setJSON([
            'ok'        => false,
            'connected' => false,
            'reply'     => "The {$brand} Assistant isn't connected to an AI provider yet. This is a placeholder reply — "
                . 'once an LLM API key or an automation tool like n8n/Zapier is wired in here, '
                . "I'll be able to give real answers and take real actions.",
        ]);
    }
}
