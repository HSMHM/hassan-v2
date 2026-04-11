<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProposalAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $proposalId = $request->route('proposalId');
        $sessionKey = 'proposal_auth_'.$proposalId;

        if (! $request->session()->get($sessionKey)) {
            $loginRoute = app()->getLocale() === 'en' ? 'proposals.login.en' : 'proposals.login';

            return redirect()->route($loginRoute)
                ->withErrors(['proposal_id' => __('messages.proposals.auth_required')]);
        }

        return $next($request);
    }
}
