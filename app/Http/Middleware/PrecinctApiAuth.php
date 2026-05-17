<?php

namespace App\Http\Middleware;

use App\Models\Precinct;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class PrecinctApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->bearerToken();
        $precinctCode = $request->header('X-Precinct-ID');

        if (! $apiKey || ! $precinctCode) {
            return response()->json([
                'message' => 'Authentication required.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $precinct = Precinct::where('precinct_code', $precinctCode)->first();

        if (! $precinct) {
            return response()->json([
                'message' => 'Invalid precinct.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (! Hash::check($apiKey, $precinct->api_key_hash)) {
            return response()->json([
                'message' => 'Invalid API key.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request->merge(['precinct' => $precinct]);

        return $next($request);
    }
}
