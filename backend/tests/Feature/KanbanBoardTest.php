<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\BoardList;
use App\Models\Card;
use App\Models\Member;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KanbanBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_list_boards(): void
    {
        $response = $this->postJson('/api/boards', [
            'name' => 'Test Board',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('name', 'Test Board');

        $this->assertDatabaseHas('boards', ['name' => 'Test Board']);

        $response = $this->getJson('/api/boards');
        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }

    public function test_can_create_lists_nested_under_board(): void
    {
        $board = Board::create(['name' => 'Project Board']);

        $response = $this->postJson("/api/boards/{$board->id}/lists", [
            'name' => 'To Do',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('name', 'To Do');

        $this->assertDatabaseHas('lists', [
            'board_id' => $board->id,
            'name' => 'To Do',
        ]);
    }

    public function test_can_create_cards_nested_under_list(): void
    {
        $board = Board::create(['name' => 'Project Board']);
        $list = BoardList::create([
            'board_id' => $board->id,
            'name' => 'To Do',
            'position' => 1,
        ]);

        $response = $this->postJson("/api/lists/{$list->id}/cards", [
            'title' => 'Build API',
            'description' => 'Create all endpoints',
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('title', 'Build API');

        $this->assertDatabaseHas('cards', [
            'list_id' => $list->id,
            'title' => 'Build API',
        ]);
    }

    public function test_can_attach_and_detach_tags(): void
    {
        $board = Board::create(['name' => 'Project Board']);
        $list = BoardList::create(['board_id' => $board->id, 'name' => 'To Do', 'position' => 1]);
        $card = Card::create([
            'list_id' => $list->id,
            'title' => 'Build API',
            'description' => 'Desc',
            'position' => 1,
        ]);

        $tag = Tag::create(['name' => 'Bug', 'color' => '#ff0000']);

        $response = $this->postJson("/api/cards/{$card->id}/tags", [
            'tag_id' => $tag->id,
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('card_tag', [
            'card_id' => $card->id,
            'tag_id' => $tag->id,
        ]);

        $response = $this->deleteJson("/api/cards/{$card->id}/tags/{$tag->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('card_tag', [
            'card_id' => $card->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_can_assign_and_unassign_members(): void
    {
        $board = Board::create(['name' => 'Project Board']);
        $list = BoardList::create(['board_id' => $board->id, 'name' => 'To Do', 'position' => 1]);
        $card = Card::create([
            'list_id' => $list->id,
            'title' => 'Build API',
            'description' => 'Desc',
            'position' => 1,
        ]);

        $member = Member::create(['name' => 'John Doe', 'email' => 'john@example.com']);

        $response = $this->postJson("/api/cards/{$card->id}/members", [
            'member_id' => $member->id,
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('card_member', [
            'card_id' => $card->id,
            'member_id' => $member->id,
        ]);

        $response = $this->deleteJson("/api/cards/{$card->id}/members/{$member->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('card_member', [
            'card_id' => $card->id,
            'member_id' => $member->id,
        ]);
    }

    public function test_comments_and_activities(): void
    {
        $board = Board::create(['name' => 'Project Board']);
        $list = BoardList::create(['board_id' => $board->id, 'name' => 'To Do', 'position' => 1]);
        $card = Card::create([
            'list_id' => $list->id,
            'title' => 'Build API',
            'description' => 'Desc',
            'position' => 1,
        ]);
        $member = Member::create(['name' => 'John Doe', 'email' => 'john@example.com']);

        $response = $this->postJson("/api/cards/{$card->id}/comments", [
            'member_id' => $member->id,
            'content' => 'First comment!',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('card_activities', [
            'card_id' => $card->id,
            'type' => 'comment',
            'content' => 'First comment!',
        ]);

        $response = $this->getJson("/api/cards/{$card->id}/activities");
        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }
}
