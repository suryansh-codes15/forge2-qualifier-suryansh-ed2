<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BoardList;
use App\Models\Card;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function store(Request $request, BoardList $list)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);
        $position = $list->cards()->max('position') + 1;

        return $list->cards()->create([...$data, 'position' => $position])
            ->load('tags', 'members');
    }

    public function update(Request $request, Card $card)
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'position' => 'sometimes|integer',
        ]);
        $card->update($data);
        return $card->load('tags', 'members');
    }

    public function move(Request $request, Card $card)
    {
        $data = $request->validate([
            'list_id' => 'required|exists:lists,id',
            'position' => 'sometimes|integer',
        ]);
        $card->update([
            'list_id' => $data['list_id'],
            'position' => $data['position'] ?? $card->position,
        ]);
        return $card->load('tags', 'members');
    }

    public function destroy(Card $card)
    {
        $card->delete();
        return response()->noContent();
    }

    // Tags
    public function attachTag(Request $request, Card $card)
    {
        $data = $request->validate(['tag_id' => 'required|exists:tags,id']);
        $card->tags()->syncWithoutDetaching([$data['tag_id']]);
        return $card->load('tags');
    }

    public function detachTag(Card $card, int $tagId)
    {
        $card->tags()->detach($tagId);
        return $card->load('tags');
    }

    // Members
    public function assignMember(Request $request, Card $card)
    {
        $data = $request->validate(['member_id' => 'required|exists:members,id']);
        $card->members()->syncWithoutDetaching([$data['member_id']]);
        return $card->load('members');
    }

    public function unassignMember(Card $card, int $memberId)
    {
        $card->members()->detach($memberId);
        return $card->load('members');
    }
}
