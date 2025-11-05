<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\PaymentRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Fetch all comments for a specific request.
     * Corresponds to: GET /api/v1/requests/{paymentRequest}/comments
     */
    public function index(PaymentRequest $paymentRequest): JsonResponse
    {
        // Authorization: Ensure the user has permission to view the request first.
        // This implicitly authorizes all participating roles (CEO, FM, FO, HR if authorized to view).
        $this->authorize('view', $paymentRequest);

        // Load comments, eager-load the user's name
        $comments = $paymentRequest->comments()->with('user:id,name')->latest()->get();

        return response()->json($comments);
    }

    /**
     * Store a new comment/feedback.
     * Corresponds to: POST /api/v1/requests/{paymentRequest}/comments
     */
    public function store(Request $request, PaymentRequest $paymentRequest): JsonResponse
    {
        // Authorization: Only users involved in the request should be able to comment.
        $this->authorize('view', $paymentRequest);

        $validated = $request->validate([
            'content' => 'required|string|min:5|max:1000',
            'is_clarification_request' => 'boolean',
        ]);

        $comment = $paymentRequest->comments()->create([
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'is_clarification_request' => $validated['is_clarification_request'] ?? false,
        ]);

        // TODO: Notification: Notify the request creator or the next approver about the new comment.

        return response()->json($comment->load('user:id,name'), 201);
    }
}