<?php

namespace VCComponent\Laravel\Post\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use VCComponent\Laravel\Post\Entities\Post;
use VCComponent\Laravel\Post\Entities\PostMeta;

class SchemaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schema:posts {--type=posts} {key?} {value?} {--refresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add, update post meta data key and value';

    protected $entity;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        if (config('post.models.post') !== null) {
            $this->entity = App::make(config('post.models.post'));
        } else {
            $this->entity = Post::class;
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $type    = $this->option('type');
        $refresh = $this->option('refresh');
        $key     = $this->argument('key');
        $value   = $this->argument('value');
        if (!$value) {
            $value = '';
        }

        if ($type !== 'posts') {
            if (!in_array($type, $this->entity->postTypes())) {
                throw new \Exception(title_case($type) . ' not found in post types');
            }
        }

        $schema_keys = $this->getSchemaKeys($type);
        $posts       = $this->entity->where('type', $type)->get();
        if ($key) {
            if (!in_array($key, $schema_keys)) {
                throw new \Exception('Undefined field: ' . $key);
            }

            if ($posts->count()) {
                foreach ($posts as $post) {
                    $post->postMetas()->updateOrCreate(['key' => $key], ['value' => $value]);
                }
            }
        } else {
            if ($refresh) {
                if ($posts->count()) {
                    foreach ($posts as $post) {
                        PostMeta::where('post_id', $post->id)->delete();
                    }
                }
            }

            if ($posts->count() && count($schema_keys)) {
                foreach ($posts as $post) {
                    foreach ($schema_keys as $key) {
                        $post->postMetas()->updateOrCreate(['key' => $key], ['value' => $value]);
                    }
                }
            }
        }

        $this->info('Success');
    }

    private function getSchemaKeys($type)
    {
        if ($type === 'posts') {
            $schema = 'schema';
        } else {
            $schema = camel_case($type . '_schema');
        }
        $schema_keys = collect($this->entity->$schema())->keys()->toArray();

        return $schema_keys;
    }
}
