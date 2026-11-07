<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $branchParam = $request->route('branch');

        if ($branchParam !== null) {
            $branch = $branchParam instanceof Branch
                ? $branchParam
                : Branch::where('slug', $branchParam)->orWhere('id', $branchParam)->firstOrFail();

            if (! $user->canAccessBranch($branch)) {
                abort(403, 'Bu şubenin verilerine erişim yetkiniz bulunmamaktadır.');
            }
        }

        return $next($request);
    }
}
