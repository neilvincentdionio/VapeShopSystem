<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SessionFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param array|null $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $userSessionModel = new \App\Models\UserSessionModel();

        if ($session->has('user_id')) {
            $userId = (int) $session->get('user_id');
            $sessionId = session_id();

            if ($userId <= 0 || ! $this->userExists($userId)) {
                $session->destroy();

                return redirect()->to('/login')->with('error', 'Your session is no longer valid. Please login again.');
            }

            $existingSession = $userSessionModel->getSessionBySessionId($sessionId);

            if ($existingSession && $existingSession['status'] === 'active') {
                $userSessionModel->updateActivity($sessionId);
            } elseif ($existingSession && $existingSession['status'] !== 'active') {
                $session->destroy();

                return redirect()->to('/login')->with('error', 'Your session has expired. Please login again.');
            } else {
                try {
                    $userSessionModel->createSession(
                        $userId,
                        $sessionId,
                        $request->getIPAddress(),
                        $request->getUserAgent()->getAgentString()
                    );
                } catch (\Throwable $e) {
                    log_message('error', 'Failed to create user session record: {message}', [
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }

        if (rand(1, 20) === 1) {
            $timeoutMinutes = config('App')->sessionTimeout ?? 15;
            $userSessionModel->expireInactiveSessions($timeoutMinutes);
        }
    }

    private function userExists(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $db = \Config\Database::connect();

        return $db->table('users')->where('id', $userId)->countAllResults() > 0;
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution and must always return a response
     * object.
     *
     * @param array|null $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after response
    }
}
