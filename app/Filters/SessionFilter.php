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
        
        // Check if user is logged in
        if ($session->has('user_id')) {
            $userId = $session->get('user_id');
            $sessionId = session_id();
            
            // Try to get existing session
            $existingSession = $userSessionModel->getSessionBySessionId($sessionId);
            
            if ($existingSession && $existingSession['status'] === 'active') {
                // Update last activity
                $userSessionModel->updateActivity($sessionId);
            } else if ($existingSession && $existingSession['status'] !== 'active') {
                // Session exists but not active, force logout
                $session->destroy();
                return redirect()->to('/login')->with('error', 'Your session has expired. Please login again.');
            } else {
                // No session record found, create one
                $ipAddress = $request->getIPAddress();
                $userAgent = $request->getUserAgent();
                
                $userSessionModel->createSession(
                    $userId,
                    $sessionId,
                    $ipAddress,
                    $userAgent->getAgentString()
                );
            }
        }
        
        // Clean up expired sessions periodically (1 in 20 chance)
        if (rand(1, 20) === 1) {
            $timeoutMinutes = config('App')->sessionTimeout ?? 15;
            $userSessionModel->expireInactiveSessions($timeoutMinutes);
        }
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
