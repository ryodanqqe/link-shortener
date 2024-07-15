<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Link;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LinkController extends Controller
{
    /**
     * Store a new link.
     *
     * @OA\Post(
     *     path="/api/links/store",
     *     operationId="storeLink",
     *     tags={"Links"},
     *     summary="Store a new link",
     *     description="Create a new shortened link for the authenticated user",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"original_url"},
     *             @OA\Property(property="original_url", type="string", format="url"),
     *             @OA\Property(property="short_token", type="string"),
     *             @OA\Property(property="is_private", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Link created successfully"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'original_url' => 'required|url',
            'short_token' => 'nullable|string|unique:links,short_token',
            'is_private' => 'boolean',
        ]);

        $short_token = $request->short_token ?? Str::random(6);

        $link = Link::create([
            'user_id' => $request->user()->id,  
            'original_url' => $request->original_url,
            'short_token' => $short_token,
            'is_private' => $request->is_private ?? false,
        ]);

        return response()->json($link, 201);
    }

    /**
     * Show link details.
     *
     * @OA\Get(
     *     path="/api/links/show/{token}",
     *     operationId="showLink",
     *     tags={"Links"},
     *     summary="Show link details",
     *     description="Return details of the link identified by the short token",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true,
     *         description="Short token for the link",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Link details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Link not found"
     *     )
     * )
     */
    public function show($token)
    {
        $link = Link::where('short_token', $token)->firstOrFail();

        if ($link->is_private) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($link);
    }

    /**
     * Get all links.
     *
     * @OA\Get(
     *     path="/api/links/index",
     *     operationId="getLinks",
     *     tags={"Links"},
     *     summary="Get all links",
     *     description="Return a list of all links created by user",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of links"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $links = $request->user()->links;

        return response()->json($links);
    }

    /**
     * Redirect to the original URL associated with the token.
     *
     * @OA\Get(
     *      path="/api/links/redirect/{token}",
     *      operationId="redirectToOriginalUrl",
     *      tags={"Links"},
     *      summary="Redirect to original URL",
     *      description="Redirects to the original URL associated with the token",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="token",
     *          in="path",
     *          required=true,
     *          description="Short token for the link",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=302,
     *          description="Redirect to the original URL"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Unauthorized - Link is private"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Link not found"
     *      )
     * )
     */
    public function redirect($token)
    {
        $link = Link::where('short_token', $token)->firstOrFail();

        if ($link->is_private) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return redirect($link->original_url);
    }

}
