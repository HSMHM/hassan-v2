<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Services\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProposalController extends Controller
{
    public function login(): Response
    {
        $locale = app()->getLocale();

        return Inertia::render('Proposals/Login', [
            'meta' => SeoService::forProposalLogin($locale),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'proposal_id' => ['required', 'string', 'max:64'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $locale = app()->getLocale();
        $proposal = Proposal::where('proposal_id', $data['proposal_id'])
            ->where('locale', $locale)
            ->where('is_active', true)
            ->first();

        if (! $proposal || ! Hash::check($data['password'], $proposal->password)) {
            return back()->withErrors([
                'proposal_id' => __('messages.proposals.invalid_credentials'),
            ])->onlyInput('proposal_id');
        }

        $request->session()->put('proposal_auth_'.$proposal->proposal_id, true);

        $route = $locale === 'en' ? 'proposals.show.en' : 'proposals.show';

        return redirect()->route($route, ['proposalId' => $proposal->proposal_id]);
    }

    public function show(string $proposalId): Response
    {
        $locale = app()->getLocale();
        $proposal = Proposal::where('proposal_id', $proposalId)
            ->where('locale', $locale)
            ->where('is_active', true)
            ->firstOr(fn () => throw new NotFoundHttpException);

        return Inertia::render('Proposals/Show', [
            'meta' => SeoService::forProposalLogin($locale),
            'proposal' => $proposal->makeHidden('password'),
        ]);
    }
}
