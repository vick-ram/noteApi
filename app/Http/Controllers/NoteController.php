<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Note;
use App\Helpers\ApiResponse;

class NoteController extends Controller
{
    public function addNote(Request $request): JsonResponse
    {
        $valid_note = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:60'],
            'content' => ['required', 'string', 'min:10'],
        ]);

        if ($valid_note->fails()) {
            return ApiResponse::error(
                message: $valid_note->errors(),
                status: 422
            );
        }

        $user = $request->user();

        $existing_note = Note::where('title', $request->title)
            ->where('user_id', $user->id)
            ->first();

        if ($existing_note) {
            return ApiResponse::error(
                message: 'Note with this title already exists',
                status: 409
            );
        }

        $note = Note::create([
            'title' => $request->title,
            'content' => $request->content,
            'user_id' => $user->id,
            'favorite' => $request->favorite ?? false,
        ]);

        $note->save();

        return ApiResponse::success(
            data: $note,
            status: 201
        );
    }
}
