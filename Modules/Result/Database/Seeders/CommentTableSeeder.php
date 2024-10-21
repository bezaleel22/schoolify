<?php

namespace Modules\Result\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Result\Entities\Comment;
use Modules\Result\Entities\CommentTag;

class CommentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Path to your JSON file in the storage folder
        $json = Storage::get('comments.json');

        // Decode the JSON data
        $data = json_decode($json, true);

        // Seeding tags first
        foreach ($data['tags'] as $tagData) {
            CommentTag::firstOrCreate(['tag' => $tagData['tag']]);
        }

        // Prepare for batch insert
        $commentsToInsert = [];
        foreach ($data['comments'] as $commentData) {
            $commentsToInsert[] = [
                'text' => $commentData['text'],
                'is_flagged' => $commentData['flagged'],
                'type' => $commentData['type'] ?? 'neutral', // Default type if not provided
                'school_id' => 1, // Or another relevant school_id if applicable
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert comments in batches
        $chunks = array_chunk($commentsToInsert, 1000); // Adjust chunk size as needed
        foreach ($chunks as $chunk) {
            Comment::insert($chunk);
        }

        // Attach tags to the comments
        // This requires finding the comment IDs after insertion
        $commentIds = Comment::pluck('id')->toArray();
        $tagIds = CommentTag::pluck('id', 'tag')->toArray();

        // Prepare pivot data
        $pivotData = [];
        foreach ($data['comments'] as $index => $commentData) {
            $commentId = $commentIds[$index]; // Get the corresponding comment ID
            $tags = $commentData['tags'] ?? [];
            foreach ($tags as $tag) {
                if (isset($tagIds[$tag])) {
                    $pivotData[] = [
                        'comment_id' => $commentId,
                        'comment_tag_id' => $tagIds[$tag],
                    ];
                }
            }
        }

        // Insert pivot data in batches
        $chunks = array_chunk($pivotData, 1000); // Adjust chunk size as needed
        foreach ($chunks as $chunk) {
            DB::table('comment_pivots')->insert($chunk);
        }
    }
}
