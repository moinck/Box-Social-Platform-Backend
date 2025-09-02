<?php

namespace App\Console\Commands;

use App\Models\PostTemplate;
use App\Models\UserTemplates;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class addPostTemplateIdsToOldRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:user-template-post-content-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Start adding post content id to user templates');

        try {
            DB::beginTransaction();
            $userTemplates = UserTemplates::select('id', 'template_id','post_content_id')
                ->where('template_id', '!=', null)
                ->get();
    
            $this->info('Total user templates: ' . $userTemplates->count());
    
            foreach ($userTemplates as $userTemplate) {
                $postTemplate = PostTemplate::select('id','post_content_id')
                    ->where('id', $userTemplate->template_id)
                    ->first();
    
                if($postTemplate){
                    $userTemplate->post_content_id = $postTemplate->post_content_id ?? null;
                    $userTemplate->save();
                }
            }
    
            $this->info('Finished adding post content id to user templates');
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
